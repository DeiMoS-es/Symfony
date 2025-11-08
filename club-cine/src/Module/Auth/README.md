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
- [ ] Registro de usuarios
- [ ] Inicio de sesión
- [ ] Recuperación de contraseña
- [ ] Gestión de roles
- [ ] Autenticación mediante JWT
- [ ] Protección CSRF
- [ ] Validación de datos

## Configuración
La configuración principal del módulo se encuentra en:
- `config/packages/security.yaml`
- `config/routes.yaml` (rutas de autenticación)

## Dependencias
- symfony/security-bundle
- lexik/jwt-authentication-bundle (pendiente de implementar)

## Historial de Implementación
1. Creación de la estructura base del módulo Auth (07/11/2025)
2. Configuración inicial de security.yaml

## Plan de Implementación Detallado

### 1. Configuración Base ✓
- [x] Creación de la estructura base del módulo Auth
- [x] Configuración inicial de security.yaml

### 2. Implementación de Usuario
- [ ] Crear entidad User con campos básicos:
  - [ ] email
  - [ ] password
  - [ ] roles
  - [ ] createdAt
  - [ ] updatedAt
- [ ] Generar migración de base de datos
- [ ] Implementar UserRepository
- [ ] Crear UserProvider personalizado

### 3. Sistema de Autenticación
- [ ] Configurar Guard Authenticator
- [ ] Implementar LoginFormAuthenticator
- [ ] Crear formulario de login (LoginType)
- [ ] Desarrollar controlador de autenticación (SecurityController)
- [ ] Implementar páginas de login y registro

### 4. Registro de Usuarios
- [ ] Crear formulario de registro (RegistrationType)
- [ ] Implementar RegistrationController
- [ ] Añadir validaciones de datos
- [ ] Configurar email de verificación
- [ ] Implementar confirmación de cuenta

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
- [ ] Instalar lexik/jwt-authentication-bundle
- [ ] Generar claves JWT
- [ ] Configurar autenticación JWT
- [ ] Crear endpoints de API para login/refresh

### 8. Seguridad y Optimización
- [ ] Implementar CSRF protection
- [ ] Añadir rate limiting
- [ ] Configurar headers de seguridad
- [ ] Implementar logging de accesos
- [ ] Realizar tests de seguridad

## Estado Actual
- Fase actual: Configuración Base
- Próxima fase: Implementación de Usuario

## Notas de Seguridad
- Las contraseñas se almacenarán utilizando el algoritmo de hash bcrypt
- Implementación de protección CSRF en todos los formularios
- Validación de datos tanto en el cliente como en el servidor