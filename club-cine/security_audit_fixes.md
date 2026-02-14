# Auditoría de Seguridad - Hallazgos y Prompts de Corrección

## Vulnerabilidades en Dependencias

### 1. Symfony http-foundation - CVE-2025-64500 (High)
**Descripción:** Incorrect parsing of PATH_INFO can lead to limited authorization bypass.

**Prompt para corrección:**
Actualiza Symfony a la versión 7.3.7 o superior para resolver esta vulnerabilidad. Ejecuta `composer update symfony/http-foundation` y verifica que la versión sea segura. Asegúrate de probar la aplicación después de la actualización para confirmar que no hay regressions en el routing y autorización.

### 2. PHPUnit - CVE-2026-24765 (High)
**Descripción:** Vulnerable to unsafe deserialization in PHPT code coverage handling.

**Prompt para corrección:**
Actualiza PHPUnit a la versión 12.5.8 o superior. Como es una dependencia de desarrollo, ejecuta `composer update --dev phpunit/phpunit`. Si usas versiones anteriores, actualiza a 11.5.50, 10.5.62, 9.6.33 o 8.5.52 según corresponda.

### 3. Symfony process - CVE-2026-24739 (Medium)
**Descripción:** Incorrect argument escaping under MSYS2/Git Bash can lead to destructive file operations on Windows.

**Prompt para corrección:**
Actualiza Symfony process a 7.3.7 o superior. Ejecuta `composer update symfony/process`. Si el proyecto se ejecuta en Windows con MSYS2/Git Bash, prioriza esta actualización.

## Mejoras de Seguridad Recomendadas

### 4. Implementar Rate Limiting en Autenticación
**Descripción:** No hay protección contra ataques de fuerza bruta en endpoints de login y registro.

**Prompt para corrección:**
Implementa rate limiting usando Symfony Rate Limiter. Crea un RateLimiter para login attempts (ej. 5 intentos por minuto por IP). Configura en security.yaml o crea un listener/middleware. Usa `symfony/rate-limiter` si no está instalado. Aplica a rutas /auth/login y /auth/register. Registra intentos fallidos en logs.

### 5. Mejorar Configuración de Email en Invitaciones
**Descripción:** El email 'from' está hardcodeado y debe coincidir con la cuenta SMTP autenticada.

**Prompt para corrección:**
Haz configurable el email 'from' usando variables de entorno (ej. MAILER_FROM). Actualiza InvitationService para usar `'%env(MAILER_FROM)%'`. Asegúrate de que coincida con la configuración SMTP. Documenta en .env.example.

### 6. Revisar Content Security Policy (CSP)
**Descripción:** CSP enforce true con políticas restrictivas podría romper funcionalidades si se usan assets externos.

**Prompt para corrección:**
Revisa si la aplicación usa CDNs o assets externos. Si es así, ajusta las políticas en nelmio_security.yaml para permitir dominios necesarios (ej. 'https://cdn.jsdelivr.net'). Mantén report-uri configurado y maneja reports en un endpoint /csp-report. Considera usar CSP nonces para scripts inline si es necesario.

### 7. Implementar Logging de Seguridad
**Descripción:** No hay logs específicos para eventos de seguridad como logins fallidos, cambios de password, etc.

**Prompt para corrección:**
Configura un canal de Monolog para 'security'. Crea handlers para loggear eventos como login attempts, token revocations, invitation accepts. Usa en controladores/servicios relevantes. En prod, envía a un sistema centralizado si es posible.

### 8. Validar Protección contra XSS en Templates
**Descripción:** Asegurar que outputs en templates no permitan inyección de HTML/JS.

**Prompt para corrección:**
Revisa todos los templates que usan variables dinámicas (ej. {{ error }}, {{ email }}). Usa |escape o |raw solo cuando sea seguro. Implementa Content Security Policy para mitigar riesgos. Prueba con inputs maliciosos.

### 9. Verificar Principios SOLID y Estructura Modular
**Descripción:** La estructura parece seguir SOLID, pero confirmar.

**Prompt para corrección:**
Revisa cada módulo para asegurar Single Responsibility (ej. AuthService solo autenticación). Verifica Dependency Inversion (interfaces para servicios). Mantén separación de concerns. Si hay violaciones, refactoriza clases grandes en servicios más pequeños.

### 10. Auditoría General de Exposición de Datos
**Descripción:** Verificar que no se expongan datos sensibles en respuestas JSON, logs, etc.

**Prompt para corrección:**
Revisa respuestas de API para no incluir passwords, tokens en logs. Usa sanitización en inputs. Implementa data masking en logs si es necesario. Asegúrate de que errores no revelen información interna.