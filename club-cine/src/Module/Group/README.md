# Módulo de Grupos

## Descripción
Este módulo se encarga de la gestión de grupos de usuarios dentro de la aplicación Club de Cine. Permite la creación de grupos, la gestión de miembros y roles (propietario / miembro) y será la base para las recomendaciones y valoraciones internas de películas entre los miembros.

## Estructura del Módulo
```
Group/
├── Controller/     # (pendiente) Controladores para crear/gestionar grupos y membresías
├── Entity/         # Entidades: Group, GroupMember (ya implementadas)
├── Repository/     # Repositorios para acceso a datos
├── Service/        # Servicios del dominio (pendiente)
└── README.md       # Documentación del módulo
```

## Características Implementadas ✅
- [x] Entidad `Group` (nombre, slug, descripción, owner, miembros, timestamps)
- [x] Entidad `GroupMember` (vinculación usuario-grupo, rol, fecha de ingreso)
- [x] Entidad `Recommendation` (group, movie, recommender, createdAt, deadline, status, finalScore)
- [x] Entidad `Review` (votos por usuario, puntuaciones desglosadas, comentario, averageScore)
- [x] `GroupRepository`, `GroupMemberRepository`, `RecommendationRepository`, `ReviewRepository` con métodos básicos (`save`, `findBy...`, `findActiveByGroup`, `findExpiredToClose`)
- [x] Migración creada para las tablas `app_group_member`, `app_group_recommendation` y `app_group_review` (ver `migrations/Version20251224103850.php`)
- [ ] Controladores API para gestión de grupos y miembros
- [ ] Servicios: `GroupService`, `MembershipService`, `RecommendationService`, `ReviewService` (validaciones y casos de uso)
- [ ] Job/Command para cierre automático de recomendaciones (`CloseRecommendationsCommand` / scheduled handler)
- [ ] Tests automatizados del flujo completo (recomendación → votación → cierre → cálculo)

## Detalles de implementación 🔧
- `Recommendation` incluye métodos útiles: `isExpired()`, `canAcceptVotes()` y `closeWithScore(float $score, int $votes)` para marcarla como cerrada y almacenar el `finalScore`.
- `Review` implementa validaciones en el constructor: asegura que la recomendación esté abierta (`canAcceptVotes()`), valida que las puntuaciones estén entre 1 y 10 y calcula `averageScore` automáticamente.
- Se añadió una restricción de unicidad a nivel de BD para evitar que un mismo usuario vote más de una vez por la misma recomendación (`UNIQUE INDEX unique_user_recommendation (recommendation_id, user_id)`).
- `RecommendationRepository::findExpiredToClose()` devuelve recomendaciones que han pasado su `deadline` y siguen en estado `OPEN` — útil para el `CloseRecommendationsCommand`.

## Migraciones 🗂️
- Migración principal: `migrations/Version20251224103850.php` (crea `app_group_member`, `app_group_recommendation`, `app_group_review`, y tablas relacionadas necesarias para `movie` y `genre`).

## Historial de Implementación
1. Modelo `Group` creado con slug automático y asociación al `owner` (crea el `GroupMember` con rol OWNER) ✅
2. Modelo `GroupMember` creado con repositorio y helpers básicos ✅
3. Entidades `Recommendation` y `Review` implementadas con sus repositorios y migración ✅

## Plan de Implementación Actualizado 📋
1. Servicios y lógica de aplicación (prioridad alta)
   - [ ] Implementar `RecommendationService` y `ReviewService` (reglas de negocio: único voto por usuario, edición antes de `deadline`, cálculo de agregados)
   - [ ] Implementar `CloseRecommendationsCommand` (usar `RecommendationRepository::findExpiredToClose()` para cerrar recomendaciones y calcular `finalScore`)
2. API / Controladores
   - [ ] Endpoints para recomendar, listar recomendaciones de grupo, votar/editar voto y ver resumen (con ACL y validaciones)
3. Tests y calidad
   - [ ] Tests unitarios e integración para flujos críticos
   - [ ] Crear fixtures y pruebas para `CloseRecommendationsCommand` y reglas de agregación

## Estado Actual
- Fase actual: **Dominios y repositorios** implementados (incluyendo migraciones y restricciones de integridad).
- Próxima tarea lógica: implementar servicios y endpoints, seguido por tests y el comando de cierre automático.
- Status: **En progreso** — listo para desarrollar casos de uso y API.

## Notas de Diseño (decisiones y consideraciones)
- Las recomendaciones deben estar restringidas a miembros del grupo; la visibilidad del `finalScore` y detalles individuales seguirá siendo para miembros por defecto.
- La agregación del `finalScore` será configurable (media simple por defecto; en el futuro se podrá introducir medianas, descarte de outliers o pesos).
- Las validaciones críticas se encuentran en las entidades (`Review` y `Recommendation`) para proteger la integridad incluso si se omiten validaciones a nivel de servicio.

---


---