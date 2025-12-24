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

## Características Implementadas
- [x] Entidad `Group` (nombre, slug, descripción, owner, miembros, timestamps)
- [x] Entidad `GroupMember` (vinculación usuario-grupo, rol, fecha de ingreso)
- [x] `GroupRepository` con métodos básicos (`findByName`, `save`, `delete`, `isActive`)
- [x] `GroupMemberRepository` con métodos `save` y `findByUserAndGroup`
- [ ] Controladores API para gestión de grupos y miembros
- [ ] Servicios: `GroupService`, `MembershipService` (validaciones y casos de uso)
- [ ] Recomendaciones de películas (`Recommendation`) y reseñas/valoraciones (`Review`)
- [ ] Job/Command para cierre de periodo de valoración y cálculo de puntuación final del grupo
- [ ] Migraciones y tests automatizados del flujo completo

## Configuración
No requiere configuración adicional específica por ahora. Las rutas y servicios se agregarán cuando se implementen los controladores y servicios.

## Dependencias
- doctrine/orm (usado para entidades y repositorios)
- symfony/security (para validar miembros/propietarios vía usuario autenticado)

## Historial de Implementación
1. Modelo `Group` creado con slug automático y asociación al `owner` (crea el `GroupMember` con rol OWNER) ✅
2. Modelo `GroupMember` creado con repositorio y helpers básicos ✅
3. Repositorios con métodos de guardado y búsqueda básicos ✅

## Plan de Implementación Detallado
### 1. Ajustes y servicios básicos
- [ ] Crear `GroupService` para casos de uso (crear/editar/eliminar grupos, invitar usuarios)
- [ ] Implementar `MembershipService` para invitar/aceptar/quitar miembros y validaciones ACL

### 2. Recomendaciones y valoraciones (prioridad alta)
- [ ] Crear entidad `Recommendation` (group, movie, recommender, createdAt, deadline, status, finalScore)
- [ ] Crear entidad `Review` o `Rating` (recommendation, user, score, reviewText, createdAt)
- [ ] `RecommendationService` y `ReviewService` con reglas de negocio (único voto por usuario, edición antes de `deadline`)
- [ ] Endpoints API/Controladores: recomendar, listar, puntuar, ver resumen (con ACL)

### 3. Cierre de periodo y cálculo de puntuación
- [ ] Implementar `CloseRecommendationsCommand` o handler programado (cron / Messenger scheduled)
- [ ] Reglas de agregación (media, medianas, descarte de outliers — definir)
- [ ] Notificaciones a miembros (opcional)

### 4. Tests y migraciones
- [ ] Crear migraciones Doctrine para nuevas entidades
- [ ] Tests unitarios e integración para flujos: crear recomendación, votar, cierre y cálculo

### 5. Funcionalidades tardías / Opcionales
- [ ] Permitir crear manualmente una película si no existe en TMDB (planear al final)
- [ ] UI/UX: páginas para administrar grupos y ver recomendaciones internas

## Estado Actual
- Fase actual: **Modelo de dominio base** implementado (entidades `Group` y `GroupMember` + repositorios).
- Próxima tarea lógica: implementar servicios y endpoints para crear y gestionar grupos y membresías, seguido por el sistema de recomendaciones y valoraciones de grupo.
- Status: **En progreso** — estructura básica lista para añadir casos de uso y API.

## Notas de Diseño (decisiones y consideraciones)
- Los grupos tienen un `owner` (propietario) y miembros con rol (`OWNER`/`MEMBER`) — el owner se añade automáticamente al crear el grupo.
- Las recomendaciones y reseñas deben estar restringidas a miembros del grupo; la visibilidad de las reseñas individuales y del `finalScore` debe definirse (por defecto: resumen y `finalScore` solo visibles a miembros).
- La creación manual de películas (cuando no existe en TMDB) se considera una funcionalidad avanzada y se planifica como último paso.

---