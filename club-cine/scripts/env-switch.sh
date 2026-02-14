#!/bin/bash

# Detectar directorio raíz
PARENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/.."
cd "$PARENT_DIR" || exit

echo ""
echo "=== Symfony Production Build for Vercel ==="
echo ""

case "$1" in
  prod)
    echo "→ Limpiando y preparando entorno..."
    rm -rf vendor/
    # Limpiamos assets locales para forzar un hash nuevo y romper la caché de Vercel
    rm -rf public/assets/*

    echo "→ Instalando dependencias de producción..."
    APP_ENV=prod composer install --no-dev --optimize-autoloader

    echo "→ Compilando assets..."
    # Generamos los archivos físicos (el app-xxxx.css que vemos en tus capturas)
    APP_ENV=prod php bin/console asset-map:compile
    
    echo "→ Calentando caché..."
    APP_ENV=prod php bin/console cache:clear
    APP_ENV=prod php bin/console cache:warmup

    echo ""
    read -p "Mensaje del commit: " commit_msg

    echo "→ Sincronizando con Git..."
    # Añadimos todo, pero forzamos la carpeta de assets por si acaso
    git add .
    git add -f public/assets/
    
    git commit -m "$commit_msg"

    echo "→ Subiendo a GitHub..."
    git push

    echo ""
    echo "✔ Proceso completado localmente."
    echo "⚠ IMPORTANTE: Si el 404 persiste, revisa el archivo vercel.json."
    ;;

  *)
    echo "Uso: ./env-switch.sh prod"
    ;;
esac