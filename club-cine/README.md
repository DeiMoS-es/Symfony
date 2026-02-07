# 🎬 CineClub App

Una aplicación web desarrollada con **Symfony 7** para digitalizar y mejorar la experiencia del cine club creado por mi pareja y sus compañeros de trabajo. Cada semana el grupo elige una película, la ve y comparte sus valoraciones y comentarios. Esta app reemplaza el uso de hojas de Excel por una interfaz moderna, accesible y colaborativa.

---

## 🚀 Objetivo

Facilitar la participación en el cine club mediante una plataforma web que permita:

- Registrar usuarios y gestionar sesiones.
- Crear grupos de amigos para compartir películas y valoraciones.
- Puntuar películas en distintos aspectos (guion, dirección, actuación, etc.).
- Escribir comentarios personales sobre cada película.
- Visualizar estadísticas y rankings semanales.
- Fomentar el debate y la interacción entre los miembros.

---

## 🧩 Arquitectura modular

El proyecto está organizado en módulos independientes dentro de `src/Module`, cada uno con su propio README y responsabilidades bien definidas:

- 📄 [Módulo Auth](src/Module/Auth/README.md)
- 📄 [Módulo Group](src/Module/Group/README.md)
- 📄 [Módulo Movie](src/Module/Movie/README.md) — Implementado: integración con TMDb, sincronización y persistencia de películas; catálogo y dashboard en la UI.

---

## 🛠️ Tecnologías utilizadas

- **Backend:** Symfony 7 (PHP 8.2)
- **Frontend:** Twig + Bootstrap 5
- **Base de datos:** MySQL (desarrollo) y SQLite en memoria para tests
- **Autenticación:** JWT + cookies (LexikJWTAuthenticationBundle)
- **Control de versiones:** Git + GitLab

---

## 📦 Estado actual del proyecto

**Módulos principales:**
- ✅ **Auth**: Registro, login con JWT + refresh token, cookies (`ACCESS_TOKEN`/`REFRESH_TOKEN`), rotación segura
- ✅ **Group**: Creación de grupos, gestión de miembros, recomendaciones, reviews con puntuación por aspectos
- ✅ **Movie**: Catálogo TMDb sincronizado, búsqueda por título, persistencia local de películas y géneros
- ✅ **Group - Invitaciones**: Sistema completo de invitaciones por email con tokens, expiración y flujo seguro

**Funcionalidades implementadas:**
- ✅ Registro de usuarios (API JSON)
- ✅ Inicio de sesión con JWT + refresh token y rotación automática
- ✅ Creación de grupos de amigos (modelo y migraciones completas)
- ✅ Sistema de recomendaciones y reviews dentro de grupos
- ✅ Puntuación por aspectos (guion, dirección, actuación, etc.) con `averageScore` automático
- ✅ Búsqueda de películas por título (TMDb + BD local) con UI integrada
- ✅ Comando para cierre automático de recomendaciones y cálculo de estadísticas
- ✅ **Invitaciones a grupos por email** (tokens únicos, expiración 48h, flujo de registro integrado)
- ✅ Sistema de refresh tokens con rotación y revocación

**En desarrollo / Próximos pasos:**
- 🔜 Panel de administración
- 🔜 Visualización de rankings y estadísticas agregadas (UI avanzada)
- 🔜 Endpoints de votación/edición de voto (API completa)

## 📣 Últimos cambios (2026-02-07)

**Sistema de Invitaciones a Grupos - COMPLETADO ✅**
- Implementación del sistema completo de invitaciones por email (módulo Group).
- Servicio `InvitationService` que orquesta: creación, validación y aceptación de invitaciones.
- Controlador `InvitationController` con rutas POST (enviar) y GET (aceptar con token).
- Flujo seguro: validación de emails, tokens únicos, expiración automática en 48h, control de integridad.
- **Corrección crítica resuelta:** Email en `->from()` debe coincidir exactamente con la cuenta SMTP autenticada.
- Configuración de email SMTP con **Mailtrap** para desarrollo/testing.
- Documentación completa: [INVITATION_SYSTEM.md](INVITATION_SYSTEM.md) con arquitectura DDD y principios SOLID.
- [Ver documentación de invitaciones](INVITATION_SYSTEM.md) para detalles técnicos y troubleshooting.

**Cambios anteriores:**
- Implementación de búsqueda de películas por título en TMDb mediante `MovieService::getSearchCatalog()`. 🔍
- Nuevos métodos en `TmdbService`: `searchCatalog()` para búsquedas transformadas a DTOs. ✅

---

## ▶️ Puesta en marcha rápida

```bash
git clone https://github.com/tu-usuario/cineclub-app.git
cd cineclub-app

# Instalar dependencias
composer install

# Configurar variables de entorno (editar .env o crear .env.local)
# - DATABASE_URL (MySQL para producción; SQLite en memoria para tests)
# - JWT_PRIVATE_KEY_PATH, JWT_PUBLIC_KEY_PATH, JWT_PASSPHRASE (autenticación JWT)
# - TMDB_API_KEY, TMDB_READ_TOKEN (integración con TMDb para catálogo de películas)
# - MAILER_DSN (configuración SMTP para invitaciones por email):
#   Ejemplo Mailtrap (desarrollo): "smtp://usuario:contraseña@sandbox.smtp.mailtrap.io:2525"
#   Ejemplo Gmail (producción): "smtp://email%40gmail.com:app_password@smtp.gmail.com:465?encryption=ssl&auth_mode=login"

# Ejecutar migraciones de base de datos
php bin/console doctrine:migrations:migrate

# Limpiar caché después de cambios en .env
php bin/console cache:clear

# Arrancar el servidor de desarrollo
symfony server:start -d
# o: php -S localhost:8000 -t public
```

**Notas importantes sobre email:**
- El email en `->from()` debe coincidir exactamente con la cuenta autenticada en SMTP.
- Para Gmail, se requiere **2FA** habilitado y usar una **App Password** (no la contraseña de la cuenta).
- En desarrollo, **Mailtrap** es recomendado para pruebas sin restricciones de seguridad de Gmail.

---

## 🧪 Tests

- Ejecutar toda la batería de tests (usa `.env.test` con SQLite en memoria):
  - `php bin/phpunit`
- Ejecutar un test concreto del módulo de autenticación:
  - `php bin/phpunit tests/Module/Auth/AuthModuleTest.php`

---

## 📂 Módulos principales

### Auth (`src/Module/Auth`)
Maneja el registro de usuarios, login, generación de JWT/refresh tokens y cierre de sesión. Expone endpoints JSON (`/auth/register`, `/auth/login`, `/auth/refresh`) y se integra con Symfony Security + LexikJWT.

### Group (`src/Module/Group`)
Modela los grupos del cine club, su membresía y las recomendaciones/reviews internas entre miembros. Incluye la lógica para cerrar automáticamente recomendaciones cuando expira la fecha límite.

### Movie (`src/Module/Movie`)
Se encarga de la integración con TMDb, el catálogo de películas y la persistencia local de `Movie` y `Genre`. Proporciona un dashboard de películas populares y servicios para sincronizar datos desde la API externa.
