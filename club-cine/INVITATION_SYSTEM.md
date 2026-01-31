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

**Última actualización:** 31 de Enero de 2026
**Autor:** Refactorización SOLID
