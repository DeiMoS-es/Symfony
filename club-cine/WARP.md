# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Comandos

### Preparación del entorno
- Instalar dependencias PHP:
  - `composer install`
- Asegúrate de tener las variables de entorno definidas (ver `.env` y `.env.test`):
  - `DATABASE_URL` para la base de datos principal (MySQL en desarrollo por defecto).
  - `TMDB_API_KEY` y `TMDB_READ_TOKEN` para la API de TMDb.
  - `JWT_SECRET_KEY`, `JWT_PUBLIC_KEY`, `JWT_PASSPHRASE` se usan en la configuración de LexikJWT.

### Base de datos y migraciones
- Crear/actualizar el esquema en la base de datos de desarrollo usando migraciones de Doctrine:
  - `php bin/console doctrine:migrations:migrate`
- Generar una nueva migración tras cambiar entidades:
  - `php bin/console make:migration`

### Ejecutar la aplicación en local
- Usando el servidor embebido de PHP:
  - `php -S localhost:8000 -t public`
- Si tienes el CLI de Symfony instalado, puedes usar:
  - `symfony server:start -d`

### Tests
- Ejecutar toda la batería de tests (usa `.env.test` con SQLite en memoria):
  - `php bin/phpunit`
- Ejecutar un archivo de test concreto:
  - `php bin/phpunit tests/Module/Auth/AuthModuleTest.php`
- Ejecutar un método de test concreto (ejemplo):
  - `php bin/phpunit --filter testLoginSuccessfully tests/Module/Auth/AuthModuleTest.php`

### Assets
- Los assets de frontend se gestionan con Symfony AssetMapper; cuando cambies JS/CSS puede ser necesario recompilar el mapa de assets:
  - `php bin/console asset-map:compile`

### Linting / calidad
- No hay un comando de estilo o análisis estático global configurado en `composer.json`. Usa `php -l` para comprobaciones rápidas de sintaxis y apóyate en la batería de tests para evitar regresiones.

## Visión general de la arquitectura

### Symfony y estructura modular
- Aplicación Symfony 7 con estructura de dominio modular en `src/Module` en lugar del layout plano típico `src/Controller` / `src/Entity`.
- Cada módulo agrupa sus propios controladores HTTP, entidades, repositorios, servicios, DTOs y excepciones. El routing se configura por módulo mediante atributos y `config/routes.yaml`.
- La inyección de dependencias se configura en `config/services.yaml` con autowire/autoconfigure activados. El namespace `App\` está registrado y los controladores de módulos se marcan explícitamente como públicos y etiquetados como controladores.
- Las entidades de Doctrine viven en `Entity/` dentro de cada módulo pero se excluyen del registro automático de servicios; los repositorios y servicios sí se autoinyectan.

### Módulo Auth
- Ubicación: `src/Module/Auth` (documentado en `src/Module/Auth/README.md`).
- Responsabilidad: registro de usuarios, login, emisión/refresh de JWT y persistencia de usuarios.
- Piezas principales:
  - `Entity/User`: entidad de usuario basada en UUID con `email`, `password` hasheado, `roles`, `name`, `createdAt` y una asociación opcional a `Group`. Implementa `UserInterface` y `PasswordAuthenticatedUserInterface` de Symfony.
  - `Entity/RefreshToken`: almacena tokens de refresco hasheados con fecha de expiración, vinculados a `User` (usado por `AuthService::generateRefreshToken`).
  - `Service/AuthService`: valida credenciales vía `UserRepository` + `UserPasswordHasherInterface`, emite JWTs mediante `JWTTokenManagerInterface` y persiste entidades `RefreshToken`.
  - `Security/JwtTokenGenerator` + `TokenGeneratorInterface`: envoltorio fino sobre `JWTTokenManagerInterface` de Lexik, registrado en `services.yaml` para que los consumidores puedan depender de la interfaz.
  - Los controladores en `Controller/` exponen endpoints JSON para `/auth/register`, `/auth/login`, `/auth/refresh`, delegando en `AuthService`.
- Configuración de seguridad:
  - `config/packages/security.yaml` define un provider de usuarios basado en `App\Module\Auth\Entity\User` y un password hasher.
  - Firewalls:
    - `login` (patrón `^/auth/login`) desactiva seguridad para permitir login anónimo.
    - `refresh` (`^/api/refresh`) y `api` (`^/api`) son stateless y usan Lexik JWT.
    - `movies` protege `^/movies` con JWT.
  - `access_control` permite acceso anónimo a `/auth/register` y `/`, mientras que `/api/user` y `/movies` requieren `ROLE_USER`.
- JWT y cookies:
  - `App\EventListener\JwtCookieInjectorListener` escucha en `kernel.request` y, si no hay cabecera `Authorization` pero sí cookie `ACCESS_TOKEN`, inyecta `Authorization: Bearer <token>`. Esto permite a clientes web autenticarse vía cookies HttpOnly reutilizando el mismo flujo JWT.

### Módulo Movie
- Ubicación: `src/Module/Movie`.
- Responsabilidad: integración con TMDb y persistencia local de películas y géneros.
- Piezas principales:
  - `Service/TmdbService`: cliente HTTP central para TMDb, inyectado con `HttpClientInterface`, `tmdb.api_key` y `tmdb.read_token` (configurados en `services.yaml` y alimentados por `TMDB_API_KEY` / `TMDB_READ_TOKEN`).
    - Métodos para catálogo: `fetchPopular` (raw), `fetchPopularCatalog` (DTOs con paginación), `fetchMovie` (detalle).
    - Métodos de búsqueda: `search` (raw) y `searchCatalog` (transformados a `MovieCatalogItemDTO` con paginación).
    - Lanza excepciones tipadas (`TmdbUnauthorizedException`, `TmdbNotFoundException`, `TmdbUnavailableException`, `TmdbException`) según el código de estado.
  - `Service/MovieService`: orquesta la persistencia de entidades `Movie` y `Genre` a partir de un DTO `MovieUpsertRequest` o de datos obtenidos vía `TmdbService`.
    - `findOrCreateFromUpsertDTO` crea o actualiza una película y sus géneros.
    - `getAndPersistFromTmdb` obtiene detalles desde TMDb, construye el DTO y delega en `findOrCreateFromUpsertDTO`, haciendo `flush` al final.
    - `getSearchCatalog` busca películas por título, delegando a `TmdbService::searchCatalog` si la consulta es válida.
  - `Repository/MovieRepository`: además del CRUD, proporciona `searchByTitle(string $term)` para búsquedas en la BD local.
  - Los DTOs en `DTO/` definen la forma de los datos que viajan entre controladores, servicios y vistas (por ejemplo, `MovieCatalogItemDTO`, `MovieUpsertRequest`).
- Controladores:
  - `DashboardController` (prefijo de ruta `/movies`) gestiona dos funcionalidades:
    - Obtiene `q` del request (parámetro de búsqueda).
    - Delega en `MovieService::getSearchCatalog($searchTerm, $page)` que internamente decide: si `$searchTerm` está vacío, devuelve catálogo popular; si no, busca en TMDb.
    - Renderiza `dashboard.html.twig` pasando `searchTerm`, `movies` (con estructura `items` + paginación), `totalPages` y `currentPage`.
  - `MovieCatalogController` maneja endpoints más específicos de catálogo (si existen).
- Vistas:
  - `dashboard.html.twig`: 
    - Renderiza un formulario de búsqueda con campo `q` que envía a `/movies/dashboard` por GET.
    - Muestra dinámicamente "Películas Populares" o "Resultados para: [término]" según hay búsqueda activa.
    - Itera sobre `movies.items` (array de `MovieCatalogItemDTO`) y renderiza tarjetas con imagen, título, año y botón "Recomendar" (si el usuario pertenece a un grupo).
- Manejo de errores:
  - `App\EventListener\TmdbExceptionListener` convierte excepciones relacionadas con TMDb en respuestas HTTP JSON con códigos apropiados (401/404/503/500), centralizando el manejo de errores de la API externa.

### Módulo Group
- Ubicación: `src/Module/Group` (documentado en `src/Module/Group/README.md`).
- Responsabilidad: gestión de grupos de usuarios, membresías, recomendaciones de películas dentro del grupo y reviews/votos por usuario.
- Entidades principales:
  - `Entity/Group`: agregado basado en UUID que representa un grupo del cine club (`name`, `slug`, `description`, `owner`, timestamps, flag de actividad).
    - En el constructor genera el `slug` a partir del nombre y añade automáticamente al owner como primer `GroupMember` con rol `OWNER`.
    - Mantiene relación `owner` con `User` y una colección de `members`.
  - `Entity/GroupMember`: vincula un `User` a un `Group` con un rol y fecha de alta.
  - `Entity/Recommendation`: vincula un `Group` y un `Movie` recomendado por un `User`, con `createdAt`, `deadline`, `status` (`OPEN`/`CLOSED`), campos de estadísticas agregadas y helpers como `canAcceptVotes()` y `closeWithStats()`.
  - `Entity/Review`: voto de un usuario sobre una `Recommendation`, con puntuaciones por categoría (guion, actores, dirección, etc.), un `averageScore` y validaciones en el constructor para garantizar rangos válidos y que solo se vote mientras la recomendación está abierta.
- Persistencia y repositorios:
  - Repositorios para cada entidad (por ejemplo, `GroupRepository`, `GroupMemberRepository`, `RecommendationRepository`, `ReviewRepository`) encapsulan la lógica de guardado/búsqueda/consultas.
  - `RecommendationRepository::findExpiredToClose()` devuelve recomendaciones cuya `deadline` ha pasado y siguen en estado `OPEN`, utilizado para el cierre automático.
- Agregación y procesado programado:
  - `Services/RecommendationManager` encapsula la lógica de cierre de recomendaciones expiradas agregando sus reviews asociadas.
    - `processExpiredRecommendations()` recupera recomendaciones expiradas y llama a `calculateAndClose()` para cada una.
    - `calculateAndClose()` calcula las medias por categoría y la nota final a partir de los datos de `Review`, llama a `Recommendation::closeWithStats()` y persiste el resultado.
  - `App\Command\CloseRecommendationsCommand` expone esto como comando CLI `app:close-recommendations`, pensado para cron/tareas programadas.
- Esquema de base de datos:
  - Las migraciones en `migrations/` crean tablas para grupos, miembros, recomendaciones, reviews y las relaciones necesarias con usuarios/películas.
  - Hay una restricción de unicidad para evitar votos duplicados por usuario y recomendación.

### Estrategia de testing
- Configuración global:
  - PHPUnit se configura vía `phpunit.dist.xml`, con `tests/bootstrap.php` levantando el kernel de Symfony y cargando `.env`.
  - El entorno de test usa `APP_ENV=test` y `DATABASE_URL="sqlite:///:memory:"` (ver `.env.test`), de modo que los tests se ejecutan completamente en memoria sin tocar la base de datos de desarrollo.
- Tests del módulo Auth:
  - `tests/Module/Auth/RegistrationControllerTest` y `AuthModuleTest` extienden `WebTestCase`, crean un cliente de test de Symfony y reconstruyen el esquema de base de datos en cada test usando `SchemaTool` de Doctrine.
  - Helpers como `postJson()` se usan para enviar peticiones JSON a `/auth/register`, `/auth/login`, `/auth/refresh` y hacer asserts sobre las respuestas JSON, cubriendo casos de éxito y de error (email duplicado, password no válida, credenciales inválidas, flujo de refresh token).

### Configuración transversal
- Inyección de dependencias y servicios:
  - `config/services.yaml` activa `autowire` y `autoconfigure` para el namespace `App\`.
  - Los controladores de módulos se registran explícitamente como servicios públicos con las etiquetas de controlador.
  - `App\Module\Auth\Security\TokenGeneratorInterface` se aliasa a `JwtTokenGenerator`, permitiendo que otros servicios dependan de la interfaz.
- Doctrine y persistencia:
  - La conexión se configura vía `DATABASE_URL` en `.env`; los comentarios documentan motores alternativos (SQLite, MySQL, PostgreSQL, MariaDB).
  - Las migraciones y la configuración por defecto del ORM se gestionan mediante las recetas de Symfony (ver `symfony.lock`).
- Configuración de integraciones externas:
  - La integración con TMDb se cablea mediante los parámetros de contenedor `tmdb.api_key` y `tmdb.read_token` definidos a partir de variables de entorno y se inyecta en `TmdbService`.
  - Las rutas de las claves JWT y la passphrase se configuran vía variables de entorno definidas en `.env` y usadas por LexikJWT.
