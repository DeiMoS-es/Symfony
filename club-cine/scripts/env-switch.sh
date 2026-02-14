#!/bin/bash

# Detectar directorio raíz
PARENT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/.."
cd "$PARENT_DIR" || exit

echo ""
echo "=== Symfony Environment Switcher ==="
echo ""

# Función para manejar errores
handle_error() {
    echo ""
    echo "❌ Error: $1"
    exit 1
}

case "$1" in
  dev)
    echo "→ Cambiando a entorno de DESARROLLO..."
    
    # 1. Limpieza de archivos de producción si existen
    rm -rf vendor/
    rm -rf public/assets/*
    
    # 2. Instalación de todas las dependencias (incluye DebugBundle)
    composer install || handle_error "Composer install falló"
    
    # 3. Limpiar caché para desarrollo
    php bin/console cache:clear || handle_error "Cache clear falló"
    
    echo ""
    echo "✔ Entorno de desarrollo listo. Puedes usar 'symfony serve' o tu servidor local."
    ;;

  prod)
    echo "→ Generando BUILD DE PRODUCCIÓN para Vercel..."
    echo ""
    
    # 1. Limpieza profunda (SIN ejecutar Symfony, solo agregar permisos)
    echo "  [1/7] Limpiando archivos previos..."
    rm -rf vendor/
    rm -rf public/assets/*

    # 2. Instalación solo de producción (optimizado)
    echo "  [2/7] Instalando dependencias de producción..."
    APP_ENV=prod composer install --no-dev --optimize-autoloader || handle_error "Composer install falló"

    # 3. Limpieza de caché DESPUÉS de instalar composer
    echo "  [3/7] Limpiando caché..."
    APP_ENV=prod php bin/console cache:clear || handle_error "Cache clear falló"

    # 4. Generación del importmap
    echo "  [4/7] Generando importmap..."
    APP_ENV=prod php bin/console importmap:install || handle_error "Importmap install falló"
    
    # 5. Compilación de assets (genera los archivos físicos con hash)
    echo "  [5/7] Compilando assets..."
    APP_ENV=prod php bin/console asset-map:compile || handle_error "Asset compilation falló"
    
    # 6. Validar que los assets se compilaron correctamente
    if [ ! -f "public/assets/manifest.json" ]; then
        handle_error "Manifest no se generó. Assets compilation falló."
    fi
    echo "       ✓ Assets compilados correctamente (manifest.json existe)"

    # 7. Calentamiento de caché de producción
    echo "  [6/7] Calentando caché..."
    APP_ENV=prod php bin/console cache:warmup || handle_error "Cache warmup falló"

    echo "  [7/7] Sincronizando con Git..."
    # Agregar TODOS los cambios (deletes + nuevos assets con hashes)
    git add . || handle_error "Git add falló"
    
    # Forzar inclusión de public/assets (importante para Vercel, evita .gitignore issues)
    git add -f public/assets/ 2>/dev/null || true
    
    echo ""
    read -p "  [7/7] Mensaje del commit: " commit_msg
    
    if [ -z "$commit_msg" ]; then
        commit_msg="Build de producción - $(date '+%Y-%m-%d %H:%M:%S')"
    fi

    echo ""
    echo "→ Commiteando cambios..."
    git commit -m "$commit_msg" || handle_error "Git commit falló"

    echo "→ Subiendo a GitHub (Vercel se desplegará automáticamente)..."
    git push || handle_error "Git push falló"

    echo ""
    echo "✔ Deploy completado exitosamente."
    echo "   Los cambios se han pushed a GitHub y Vercel debería desplegar automáticamente."
    ;;

  *)
    echo "Uso:"
    echo "  $0 dev     → Volver a entorno de desarrollo (local)"
    echo "  $0 prod    → Generar build y subir a producción (Vercel)"
    ;;
esac