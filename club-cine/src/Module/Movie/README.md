# Módulo de Películas (Movie)

## Descripción
Este módulo gestiona todo lo relacionado con el catálogo de películas dentro de la aplicación: integración con la API de TMDb, persistencia local de entidades `Movie` y `Genre`, y exposición de datos a los controladores y vistas (dashboard de películas, búsqueda, etc.).

## Estructura del Módulo
```
Movie/
├── Controller/   # Controladores HTTP (dashboard, catálogo, búsqueda)
├── DTO/          # Objetos de transferencia de datos (catálogo, detalle, upsert)
├── Entity/       # Entidades de dominio: Movie, Genre
├── Exception/    # Excepciones específicas de TMDb
├── Mapper/       # Mapeadores de entidades/DTOs hacia respuestas
├── Repository/   # Repositorios de Movie y Genre
└── Service/      # Servicios de dominio: MovieService, TmdbService
```

## Características Implementadas ✅
- [x] Integración con TMDb mediante `TmdbService` (peticiones HTTP autenticadas con API key / read token).
- [x] Método `fetchPopular(int $page)` para obtener películas populares directamente desde TMDb.
- [x] Método `fetchPopularCatalog(int $page)` que transforma los resultados en una estructura amigable con `MovieCatalogItemDTO`.
- [x] Método `fetchMovie(int $tmdbId)` para obtener el detalle completo de una película.
- [x] Método `search(string $query, int $page)` para búsquedas por texto.
- [x] Entidades `Movie` y `Genre` persistidas en la base de datos local.
- [x] `MovieService` para:
  - [x] Crear/actualizar películas a partir de `MovieUpsertRequest`.
  - [x] Resolver/crear géneros asociados.
  - [x] Sincronizar una película concreta desde TMDb (`getAndPersistFromTmdb`).
- [x] Controladores para dashboard y catálogo (`DashboardController`, `MovieCatalogController`).
- [x] Listener global `TmdbExceptionListener` para mapear errores de TMDb a respuestas HTTP JSON con códigos adecuados.

## Configuración
- Servicio `TmdbService` configurado en `config/services.yaml`:
  - Inyecta `HttpClientInterface`.
  - Lee parámetros `tmdb.api_key` y `tmdb.read_token` desde variables de entorno:
    - `TMDB_API_KEY`
    - `TMDB_READ_TOKEN`
- Listener `TmdbExceptionListener` registrado como `kernel.event_listener` para capturar excepciones del espacio `App\Module\Movie\Exception`.

## Flujo principal
1. El usuario accede al dashboard de películas (`/movies/dashboard`).
2. `DashboardController` invoca `TmdbService::fetchPopularCatalog($page)` para obtener un listado paginado de películas populares.
3. Los resultados se transforman en instancias de `MovieCatalogItemDTO` y se pasan a la vista Twig (`dashboard.html.twig`).
4. Cuando es necesario persistir una película en la base de datos local, se utiliza `MovieService::getAndPersistFromTmdb($tmdbId)` o `findOrCreateFromUpsertDTO()` con el DTO adecuado.

## Errores y manejo de fallos
- Si TMDb devuelve códigos 401, 404, 5xx o estados inesperados, `TmdbService` lanza excepciones tipadas (`TmdbUnauthorizedException`, `TmdbNotFoundException`, `TmdbUnavailableException`, `TmdbException`).
- `TmdbExceptionListener` captura estas excepciones y devuelve respuestas JSON con un mensaje de error y el código HTTP apropiado.
- En caso de fallos no críticos, `fetchPopularCatalog` devuelve una estructura vacía segura, evitando romper el dashboard.

## Próximos pasos
- [ ] Exponer endpoints adicionales para buscar y filtrar películas dentro del propio club (por ejemplo, películas ya puntuadas por el grupo).
- [ ] Integrar la información de recomendaciones y reviews del módulo `Group` en las vistas de detalle de película.
- [ ] Añadir tests de integración específicos para `TmdbService` y `MovieService` usando dobles de prueba del cliente HTTP.
