# Módulo de Autenticación

## Descripción
Este módulo maneja toda la autenticación y autorización de usuarios en la aplicación Club de Cine. Implementa las funcionalidades de registro, inicio de sesión, recuperación de contraseña y gestión de roles de usuario.

## Estructura del Módulo
```
Auth/
├── Controller/     # Controladores de autenticación
├── Entity/        # Entidades relacionadas con usuarios y roles
├── Repository/    # Repositorios para acceso a datos
├── Service/       # Servicios de autenticación
└── Security/      # Clases relacionadas con la seguridad
```

## Características Implementadas
- [x] Registro de usuarios (con test automatizado)
- [x] Inicio de sesión (LoginController implementado)
- [x] Generación de JWT y Refresh tokens
- [x] Mappers de datos (UserMapper, DTOs)
- [x] Logout (revoca refresh token, borra cookies y invalida sesión)
- [ ] Recuperación de contraseña
- [ ] Gestión de roles
- [x] Protección CSRF (implementada en formularios)
- [x] Validación de datos (en registro)

## Configuración
La configuración principal del módulo se encuentra en:
- `config/packages/security.yaml`
- `config/routes.yaml` (rutas de autenticación)
- `config/packages/nelmio_security.yaml` (configuración de headers de seguridad)

## Dependencias
- symfony/security-bundle
- doctrine/orm
- sqlite3 (para tests en memoria)
- lexik/jwt-authentication-bundle (pendiente de implementar)
- nelmio/security-bundle (para headers de seguridad)

## Historial de Implementación
1. Creación de la estructura base del módulo Auth (07/11/2025)
2. Configuración inicial de security.yaml
3. Implementación del test `RegistrationControllerTest` usando SQLite en memoria (08/11/2025)
4. Implementación de LoginController con generación de JWT y Refresh tokens (11/11/2025)
5. Creación de UserMapper y DTOs para mapeo de datos (11/11/2025)
6. Refactorización de LoginController para delegar en AuthService (11/11/2025)

## Plan de Implementación Detallado

### 1. Configuración Base ✓
- [x] Creación de la estructura base del módulo Auth
- [x] Configuración inicial de security.yaml

### 2. Implementación de Usuario
- [x] Crear entidad User con campos básicos:
  - [x] email
  - [x] password
  - [x] roles
  - [x] createdAt
  - [x] updatedAt
- [ ] Generar migración de base de datos
- [ ] Implementar UserRepository
- [ ] Crear UserProvider personalizado

### 3. Sistema de Autenticación
- [ ] Configurar Guard Authenticator
- [ ] Implementar LoginFormAuthenticator
- [ ] Crear formulario de login (LoginType)
- [x] Desarrollar controlador de autenticación (LoginController)
- [ ] Implementar páginas de login y registro

### 4. Registro de Usuarios ✓
- [x] Crear formulario de registro (RegistrationType)
- [x] Implementar RegistrationController
- [x] Añadir validaciones de datos
- [ ] Configurar email de verificación
- [ ] Implementar confirmación de cuenta
- [x] Test automatizado con SQLite en memoria (`RegistrationControllerTest`)

### 5. Sistema de Roles
- [ ] Definir roles base (ROLE_USER, ROLE_ADMIN)
- [ ] Implementar RoleHierarchy
- [ ] Crear voters personalizados si son necesarios
- [ ] Configurar acceso por roles en security.yaml

### 6. Recuperación de Contraseña
- [ ] Crear formulario de recuperación
- [ ] Implementar sistema de tokens
- [ ] Configurar envío de emails
- [ ] Crear páginas de reset password

### 7. Implementación JWT
- [x] Instalar lexik/jwt-authentication-bundle
- [x] Generar claves JWT
- [x] Configurar autenticación JWT
- [x] Crear endpoints de API para login/refresh

### 8. Seguridad y Optimización
- [x] Implementar CSRF protection
- [ ] Añadir rate limiting
- [ ] Configurar headers de seguridad
- [x] Implementar logging de accesos
- [ ] Realizar tests de seguridad

## Estado Actual
- Fase actual: Implementación de sistema de login y generación de tokens JWT
- Próxima fase: Implementación de UserRepository y refactorización de seguridad
- Status: **En progreso** - LoginController funcional con delegación en AuthService

## Notas de Seguridad
- Las contraseñas se almacenan utilizando el algoritmo de hash bcrypt
- Implementación de protección CSRF en todos los formularios
- Validación de datos tanto en el cliente como en el servidor
- Los tests se realizan usando SQLite en memoria para no afectar la base de datos real