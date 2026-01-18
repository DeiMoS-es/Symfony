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
- [x] Métodos de búsqueda por título:
  - [x] `search(string $query, int $page)` - búsqueda raw en TMDb.
  - [x] `searchCatalog(string $query, int $page)` - búsqueda en TMDb transformada a DTOs.
  - [x] `MovieRepository::searchByTitle(string $term, int $limit, int $offset)` - búsqueda en la base de datos local.
- [x] Entidades `Movie` y `Genre` persistidas en la base de datos local (migraciones creadas: `movie`, `genre`, `movie_genres`).
- [x] `Movie` guarda campos `voteAverage` y `voteCount` que pueden ser sincronizados o calculados a partir de reviews.
- [x] `MovieService` para:
  - [x] Crear/actualizar películas a partir de `MovieUpsertRequest`.
  - [x] Resolver/crear géneros asociados.
  - [x] Sincronizar una película concreta desde TMDb (`getAndPersistFromTmdb`).
  - [x] Buscar películas por título en TMDb (`getSearchCatalog`).
- [x] Controladores para dashboard y catálogo (`DashboardController`, `MovieCatalogController`).
  - [x] `DashboardController::dashboard` integra búsqueda por parámetro `q` usando `MovieService::getSearchCatalog`.
  - [x] Vista `dashboard.html.twig` con formulario de búsqueda y renderizado de resultados (populares o búsqueda).
- [x] Botón "Recomendar" en el catálogo que permite recomendar la película al grupo del usuario (si está en un grupo).
- [x] Listener global `TmdbExceptionListener` para mapear errores de TMDb a respuestas HTTP JSON con códigos adecuados.
- [x] Test de integración `MovieServiceTest` para verificar la búsqueda de películas por título en TMDb.

## Configuración
- Servicio `TmdbService` configurado en `config/services.yaml`:
  - Inyecta `HttpClientInterface`.
  - Lee parámetros `tmdb.api_key` y `tmdb.read_token` desde variables de entorno:
    - `TMDB_API_KEY`
    - `TMDB_READ_TOKEN`
- Listener `TmdbExceptionListener` registrado como `kernel.event_listener` para capturar excepciones del espacio `App\Module\Movie\Exception`.

## Flujo principal

### Listado de películas populares
1. El usuario accede al dashboard de películas (`/movies/dashboard`).
2. `DashboardController` invoca `TmdbService::fetchPopularCatalog($page)` para obtener un listado paginado de películas populares.
3. Los resultados se transforman en instancias de `MovieCatalogItemDTO` y se pasan a la vista Twig (`dashboard.html.twig`).
4. Cuando es necesario persistir una película en la base de datos local, se utiliza `MovieService::getAndPersistFromTmdb($tmdbId)` o `findOrCreateFromUpsertDTO()` con el DTO adecuado.

### Búsqueda de películas por título
1. El usuario ingresa un término en el formulario de búsqueda en `dashboard.html.twig`.
2. El formulario envía la búsqueda con parámetro `q` a `DashboardController::dashboard`.
3. El controlador captura el parámetro `q` y lo pasa a `MovieService::getSearchCatalog($searchTerm, $page)`.
4. `MovieService` valida la consulta: si está vacía, devuelve catálogo popular; si tiene contenido, delega en `TmdbService::searchCatalog()`.
5. `TmdbService::searchCatalog` realiza la petición GET a `/search/movie` en TMDb, transforma los resultados en `MovieCatalogItemDTO` y devuelve estructura de paginación.
6. Los resultados se pasan de vuelta a la vista con `searchTerm` para mostrar "Resultados para: [término]" en el encabezado.
7. La vista renderiza las películas encontradas con el mismo layout que el catálogo popular.

## Errores y manejo de fallos
- Si TMDb devuelve códigos 401, 404, 5xx o estados inesperados, `TmdbService` lanza excepciones tipadas (`TmdbUnauthorizedException`, `TmdbNotFoundException`, `TmdbUnavailableException`, `TmdbException`).
- `TmdbExceptionListener` captura estas excepciones y devuelve respuestas JSON con un mensaje de error y el código HTTP apropiado.
- En caso de fallos no críticos, `fetchPopularCatalog` devuelve una estructura vacía segura, evitando romper el dashboard.

## Próximos pasos
- [ ] Exponer endpoint API REST `/api/movies/search?q=...` para búsqueda desde cliente JavaScript.
- [ ] Implementar búsqueda full-text en la base de datos local para películas almacenadas.
- [ ] Integrar la información de recomendaciones y reviews del módulo `Group` en las vistas de detalle de película.
- [ ] Añadir más tests de integración para cubrir casos edge (búsquedas vacías, resultados paginados, errores de API).
- [ ] Paginación avanzada en la UI (botones anterior/siguiente, saltar a página).
