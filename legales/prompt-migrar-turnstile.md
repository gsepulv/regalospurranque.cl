# Prompt para Claude Code — Migrar de hCaptcha a Cloudflare Turnstile

## Contexto

El sitio regalospurranque.cl / v2.regalos.purranque.info es un sistema PHP MVC artesanal (sin frameworks) en hosting compartido HostGator con cPanel. Recientemente se implementó hCaptcha en los formularios, pero se ha decidido migrar a **Cloudflare Turnstile** por las siguientes razones:

- **100% gratuito** sin límites ocultos para pymes
- **Mejor UX:** funciona en segundo plano sin puzzles visuales (crucial para usuarios rurales con conexiones lentas y dispositivos móviles)
- **Menos abandono de formularios** — no hay fricción para el usuario
- **No requiere plan Cloudflare** — funciona standalone en cualquier sitio

## Tarea: Migrar de hCaptcha a Cloudflare Turnstile

### 1. ELIMINAR hCaptcha

Buscar y eliminar todo lo relacionado con hCaptcha en el proyecto:

- **Frontend:** Buscar `hcaptcha`, `h-captcha`, `js.hcaptcha.com` en todas las vistas/templates y eliminar:
  - Scripts: `<script src="https://js.hcaptcha.com/1/api.js">`
  - Widgets: `<div class="h-captcha" data-sitekey="...">`
  - Cualquier referencia a hCaptcha en CSS o JS

- **Backend:** Buscar `hcaptcha`, `HCAPTCHA`, `hcaptcha.com/siteverify` en todos los archivos PHP y eliminar:
  - Validación server-side del token hCaptcha
  - Includes/requires al archivo de config de hCaptcha
  - Constantes `HCAPTCHA_SITE_KEY`, `HCAPTCHA_SECRET_KEY`, `HCAPTCHA_ENABLED`

- **Config:** El archivo `config/captcha.php` con las keys de hCaptcha será reemplazado por uno de Turnstile.

### 2. IMPLEMENTAR CLOUDFLARE TURNSTILE — FRONTEND

Agregar Turnstile a TODOS los formularios que tenían hCaptcha, y también a cualquier formulario público que no lo tuviera:

**Formularios que deben tener Turnstile:**
- Formulario de reseñas
- Formulario de contacto (/contacto)
- Formulario de ejercicio de derechos ARCO (/derechos)
- Cualquier otro formulario público que acepte input del usuario

**Implementación frontend por formulario:**

```html
<!-- Agregar ANTES del botón submit de cada formulario -->
<div class="cf-turnstile" data-sitekey="<?php echo TURNSTILE_SITE_KEY; ?>"></div>

<!-- Agregar UNA VEZ en el layout principal (antes de </body>) -->
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
```

**Notas:**
- El widget de Turnstile se renderiza automáticamente — no requiere interacción del usuario
- Funciona en modo "managed" por defecto (Cloudflare decide si mostrar challenge o no)
- Es invisible en la mayoría de los casos (mejor UX que hCaptcha)
- El script se carga async/defer para no bloquear la página

### 3. IMPLEMENTAR CLOUDFLARE TURNSTILE — BACKEND

Crear una función helper reutilizable para validar el token de Turnstile en el servidor.

**Crear archivo `app/helpers/turnstile.php`** (o donde corresponda según la estructura):

```php
<?php
/**
 * Valida el token de Cloudflare Turnstile
 * @param string $token - El valor de cf-turnstile-response del POST
 * @return bool - true si la validación pasa, false si falla
 */
function validar_turnstile($token) {
    if (!defined('TURNSTILE_ENABLED') || !TURNSTILE_ENABLED) {
        return true; // Si está deshabilitado, dejar pasar
    }
    
    if (empty($token)) {
        return false;
    }
    
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = [
        'secret' => TURNSTILE_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ];
    
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === false) {
        return false; // Si falla la conexión, rechazar por seguridad
    }
    
    $response = json_decode($result, true);
    return isset($response['success']) && $response['success'] === true;
}
```

**En cada controller/procesador de formulario**, agregar la validación:

```php
// Antes de procesar el formulario
$token_turnstile = $_POST['cf-turnstile-response'] ?? '';
if (!validar_turnstile($token_turnstile)) {
    // Rechazar envío — mostrar error amigable
    $error = 'No pudimos verificar que eres humano. Intenta de nuevo.';
    // No procesar el formulario, mostrar el error
}
```

### 4. ARCHIVO DE CONFIGURACIÓN

Reemplazar el contenido de `config/captcha.php` con la estructura para Turnstile:

```php
<?php
// Cloudflare Turnstile - Reemplaza hCaptcha
// Keys se obtienen en https://dash.cloudflare.com/turnstile
define('TURNSTILE_SITE_KEY', 'PLACEHOLDER_SITE_KEY');
define('TURNSTILE_SECRET_KEY', 'PLACEHOLDER_SECRET_KEY');
define('TURNSTILE_ENABLED', true);
```

**IMPORTANTE:**
- Las keys reales NO van en el repo (es público). Dejar los placeholders.
- Gustavo reemplazará los placeholders directamente en el servidor vía cPanel File Manager.
- El archivo `config/captcha.php` debe estar en `.gitignore`.
- Verificar que `config/captcha.php` ya está en `.gitignore`. Si no, agregarlo.

### 5. ACTUALIZAR CSP EN .htaccess

El Content-Security-Policy en `.htaccess` referencia dominios de hCaptcha. Actualizarlo:

**Reemplazar** las referencias a hCaptcha:
```
https://hcaptcha.com https://*.hcaptcha.com
```

**Por** las de Turnstile:
```
https://challenges.cloudflare.com
```

El CSP actualizado debe incluir:
```apache
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://challenges.cloudflare.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' https://challenges.cloudflare.com; frame-src https://challenges.cloudflare.com;"
```

Ajustar si el sitio usa otros dominios externos (CDNs, Google Fonts, etc.).

### 6. LIMPIEZA

- Eliminar cualquier referencia residual a hCaptcha en comentarios del código
- Verificar que no quede ningún `hcaptcha` en ningún archivo del proyecto:
  ```bash
  grep -ri "hcaptcha" --include="*.php" --include="*.html" --include="*.js" --include="*.css" .
  ```
- Si el resultado está limpio, la migración está completa

---

## Verificación post-migración

Después del deploy, verificar:

- [ ] `grep -ri "hcaptcha"` no devuelve resultados en el proyecto
- [ ] Formulario de reseñas muestra widget Turnstile (puede ser invisible)
- [ ] Formulario de contacto muestra widget Turnstile
- [ ] Formulario de derechos ARCO muestra widget Turnstile
- [ ] Enviar reseña de prueba → funciona correctamente
- [ ] Enviar contacto de prueba → funciona correctamente
- [ ] Enviar solicitud ARCO de prueba → funciona correctamente
- [ ] CSP en headers no referencia hcaptcha.com
- [ ] CSP incluye challenges.cloudflare.com

---

## Restricciones (recordatorio)

- **Repo público:** Keys van en `config/captcha.php` que está en `.gitignore`
- **BD la gestiona Gustavo:** No ejecutar mysql directamente
- **No tocar CLAUDE.md** del proyecto
- **Al terminar:** git add, commit, push y avisarme para deploy en cPanel
- Después del deploy, Gustavo actualizará las keys reales en `config/captcha.php` vía cPanel File Manager
