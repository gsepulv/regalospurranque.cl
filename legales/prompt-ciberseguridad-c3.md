# Prompt para Claude Code — Mejoras de Ciberseguridad (Auditoría C.3)

Lee `legal/PARCHE-LEGAL.md` para entender el flujo de trabajo y restricciones del proyecto.

## Contexto

El sitio regalospurranque.cl / v2.regalos.purranque.info ya tiene implementado el parche legal (banner cookies, formularios ARCO, headers básicos de seguridad, cláusulas legales). Ahora necesito implementar las mejoras de ciberseguridad pendientes de la Auditoría C.3.

## Tareas a ejecutar (en orden)

### 1. CAPTCHA en formulario de reseñas [CRÍTICO]

El formulario de reseñas no tiene protección anti-bot. Cualquier bot puede enviar reseñas falsas.

Implementar:
- **hCaptcha invisible** (preferido sobre reCAPTCHA por privacidad). Si no es viable, usar reCAPTCHA v3.
- Agregar el script de hCaptcha en el layout donde está el formulario de reseñas.
- Agregar el widget invisible al formulario.
- Validar el token en el backend (server-side) antes de procesar la reseña.
- Si la validación falla, rechazar el envío con mensaje amigable.

**IMPORTANTE:** Las API keys de hCaptcha NO van en el repo (es público). Crear un archivo de configuración para las keys que esté en `.gitignore`, igual que la config de BD. En el código usar un `require`/`include` al archivo de config.

### 2. Rate limiting por IP en reseñas [CRÍTICO]

Implementar rate limiting server-side:
- Máximo **3 reseñas por hora por IP**.
- Máximo **10 reseñas por día por IP**.
- Antes de insertar una reseña, verificar en BD cuántas ha enviado esa IP en la última hora/día.
- Si excede el límite, mostrar mensaje: "Has enviado demasiadas reseñas. Intenta de nuevo más tarde."
- Registrar el intento bloqueado en logs (para detectar ataques).

Buscar dónde se procesan las reseñas en el código (probablemente en `app/` o en el controller que maneja el POST del formulario de reseñas) y agregar la validación ahí.

### 3. Moderación previa de reseñas [CRÍTICO]

Las reseñas NO deben publicarse automáticamente. Implementar cola de aprobación:
- Buscar el nombre real de la tabla de reseñas en la BD (puede ser `resenas`, `reviews`, `opiniones`, etc.).
- **Indicarme el nombre de la tabla** para que yo ejecute el ALTER TABLE en phpMyAdmin:
  ```sql
  ALTER TABLE [nombre_tabla] ADD COLUMN moderado TINYINT(1) NOT NULL DEFAULT 0;
  ALTER TABLE [nombre_tabla] ADD COLUMN moderado_por INT DEFAULT NULL;
  ALTER TABLE [nombre_tabla] ADD COLUMN moderado_at DATETIME DEFAULT NULL;
  ```
- Modificar la query pública que muestra reseñas para que solo muestre las que tienen `moderado = 1`.
- Al insertar una reseña nueva, que se guarde con `moderado = 0` (pendiente).
- Mostrar al usuario un mensaje después de enviar: "¡Gracias! Tu reseña será revisada y publicada pronto."
- En el panel admin (si existe), agregar vista de reseñas pendientes de moderación.

### 4. Headers de seguridad adicionales [ALTO]

El `.htaccess` ya tiene headers básicos (X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, X-XSS-Protection). Falta agregar:

- **Content-Security-Policy** básico y permisivo (para no romper funcionalidad):
  ```apache
  Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://hcaptcha.com https://*.hcaptcha.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' https://hcaptcha.com https://*.hcaptcha.com; frame-src https://hcaptcha.com https://*.hcaptcha.com;"
  ```
- Ajustar el CSP si el sitio usa CDNs, Google Fonts, o scripts externos (revisar el HTML para detectar dominios externos).
- **Strict-Transport-Security** (HSTS):
  ```apache
  Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
  ```

### 5. Protección de datos de contacto en fichas [MEDIO]

En las fichas de comercios, los emails y teléfonos están expuestos directamente en el HTML (scraping fácil).

Implementar:
- **Ofuscación de email:** No mostrar el email en texto plano. Usar JavaScript para decodificarlo al hacer clic (ej: `data-email` con valor codificado en base64, y un onclick que lo decodifica y abre mailto:).
- **Botón "Enviar mensaje"** en vez de mostrar el email directamente.
- Para WhatsApp: está bien mostrar el enlace wa.me, pero verificar que no se muestre el número como texto plano innecesariamente.

### 6. CAPTCHA en formulario de contacto [MEDIO]

El formulario de contacto (/contacto) también necesita protección anti-bot:
- Agregar el mismo hCaptcha que en reseñas.
- Validar server-side antes de procesar.

### 7. CAPTCHA en formulario ARCO [BAJO]

El formulario de ejercicio de derechos (/derechos) ya tiene rate limiting (3 por email/24h), pero agregar hCaptcha como capa adicional para prevenir abuso automatizado.

---

## Restricciones (recordatorio)

- **Repo público:** API keys y secrets van en archivo de config en `.gitignore`
- **BD la gestiona Gustavo:** No ejecutar mysql. Generar el SQL y decirme qué ejecutar en phpMyAdmin
- **Apache/.htaccess:** Headers van ahí, no en nginx.conf
- **No tocar CLAUDE.md** del proyecto
- **Al terminar:** git add, commit, push y avisarme para deploy en cPanel

## Orden de prioridad

1. CAPTCHA en reseñas (CRÍTICO)
2. Rate limiting en reseñas (CRÍTICO)
3. Moderación previa de reseñas (CRÍTICO)
4. Headers CSP + HSTS (ALTO)
5. Ofuscación de emails en fichas (MEDIO)
6. CAPTCHA en contacto (MEDIO)
7. CAPTCHA en ARCO (BAJO)

Empieza explorando la estructura del proyecto para encontrar: el formulario de reseñas, el controller que lo procesa, la tabla de reseñas, el formulario de contacto, y los scripts externos que usa el sitio (para el CSP). Luego ejecuta las tareas en orden de prioridad.
