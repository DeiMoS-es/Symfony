#!/bin/bash

# Esta línea detecta dónde está el script y sube un nivel para entrar en la raíz 'club-cine'
PARENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/.."
cd "$PARENT_DIR" || exit

echo ""
echo "=== Symfony Environment Switcher ==="
echo ""

case "$1" in
  dev)
    echo "→ Cambiando a entorno de DESARROLLO..."
    rm -rf vendor/
    composer install
    php bin/console cache:clear
    echo ""
    echo "✔ Entorno de desarrollo listo."
    ;;

  prod)
    echo "→ Generando BUILD DE PRODUCCIÓN para Vercel..."
    rm -rf vendor/
    APP_ENV=prod composer install --no-dev --optimize-autoloader
    APP_ENV=prod php bin/console importmap:install
    APP_ENV=prod php bin/console asset-map:compile
    php bin/console cache:clear --env=prod

    echo ""
    echo "✔ Build de producción generada."
    echo ""

    # Preguntar mensaje de commit
    read -p "Mensaje del commit: " commit_msg

    echo "→ Añadiendo archivos necesarios..."
    # git add -f vendor/ importmap.php assets/vendor/ 2>/dev/null
    git add importmap.php assets/vendor/ 2>/dev/null
    git add .  # por si hay otros cambios

    echo "→ Haciendo commit..."
    git commit -m "$commit_msg"

    echo "→ Subiendo a GitHub..."
    git push

    echo ""
    echo "✔ Deploy listo para Vercel."
    ;;

  *)
    echo "Uso:"
    echo "  ./env-switch.sh dev     → volver a entorno de desarrollo"
    echo "  ./env-switch.sh prod    → generar build de producción y subirla"
    ;;
esac
