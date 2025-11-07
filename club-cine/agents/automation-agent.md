# ⚙️ Automation Agent — CI/CD & Tasks programadas

## Misión
Automatizar build, tests, lint, despliegue y tareas periódicas (selección semanal, backups).

## Pipelines sugeridos (GitHub Actions)
- `ci.yml` → test + phpstan + cs fixer en `push` y `pull_request`
- `deploy.yml` → desplegar a Railway/Render al merge en `main`
- `weekly.yml` → `schedule` cron para `app:select-weekly-movie` (cada lunes)

## Ejemplo de pasos CI mínimos
1. Checkout
2. Setup PHP (`shivammathur/setup-php`)
3. Cache composer
4. composer install
5. phpunit
6. phpstan
7. cs fixer (dry-run)

## Scripts / Commands
- `php bin/console app:select-weekly-movie` (command custom)
- `php bin/console app:send-weekly-reminder` (command custom)
- DB backup (script bash y subir a storage)

## Secrets necesarios
- `RAILWAY_TOKEN` / `RENDER_TOKEN`
- `DATABASE_URL` (prod)
- `MAILER_DSN`

## Prompts para IA
