# 🧩 Backend Agent — Lógica / API / DB

## Misión
Diseñar y asegurar la lógica del dominio: entidades, repositorios, servicios, validaciones y seguridad.

## Entidades principales
- User (id, email, password, roles, name)
- Movie (id, title, description, year, posterUrl)
- Rating (id, user_id, movie_id, score, comment, createdAt)
- WeeklySelection (id, club_id, movie_id, week_start)

## Reglas / Convenciones
- Repositorios en `src/Repository`
- Servicios en `src/Service`
- DTOs para input en `src/Dto`
- Validación: `Symfony Validator` + FormType / custom Request DTOs
- Seguridad: roles `ROLE_USER`, `ROLE_ADMIN`
- Passwords: `password_hash` con `UserPasswordHasherInterface`

## Endpoints sugeridos (REST)
- `POST /api/auth/register`
- `POST /api/auth/login`
- `GET /api/clubs/{id}/selections/current`
- `POST /api/selections/{id}/ratings`
- `GET /api/movies?query=...`

## Migrations / DB
- Mantener migraciones con `doctrine/migrations`
- No usar `schema:update --force` en producción

## Checklist diario
- [ ] Ejecutar `composer install && php bin/console doctrine:migrations:status`
- [ ] Ejecutar testes unitarios `vendor/bin/phpunit`
- [ ] Ejecutar static analysis: `vendor/bin/phpstan analyse src --level=6`

## Comandos útiles
```bash
php bin/console make:entity
php bin/console make:migration
php bin/console doctrine:migrations:migrate
vendor/bin/phpunit
vendor/bin/phpstan analyse
