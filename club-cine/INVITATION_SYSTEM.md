# Sistema de Invitaciones a Grupos - Documentación SOLID

## Visión General

El sistema de invitaciones permite que los usuarios inviten a otros (por email) a unirse a grupos de películas. Sigue principios DDD (Domain-Driven Design) y SOLID.

## Arquitectura

```
┌─────────────────────────────────────────────────────────────┐
│ Module/Group                                                 │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Entity (Modelo de Dominio)                           │   │
│  │ - GroupInvitation (Agregado raíz)                    │   │
│  │   • Responsabilidad: Representar una invitación      │   │
│  │   • token (ID único)                                 │   │
│  │   • email (destino)                                  │   │
│  │   • targetGroup (referencia al grupo)                │   │
│  │   • expiresAt (validez temporal)                     │   │
│  │   • isExpired() (lógica de dominio)                  │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Service (Lógica de Aplicación)                       │   │
│  │ - InvitationService                                  │   │
│  │   • sendInvitation(email, groupId)                   │   │
│  │   • getValidInvitation(token)                        │   │
│  │   • acceptInvitation(invitation, user)               │   │
│  │   • Responsabilidades:                               │   │
│  │     - Orquestar creación, validación y aceptación    │   │
│  │     - Coordinar con EntityManager (persistencia)     │   │
│  │     - Integrar con MailerInterface (notificación)    │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Controller (Presentación HTTP)                       │   │
│  │ - InvitationController                               │   │
│  │   • POST /group/{id}/invite                          │   │
│  │   • GET /join/group/{token}                          │   │
│  │   • Responsabilidades:                               │   │
│  │     - Convertir Request HTTP a comandos              │   │
│  │     - Responder con vistas o redirects               │   │
│  │     - Manejo básico de errores                       │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ Templates (Presentación)                             │   │
│  │ - emails/group_invitation.html.twig                  │   │
│  │   • Email profesional con CTA                        │   │
│  │   • Información de expiración                        │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Flujos

### 1. Enviar Invitación (POST /group/{id}/invite)

```
Controller
    ↓
    → InvitationService::sendInvitation(email, groupId)
        ↓
        → validateEmail(email)           [Single Responsibility]
        → find Group by ID                [Dependency Injection]
        → new GroupInvitation(email, group) [Constructor pattern]
        → em->persist() + em->flush()    [Persistence]
        → sendInvitationEmail()          [Dependency Injection - MailerInterface]
            → UrlGeneratorInterface::generate() [Absolute URL]
            → TemplatedEmail + mailer->send()
        ↓
Controller → Redirect + Flash Message
```

**Principios SOLID aplicados:**
- **S (Single Responsibility):** `validateEmail()` solo valida; `sendInvitationEmail()` solo enva el email
- **D (Dependency Injection):** Mailer y UrlGenerator inyectados en constructor
- **I (Interface Segregation):** Se usan interfaces específicas (MailerInterface, etc.)

### 2. Aceptar Invitación (GET /join/group/{token})

```
Controller
    ↓
    → InvitationService::getValidInvitation(token)
        ↓
        → find by token
        → isExpired() ? [Lógica de Dominio] → remove + return null
        → return invitation
    ↓
    → User logged in?
        ├─ NO  → Redirect to register (pass email + target_path)
        └─ YES → InvitationService::acceptInvitation(invitation, user)
                    ↓
                    → Group.addUser(user) [Evita duplicados internamente]
                    → em->remove(invitation)
                    → em->flush()
                    → return group
    ↓
Controller → Render welcome + Flash message
```

**Principios SOLID aplicados:**
- **S (Single Responsibility):** Cada método tiene una responsabilidad clara
- **O (Open/Closed):** Group.addUser() es extensible; se puede agregar lógica sin modificar
- **L (Liskov Substitution):** GroupInvitation respeta su contrato
- **I (Interface Segregation):** Controller solo usa métodos que necesita
- **D (Dependency Inversion):** Depende de abstracciones (interfaces), no de implementaciones concretas

## Entidades y Mapeos

### GroupInvitation

```php
class GroupInvitation {
    private ?int $id;                    // Auto-increment
    private string $email;                // Email del invitado
    private string $token;                // Unique token (64 chars)
    private Group $targetGroup;           // FK a Group
    private DateTimeImmutable $createdAt; // Marca temporal
    private DateTimeImmutable $expiresAt; // Expira en 48h
    
    // Constructor genera token y expiración automáticamente
    public function __construct(string $email, Group $group)
    
    // Lógica de dominio
    public function isExpired(): bool
}
```

**Ventajas:**
- Toda la lógica de creación en el constructor (garantiza validez)
- `isExpired()` es lógica de dominio (no de aplicación)
- Immutable timestamps (DateTimeImmutable)

## Flujo de Usuario Completo

### Caso 1: Usuario registrado invita a otro

```
1. Usuario A en group/show.html.twig rellena formulario con email de B
2. POST /group/{id}/invite
3. InvitationService crea GroupInvitation y envía email
4. Usuario B recibe email con CTA
5. Usuario B hace clic → GET /join/group/{token}
6. Si User B está logueado:
   - Se añade al grupo
   - Ve página de bienvenida
7. Si User B no está logueado:
   - Redirige a registro
   - Después del registro → automáticamente a accept
```

### Caso 2: Token expirado

```
1. Usuario intenta usar link después de 48h
2. getValidInvitation(token) detecta expiración
3. Elimina la invitación de BD
4. Retorna null
5. Controller muestra error y redirige
```

## Estado Actual

**✅ COMPLETADO (2026-02-07)**
- Sistema de invitaciones **totalmente funcional** con todas las capas implementadas:
  - Entidad `GroupInvitation` con lógica de dominio (validación de expiración, tokens únicos)
  - Servicio `InvitationService` con orquestación completa (crear, validar, aceptar invitaciones)
  - Controlador `InvitationController` con rutas POST (enviar) y GET (aceptar con token)
  - Template de email profesional (`emails/group_invitation.html.twig`)
  - Manejo robusto de errores y validaciones a múltiples niveles
  
- **Resolución de issues críticos (2026-02-07):**
  - ✅ **Corrección de email en `->from()`**: Debe coincidir EXACTAMENTE con la cuenta SMTP autenticada
  - ✅ Configuración SMTP/TLS correcta para Mailtrap (desarrollo) y Gmail (producción)
  - ✅ URL-encoding de caracteres especiales en DSN (`@` → `%40`, etc.)
  - ✅ Manejo completo de flujo de expiración automática de invitaciones (48h)
  - ✅ Integración de sesión para el flujo de registro de usuarios no autenticados
  
- **Testing verificado (2026-02-07):**
  - ✅ Invitaciones se envían exitosamente vía Mailtrap (sandbox SMTP)
  - ✅ Tokens se validan correctamente y se marcan como expirados pasadas 48h
  - ✅ Flujo de usuario registrado: acepta invitación y se une al grupo
  - ✅ Flujo de usuario no registrado: redirige a registro con email y token en sesión
  - ✅ Expiración automática y limpieza de invitaciones expiradas
  - ✅ Mensajes flash informativos en cada paso del flujo
  
- Status: **Implementado, testado y validado en desarrollo** — Listo para migrar a Gmail en producción con App Password válida.

## Mejoras Futuras (Extendibilidad)

El diseño permite fácilmente agregar:

1. **Reutilizar invitaciones no expiradas**
   - Cambiar `acceptInvitation()` para no eliminar; marcar como "accepted"

2. **Múltiples intentos de invitación**
   - Agregar validación: "¿Ya existe invitación activa para este email?"

3. **Eventos de dominio**
   - Emitir `InvitationSentEvent`, `InvitationAcceptedEvent`
   - Listeners pueden loguear, hacer estadísticas, etc.

4. **Invitaciones en lotes**
   - Crear servicio `BulkInvitationService` que use `InvitationService`

5. **Sistema de permisos**
   - Validar que solo el owner o admin puedan invitar
   - Usar Voter de Symfony

## Testing

### Unit Tests (InvitationService)
```php
// test sendInvitation valida email
// test sendInvitation persiste invitación
// test getValidInvitation detecta expiración
// test acceptInvitation agrega usuario
```

### Integration Tests (Controller)
```php
// test invite POST crea invitation y envía email
// test acceptInvitation GET con usuario logueado
// test acceptInvitation GET redirige sin usuario
// test acceptInvitation GET con token expirado
```

## Consideraciones de Seguridad

✅ **Token seguro:** `bin2hex(random_bytes(32))` = 64 caracteres hexadecimales
✅ **Validación de email:** `filter_var(..., FILTER_VALIDATE_EMAIL)`
✅ **CSRF protegido:** Formularios Symfony incluyen token CSRF
✅ **Autenticación:** Redirige a login/register si no hay usuario
✅ **Expiración:** Automática a 48 horas

⚠️ **Mejoras futuras:**
- Rate limiting en el endpoint POST (evitar spam)
- Validar que no haya invitación pendiente activa
- Loguear intentos de invitación para auditoría

---

**Última actualización:** 7 de Febrero de 2026
**Autor:** Refactorización SOLID + Resolución de issues SMTP

---

## Configuración de Email (Guía de Setup)

### En Desarrollo (Mailtrap)
```dotenv
MAILER_DSN="smtp://usuario:contraseña@sandbox.smtp.mailtrap.io:2525"
```
El email en `->from()` puede ser cualquiera (no necesita coincidir con credenciales).

### En Producción (Gmail)
```dotenv
MAILER_DSN="smtp://correo%40gmail.com:app_password@smtp.gmail.com:465?encryption=ssl&auth_mode=login"
```

**Requisitos:**
1. 2FA habilitado en la cuenta Google
2. App Password generada en Google Account → Security → App passwords
3. Email en `->from()` DEBE coincidir exactamente con el email autenticado
4. Caracteres especiales en DSN deben estar URL-encoded (`@` → `%40`)

**Troubleshooting:**
- **Error 534 "Please log in with your web browser"**: Google está bloqueando. Acceder a https://accounts.google.com/DisplayUnlockCaptcha desde el navegador.
- **Error 535 "Invalid credentials"**: App Password inválida o contraseña normal usada en lugar de App Password.
- **Error con email FROM**: El email en `->from()` no coincide con el usuario autenticado. Debe ser idéntico.
- **SMTP connection refused**: Verificar puertos (25, 465, 587 para Gmail) y firewall.
