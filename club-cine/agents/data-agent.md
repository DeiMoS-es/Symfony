# 💾 Data Agent — Fixtures, importaciones y seeds

## Misión
Gestionar datos de ejemplo, importar desde TMDb, y mantener fixtures reproducibles.

## Tasks
- Crear `src/DataFixtures/MoviesFixtures.php` con ~30 películas comunes.
- Crear `src/DataFixtures/UsersFixtures.php` con 5 usuarios (1 admin).
- Script `bin/import-tmdb.php --query="..."` para buscar e importar.

## Comandos
```bash
php bin/console doctrine:fixtures:load --append
php bin/console doctrine:fixtures:load --purge-with-truncate
```