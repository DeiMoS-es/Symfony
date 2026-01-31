# Sistema de Invitaciones - Verificación y Testing

## ✅ Checklist de Refactorización Completada

### Estructura SOLID
- [x] **S (Single Responsibility):**
  - `InvitationService::sendInvitation()` → solo crea e invita
  - `InvitationService::acceptInvitation()` → solo acepta y agrega
  - `InvitationService::getValidInvitation()` → solo busca y valida
  - `validateEmail()` → privado, responsabilidad única
  - `sendInvitationEmail()` → privado, responsabilidad única

- [x] **O (Open/Closed):**
  - `GroupInvitation` es cerrada a modificación (constructor completo)
  - `Group::addUser()` es abierta a extensión (se puede reescribir sin afectar consumidores)

- [x] **L (Liskov Substitution):**
  - `GroupInvitation` respeta su contrato (siempre devuelve valores válidos)
  - Métodos pueden reemplazarse sin romper el sistema

- [x] **I (Interface Segregation):**
  - `InvitationService` expone solo métodos necesarios
  - Controlador solo usa los métodos que necesita
  - Inyección de `MailerInterface`, `UrlGeneratorInterface` (no depende de implementaciones)

- [x] **D (Dependency Inversion):**
  - Depende de `EntityManagerInterface` (abstracción)
  - Depende de `MailerInterface` (abstracción)
  - Depende de `UrlGeneratorInterface` (abstracción)
  - No hay acoplamiento a clases concretas

### Arquitectura DDD
- [x] **Entidad (GroupInvitation):**
  - Constructor que garantiza estado válido
  - Métodos de dominio (`isExpired()`)
  - Immutable timestamps

- [x] **Servicio de Aplicación (InvitationService):**
  - Orquesta casos de uso
  - Coordina persistencia, notificación, validación
  - Manejo centralizado de excepciones

- [x] **Controlador:**
  - Mapea HTTP a comandos del servicio
  - Maneja respuestas (redirects, renders)
  - No contiene lógica de negocio

### Code Quality
- [x] No hay código duplicado
- [x] `GroupInvitationHandler` deprecado pero funcional (proxy a `InvitationService`)
- [x] Métodos privados para responsabilidades internas
- [x] Excepciones específicas (`InvalidArgumentException`, `RuntimeException`)
- [x] Documentación con PhpDoc

---

## 🧪 Testing Manual

### Paso 1: Preparar Entorno
```bash
cd /Users/nagibdelgadomorales/Proyectos/PhP/Symfony/club-cine

# Limpiar caché
php bin/console cache:clear

# Actualizar autoload
composer dump-autoload

# Verificar migraciones
php bin/console doctrine:migrations:status
```

### Paso 2: Probar Envío de Invitación

**Escenario:** Usuario A invita a usuario B

1. Accede a un grupo desde el dashboard
2. En la sección de invitaciones, ingresa un email válido (ej: test@example.com)
3. Haz clic en "Enviar Invitación"

**Esperado:**
- ✅ Flash message: "Invitación enviada correctamente a test@example.com"
- ✅ En la BD: Nueva fila en `group_invitations` con token único
- ✅ Email enviado (verifica logs o buzón de prueba)

**Si hay error:**
```bash
# Ver logs
tail -f var/log/dev.log

# Verificar configuración de email
php bin/console debug:config mailer
```

### Paso 3: Probar Aceptación - Usuario Logueado

**Escenario:** Usuario invitado acepta la invitación (ya tiene cuenta)

1. Obtén el token de la BD:
```bash
sqlite3 var/data.db "SELECT token FROM group_invitations LIMIT 1;"
```

2. Abre en navegador:
```
http://localhost:8000/join/group/{TOKEN_AQUI}
```

3. (Si está logueado)

**Esperado:**
- ✅ Página de bienvenida al grupo
- ✅ Usuario aparece en la lista de miembros del grupo
- ✅ Invitación eliminada de BD

**Código para verificar en la BD:**
```bash
# Ver miembro agregado
sqlite3 var/data.db "SELECT * FROM group_members WHERE user_id = {USER_ID};"

# Verificar invitación eliminada
sqlite3 var/data.db "SELECT * FROM group_invitations WHERE token = '{TOKEN}';"
```

### Paso 4: Probar Aceptación - Usuario NO Logueado

**Escenario:** Usuario invitado no tiene cuenta

1. Abre incógnito o cierra sesión
2. Accede al enlace de invitación:
```
http://localhost:8000/join/group/{TOKEN}
```

**Esperado:**
- ✅ Redirige a `/register/{TOKEN}?email=invitado@example.com`
- ✅ Formulario pre-llena el email
- ✅ Después de registrarse, redirige automáticamente a aceptar la invitación

### Paso 5: Probar Expiración

**Escenario:** Token expirado (>48 horas)

1. En la BD, actualiza una invitación para que esté expirada:
```bash
sqlite3 var/data.db "UPDATE group_invitations SET expires_at = datetime('now', '-1 day') WHERE id = {ID};"
```

2. Intenta usar el token:
```
http://localhost:8000/join/group/{TOKEN_EXPIRADO}
```

**Esperado:**
- ✅ Flash error: "La invitación no existe, ha expirado o ya ha sido utilizada."
- ✅ Redirige a home
- ✅ Invitación eliminada de BD (limpieza automática)

### Paso 6: Probar Validación de Email

**Escenario:** Email inválido

1. Intenta enviar invitación con email inválido: `"invalid@"`
2. Haz clic en enviar

**Esperado:**
- ✅ Flash error: "Validación: El email 'invalid@' no es válido."
- ✅ NO se crea invitación en BD
- ✅ Redirige al grupo

---

## 🔍 Auditoría de Código

### InvitationService
```php
// Lectura rápida de responsabilidades:
✅ sendInvitation() → crea, persiste, envía
✅ getValidInvitation() → busca, valida expiración
✅ acceptInvitation() → agrega usuario, limpia
✅ sendInvitationEmail() → privado, solo email
✅ validateEmail() → privado, solo validación
```

### InvitationController
```php
✅ invite() → mapea request → llama service → responde
✅ acceptInvitation() → maneja flujo de autenticación → llama service
✅ Manejo de excepciones → flash messages claros
✅ Sin lógica de negocio en el controlador
```

### GroupInvitation Entity
```php
✅ Constructor garantiza estado válido
✅ Token generado automáticamente
✅ Expiración automática a 48h
✅ Método isExpired() contiene lógica de dominio
✅ Getters inmutables
```

### Group Entity
```php
✅ addUser() evita duplicados internamente
✅ getUsers() devuelve Collection tipada
✅ Responsabilidades claras
```

---

## 📋 Rutas Registradas

Verifica que existan en tu aplicación:

```bash
php bin/console debug:router | grep -E "(app_group_invite|app_group_accept_invitation)"
```

**Esperado:**
```
app_group_invite                 POST   /group/{id}/invite
app_group_accept_invitation      GET    /join/group/{token}
```

---

## 🚀 Deployment Checklist

Antes de pasar a producción:

- [ ] ✅ Tests unitarios pasen (`PHPUnit`)
- [ ] ✅ Tests funcionales pasen (aceptación)
- [ ] ✅ Configurar variables de entorno:
  ```env
  MAILER_FROM=no-reply@tudominio.com
  MAILER_DSN=smtp://...
  ```
- [ ] ✅ Ejecutar migraciones en BD de producción
- [ ] ✅ Verificar plantilla de email en producción
- [ ] ✅ Rate limiting en endpoint POST (middleware)
- [ ] ✅ Logs configurados para auditoría
- [ ] ✅ Backup de BD antes de desplegar

---

## 🐛 Troubleshooting

### "El email no se envía"
```bash
# Ver configuración
php bin/console debug:config mailer

# Probar envío manual
php bin/console make:mail TestMail
```

### "Token no válido después de registrarse"
- Verificar que `_target_path` se pase correctamente
- Revisar configuración de sesiones
- Comprobar logs en `var/log/dev.log`

### "Usuario no aparece en el grupo"
- Verificar que `Group::addUser()` se ejecute
- Revisar BD: tabla `group_members`
- Comprobar que el flush se ejecute en `InvitationService`

### "Invitación no se elimina tras aceptar"
- Verificar que `em->remove()` y `em->flush()` se ejecuten
- Revisar excepciones en logs
- Comprobar estado de transacción Doctrine

---

**Última actualización:** 31 de Enero de 2026
**Estado:** ✅ Refactorización Completada y Lista para Testing
