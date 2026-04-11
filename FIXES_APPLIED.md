# FIXES_APPLIED.md — Regalos Purranque v2
**Fecha:** 2026-04-11 14:58 CLT | **Ejecutado por:** Claude Opus 4.6

---

## BLOQUE 1 — SEGURIDAD: Permisos de archivos con credenciales

### Acción: Cambiar permisos de 644 a 640

| Archivo | Antes | Después | Estado |
|---|---|---|---|
| `config/database.php` | 644 | 640 | ✅ Hecho |
| `config/mail.php` | 644 | 640 | ✅ Hecho |
| `config/google-credentials.json` | 644 | 640 | ✅ Hecho |
| `config/backup.php` | 644 | 640 | ✅ Hecho |
| `config/captcha.php` | 644 | 640 | ✅ Hecho |

**Verificación post-cambio:** Sitio responde 200 OK en `/`, `/comercio/regalos-purranque` y `/admin`. PHP puede leer los archivos como owner del proceso (`purranque`).

---

## BLOQUE 2 — BUG DE SHARE TRACKING

### Diagnóstico

**Síntoma:** En `share_log`, los shares de productos registraban URL `/producto/regalos-purranque` (slug del comercio) en vez de `/producto/4` (ID del producto). Campo `producto_id` quedaba NULL.

**Causa raíz (3 puntos de falla):**

1. **JS `shProd()` en `views/public/comercio.php:412`** — Calculaba `slug` como `window.location.pathname.split('/').pop()` que, estando en `/comercio/regalos-purranque`, devolvía `regalos-purranque` (slug del comercio, no del producto).

2. **`app/Controllers/Api/ShareApiController.php`** — Construía URL como `'/' . $tipo . '/' . $slug` → `/producto/regalos-purranque`. No leía el campo `producto_id` del payload.

3. **`app/Models/Share.php`** — El método `registrar()` no aceptaba ni guardaba `producto_id`.

### Correcciones aplicadas

#### Archivo 1: `app/Models/Share.php`
**Línea 14** — Firma del método:
```php
// ANTES:
public static function registrar(?int $comercioId, string $pagina, string $redSocial): void

// DESPUÉS:
public static function registrar(?int $comercioId, string $pagina, string $redSocial, ?int $productoId = null): void
```

**Línea 22** — Agregado campo `producto_id` al insert:
```php
'producto_id' => $productoId,
```

#### Archivo 2: `app/Controllers/Api/ShareApiController.php`
**Línea 25** — Agregada extracción de `producto_id`:
```php
$productoId = !empty($data['producto_id']) ? (int) $data['producto_id'] : null;
```

**Líneas 28-30** — Lógica de URL corregida (producto usa ID, no slug):
```php
// ANTES:
if (empty($url) && !empty($slug) && !empty($tipo)) {
    $url = '/' . $tipo . '/' . $slug;
}

// DESPUÉS:
if (empty($url) && $tipo === 'producto' && $productoId) {
    $url = '/producto/' . $productoId;
} elseif (empty($url) && !empty($slug) && !empty($tipo)) {
    $url = '/' . $tipo . '/' . $slug;
}
```

**Línea 46** — Pasa `$productoId` al modelo:
```php
// ANTES:
Share::registrar($comercioId, $url ?: ('/' . $tipo . '/' . $slug), $red);

// DESPUÉS:
Share::registrar($comercioId, $url ?: ('/' . $tipo . '/' . $slug), $red, $productoId);
```

#### Archivo 3: `views/public/comercio.php`
**Línea 412** — Función JS `shProd()`, payload del fetch:
```js
// ANTES:
body: JSON.stringify({red:r, slug:window.location.pathname.split('/').pop(), tipo:'producto', producto_id:pId})

// DESPUÉS:
body: JSON.stringify({red:r, tipo:'producto', producto_id:pId, comercio_id:cId, url:'/producto/'+pId})
```

**Resultado:** Ahora cuando un usuario comparte un producto:
- `share_log.pagina` = `/producto/4` (ID correcto)
- `share_log.producto_id` = `4` (campo llenado correctamente)
- `share_log.comercio_id` = ID del comercio propietario

**Nota:** `app.min.js` NO necesita regeneración para este fix — la función `shProd()` es JS inline en la vista PHP `comercio.php`, no en el bundle `app.js`.

---

## BLOQUE 3 — LIMPIEZA DE ARCHIVOS

### Archivos eliminados

| Archivo | Tamaño | Motivo | Estado |
|---|---|---|---|
| `config/captcha.php_00` | 181 B | Config vieja de captcha residual | ✅ Eliminado |
| `config/database.php.production` | 325 B | Config DB alternativa obsoleta con credenciales | ✅ Eliminado |
| `assets/css/styles.css` | 113,459 B | Duplicado exacto de `main.css` (md5: `ec6d070f185c51e016ec3153df8c428a`) | ✅ Eliminado |
| `deploy/fase8-update.zip` | 60,526 B | ZIP de deploy antiguo (fase 8) | ✅ Eliminado |
| `storage/diagnostico-2026-02-15-134927.json` | 124,264 B | Diagnóstico de desarrollo | ✅ Eliminado |
| `storage/diagnostico-2026-02-15-135703.json` | 124,215 B | Diagnóstico de desarrollo | ✅ Eliminado |
| `storage/diagnostico-2026-02-15-135809.json` | 124,195 B | Diagnóstico de desarrollo | ✅ Eliminado |
| `storage/diagnostico-2026-02-15-142001.json` | 124,635 B | Diagnóstico de desarrollo | ✅ Eliminado |
| `storage/diagnostico-2026-02-16-140636.json` | 124,660 B | Diagnóstico de desarrollo | ✅ Eliminado |
| `storage/diagnostico-2026-02-17-000624.json` | 130,485 B | Diagnóstico de desarrollo | ✅ Eliminado |
| `assets/js/social-profiles.php` | 6,814 B | Copia abandonada del partial — existe versión actual en `views/partials/social-profiles.php`. Nadie la incluye. | ✅ Eliminado |

**Total liberado:** ~933 KB

### Reportes solicitados

#### `ig.php` (raíz del proyecto)
- **Función:** Helper standalone de Instagram para Android. Recibe `?u=username`, sanitiza con `preg_replace('/[^a-zA-Z0-9._]/', '')`, redirige a `instagram.com/{user}` en iPhone/PC, o muestra tarjeta intermedia con deep link en Android.
- **Referenciado por:** `views/partials/social-profiles.php` y `views/partials/comercio-redes.php` generan links a `ig.php?u=...` para perfiles de Instagram.
- **Veredicto:** NO eliminar. Es funcional y necesario. Está fuera del MVC pero es un archivo minúsculo (standalone por diseño) y sanitiza correctamente el input.

#### `assets/css/main.css` vs `rp2.css`
- **MD5 main.css:** `ec6d070f185c51e016ec3153df8c428a` (113,459 B, 4,685 líneas)
- **MD5 rp2.css:** `3b32d6e7c5b4215c16937eba4194ca5d` (114,002 B, 4,722 líneas)
- **Veredicto:** Son **diferentes** (543 bytes de diferencia, 37 líneas más en rp2.css). `rp2.css` parece ser la versión actualizada que se compila a `rp2.min.css` (el que usa el layout público). `main.css` podría ser la versión previa. **No eliminado** — requiere verificación manual de cuál se carga en producción antes de decidir.

---

## VERIFICACIÓN FINAL

| Check | Resultado |
|---|---|
| `https://regalospurranque.cl/` | ✅ 200 OK |
| `https://regalospurranque.cl/comercio/regalos-purranque` | ✅ 200 OK |
| `https://regalospurranque.cl/admin` | ✅ 200 OK |
| Permisos config files | ✅ Todos 640 |
| Share tracking fix | ✅ 3 archivos modificados |
| Archivos limpiados | ✅ 11 archivos eliminados |

---

*Correcciones ejecutadas desde AUDIT_REPORT.md del 2026-04-11.*
