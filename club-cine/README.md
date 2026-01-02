# 🎬 CineClub App

Una aplicación web desarrollada con **Symfony 7** para digitalizar y mejorar la experiencia del cine club creado por mi pareja y sus compañeros de trabajo. Cada semana el grupo elige una película, la ve y comparte sus valoraciones y comentarios. Esta app reemplaza el uso de hojas de Excel por una interfaz moderna, accesible y colaborativa.

---

## 🚀 Objetivo

Facilitar la participación en el cine club mediante una plataforma web que permita:

- Registrar usuarios y gestionar sesiones.
- Crear grupos de amigos para compartir películas y valoraciones.
- Puntuar películas en distintos aspectos (guion, dirección, actuación, etc.).
- Escribir comentarios personales sobre cada película.
- Visualizar estadísticas y rankings semanales.
- Fomentar el debate y la interacción entre los miembros.

---

## 🧩 Arquitectura modular

El proyecto está organizado en módulos independientes dentro de `src/Module`, cada uno con su propio README y responsabilidades bien definidas:

- 📄 [Módulo Auth](src/Module/Auth/README.md)
- 📄 [Módulo Group](src/Module/Group/README.md)
- 📄 [Módulo Movie](src/Module/Movie/README.md) — Implementado: integración con TMDb, sincronización y persistencia de películas; catálogo y dashboard en la UI.

---

## 🛠️ Tecnologías utilizadas

- **Backend:** Symfony 7 (PHP 8.2)
- **Frontend:** Twig + Bootstrap 5
- **Base de datos:** MySQL (desarrollo) y SQLite en memoria para tests
- **Autenticación:** JWT + cookies (LexikJWTAuthenticationBundle)
- **Control de versiones:** Git + GitLab

---

## 📦 Estado actual del proyecto

- ✅ Registro de usuarios (API JSON)
- ✅ Inicio de sesión con JWT + refresh token (soporte por cookies `ACCESS_TOKEN`/`REFRESH_TOKEN` y rotación de refresh token)
- ✅ Creación de grupos de amigos (modelo y migraciones)
- ✅ Modelo de recomendaciones y reviews dentro de grupos (entidades `Recommendation` y `Review` con agregados y comentarios)
- ✅ Recomendación desde catálogo y gestión básica en la interfaz (botón "Recomendar", vista de grupo)
- ✅ Comando para cierre automático de recomendaciones (`app:close-recommendations`) y `RecommendationManager` (cálculo de estadísticas)
- ✅ Backend: soporte de puntuación por aspectos, cálculo de `averageScore` y agregados; UI de votación pendiente
- ✅ Persistencia de películas y géneros (tablas `movie`, `genre`, `movie_genres`) y sincronización desde TMDb
- ✅ Sistema de refresh tokens y tabla `refresh_tokens` (rotación y revocación)
- 🔜 Panel de administración
- 🔜 Visualización de rankings y estadísticas agregadas (front-end)

## 📣 Últimos cambios (2026-01-02)

- Refactor de varios controladores (Auth, Group) y limpieza de responsabilidades en servicios. 🔧
- Añadidos mappers (`AuthMapper`, `UserMapper`) y refactor en `RegistrationService` / `RegistrationController`. ✅
- Se añadió un test unitario: `tests/Module/Auth/Service/RegistrationServiceTest.php`. 🧪
- Mejora en `app:close-recommendations` (cierre automático y cálculo de estadísticas). ⚙️
- Nuevo `GroupService` y cambio de mensajes informativos multi-grupo. 💬
- UI: aumento del timeout de mensajes de aviso y ajustes en la barra de navegación y formulario de grupo (`templates/base.html.twig`, `templates/components/_navbar.html.twig`, `templates/group/_form.html.twig`). 🖼️
- Varias refactorizaciones menores y fixes. 🔁

---

## ▶️ Puesta en marcha rápida

```bash
git clone https://github.com/tu-usuario/cineclub-app.git
cd cineclub-app

# Instalar dependencias
composer install

# Configurar variables de entorno (editar .env o crear .env.local)
# - DATABASE_URL (MySQL)
# - JWT_SECRET_KEY / JWT_PUBLIC_KEY / JWT_PASSPHRASE
# - TMDB_API_KEY / TMDB_READ_TOKEN

# Ejecutar migraciones de base de datos (entorno dev)
php bin/console doctrine:migrations:migrate

# Arrancar el servidor de desarrollo
php -S localhost:8000 -t public
# o, si tienes el CLI de Symfony:
# symfony server:start -d
```

---

## 🧪 Tests

- Ejecutar toda la batería de tests (usa `.env.test` con SQLite en memoria):
  - `php bin/phpunit`
- Ejecutar un test concreto del módulo de autenticación:
  - `php bin/phpunit tests/Module/Auth/AuthModuleTest.php`

---

## 📂 Módulos principales

### Auth (`src/Module/Auth`)
Maneja el registro de usuarios, login, generación de JWT/refresh tokens y cierre de sesión. Expone endpoints JSON (`/auth/register`, `/auth/login`, `/auth/refresh`) y se integra con Symfony Security + LexikJWT.

### Group (`src/Module/Group`)
Modela los grupos del cine club, su membresía y las recomendaciones/reviews internas entre miembros. Incluye la lógica para cerrar automáticamente recomendaciones cuando expira la fecha límite.

### Movie (`src/Module/Movie`)
Se encarga de la integración con TMDb, el catálogo de películas y la persistencia local de `Movie` y `Genre`. Proporciona un dashboard de películas populares y servicios para sincronizar datos desde la API externa.
