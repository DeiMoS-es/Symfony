# Scripts

Esta carpeta contiene scripts auxiliares para el desarrollo y despliegue del proyecto Club Cine.

## Scripts disponibles

### `env-switch.sh`

Script para cambiar entre entornos de desarrollo y producción de Symfony.

**Uso:**
```bash
./scripts/env-switch.sh [dev|prod]
```

**Opciones:**

- **`dev`** - Prepara el entorno de desarrollo
  - Elimina la carpeta `vendor/`
  - Instala todas las dependencias con Composer
  - Limpia la caché de Symfony

- **`prod`** - Genera el build de producción para Vercel
  - Elimina la carpeta `vendor/`
  - Instala dependencias optimizadas para producción (sin dependencias de desarrollo)
  - Instala el mapa de importación
  - Limpia la caché para entorno de producción
  - Agrega los archivos necesarios al repositorio
  - Solicita un mensaje de commit
  - Realiza push automático a GitHub para desplegar en Vercel

**Nota:** Requiere tener Git configurado y permisos de push en el repositorio.
