# Resumen Completo — 8 Auditorias Regalos Purranque v2

Fecha de cierre: 2026-02-20

---

## C.1 Arquitectura (7 tareas)

| # | Tarea | Que se hizo |
|---|-------|-------------|
| 1 | Auditoria interna de codigo | Reporte completo de estructura MVC, rutas, vistas, helpers, autoloading |
| 2 | Centralizar conexion BD | Clase `Database` singleton con PDO, conexion unica reutilizada |
| 3 | Implementar layout base | Layout `views/layouts/public.php` con header/footer, `admin.php` para panel |
| 4 | Crear capa de modelos | Modelos: Comercio, Categoria, FechaEspecial, Resena, Noticia, Banner, AdminUsuario, etc. Queries migradas de controllers a modelos |
| 5 | Manejo de errores centralizado | `ErrorHandler` con paginas 404/500 personalizadas, `set_exception_handler` |
| 6 | Helpers y utilidades | `helpers.php`: `e()`, `asset()`, `url()`, `slugify()`, `old()`, `csrf_field()`, `truncate()`, `formatDate()`, `picture()` |
| 7 | Documentar estructura | README con stack, directorios, convenciones |

---

## C.2 Datos e Imagenes (7 tareas)

| # | Tarea | Que se hizo |
|---|-------|-------------|
| 1 | Auditoria modelo de datos | Reporte SQL completo: tablas, columnas, tipos, indices, FK, registros |
| 2 | Optimizacion WebP | `FileManager::generarWebP()` genera `.webp` automaticamente al subir JPG/PNG (original + thumbnail) |
| 3 | Lazy loading | `loading="lazy"` en todas las imagenes below-the-fold; above-the-fold sin lazy |
| 4 | Helper `picture()` | Funcion que genera `<picture><source type="image/webp">` con fallback a original, soporta `width`/`height` para CLS |
| 5 | Mapa en fichas | Leaflet/OpenStreetMap embebido en ficha comercio (con lat/lng), contacto y pagina mapa completa |
| 6 | Script optimizacion masiva | `scripts/optimizar-imagenes.php` recorre todas las carpetas y genera WebP de imagenes existentes |
| 7 | Indices BD recomendados | Indices en `schema.sql`: slugs UNIQUE, compuestos en comercio_categoria, resenas, sesiones, etc. |

---

## C.3 Ciberseguridad (7 tareas)

| # | Tarea | Que se hizo |
|---|-------|-------------|
| 1 | CAPTCHA en resenas | Cloudflare Turnstile (`Captcha::widget()` + `Captcha::verify()`) en formulario de resenas |
| 2 | Rate limiting resenas | 3/hora por IP, 10/dia por IP, 1/comercio por 24h — respuestas HTTP 429 |
| 3 | Moderacion previa | Columna `estado` (pendiente/aprobada/rechazada), cola de moderacion en admin, solo aprobadas se muestran |
| 4 | Headers de seguridad | `.htaccess`: CSP restrictivo, HSTS 1 ano, X-Frame-Options SAMEORIGIN, X-Content-Type-Options nosniff, Permissions-Policy, eliminado X-Powered-By |
| 5 | Proteccion datos contacto | Email obfuscado con `base64_encode()` en HTML, decodificado con `atob()` en JS al hacer clic |
| 6 | CAPTCHA contacto | Turnstile en formulario de contacto + verificacion server-side |
| 7 | CAPTCHA ARCO/derechos | Turnstile en formulario ARCO + rate limit 3 solicitudes/email/24h |

---

## C.4 Legal (8 items)

| # | Tarea | Que se hizo |
|---|-------|-------------|
| 1 | Clausula indemnizacion | Seccion 6.1 en `/terminos` — limitacion de responsabilidad |
| 2 | Licencia de contenido | Seccion 6.2 en `/terminos` — licencia no exclusiva del contenido publicado |
| 3 | Estado BETA | Seccion 2.1 en `/terminos` — aviso de plataforma en desarrollo |
| 4 | Ley 21.719 | Actualizada en `/privacidad` — referencia a ley chilena de proteccion de datos |
| 5 | Designacion DPD | Delegado de Proteccion de Datos designado en `/privacidad` |
| 6 | Notificacion de brechas | Procedimiento documentado en `/privacidad` |
| 7 | Retencion de backups | Politica de retencion en `/privacidad` |
| 8 | Coherencia entre documentos | Links cruzados entre terminos, privacidad, cookies y derechos ARCO |

---

## C.5 UX/UI (7 tareas)

| # | Tarea | Que se hizo |
|---|-------|-------------|
| 1 | Ocultar categorias vacias | `HomeController` filtra con `array_filter()` — solo muestra categorias con comercios_count > 0 |
| 2 | Ocultar fechas vacias | Mismo filtro para fechas personales, calendario y comerciales |
| 3 | Countdown inteligente | Cuenta regresiva en home hacia la proxima fecha con comercios (dias, horas, min, seg en JS) |
| 4 | Secciones vacias auto-hide | Guards `if (!empty(...))` en 6 secciones del home |
| 5 | Contenido minimo fichas | Validacion en registro de comercio (nombre min 3 chars, WhatsApp obligatorio) |
| 6 | Home mobile optimizado | Secciones reordenadas, botones "Ver todos" |
| 7 | Hero sin countdown | Alternativa cuando no hay fecha proxima con comercios |

---

## C.6 SEO Tecnico (8 tareas)

| # | Tarea | Que se hizo |
|---|-------|-------------|
| 1 | Schema.org LocalBusiness | JSON-LD en fichas de comercio: name, address, geo, phone, email, image, aggregateRating |
| 2 | Schema.org BreadcrumbList | Microdata + JSON-LD en breadcrumbs de todas las paginas |
| 3 | Schema.org Event | JSON-LD en paginas de fechas especiales: startDate, endDate, location |
| 4 | Titulos optimizados | Formato: `{Nombre} · {Categoria} en Purranque — Regalos Purranque` via `Seo::formatTitle()` |
| 5 | Sitemap.xml | Generacion dinamica: comercios, categorias, fechas, noticias, estaticas — con lastmod, priority, changefreq |
| 6 | robots.txt | Permite `/`, bloquea `/admin/`, `/api/`, `/storage/`, `/config/` — incluye referencia a sitemap |
| 7 | Open Graph + Twitter Cards | `seo-head.php`: og:title, og:description, og:image (con dimensiones), og:locale, article:* |
| 8 | Canonical URLs | `<link rel="canonical">` + hreflang (es-CL, x-default) en todas las paginas |

---

## Dashboard Admin — Fase 1 + 2 (11 items + 8 extras)

### Fase 1: Exploracion

Reporte completo del sistema de autenticacion y los 17 modulos del dashboard.

### Fase 2: Implementacion

| # | Tarea | Que se hizo |
|---|-------|-------------|
| 2.1 | Bcrypt | Ya usaba `password_hash(PASSWORD_DEFAULT)` — confirmado |
| 2.2 | Fuerza bruta | Tabla `login_intentos`, bloqueo 15 min tras 5 intentos fallidos |
| 2.3 | session_regenerate_id | Agregado en `Auth::login()` al iniciar sesion |
| 2.4 | Timeout sesion | `SESSION_LIFETIME = 7200` (2h), verificado en middleware |
| 2.5 | Cookie flags | HttpOnly, Secure, SameSite=Lax en `session_set_cookie_params()` |
| 2.6 | CSRF | `CsrfMiddleware` global: valida ALL POST, token single-use con `hash_equals()` |
| 2.7 | Middleware auth | `AuthMiddleware` centralizado + ACL por modulo via Permission |
| 2.8 | Validacion uploads | FileManager: finfo MIME, realpath() anti-traversal, extensiones bloqueadas |
| 2.9 | Prepared statements | PDO con `?` placeholders en todas las queries |
| 2.10 | Logging admin | `admin_log` registra todas las acciones CRUD con usuario, IP, timestamp |
| 2.11 | Turnstile en login | Widget en login admin y login comerciante + verificacion server-side |

### 8 Fixes extras

| # | Fix | Que se hizo |
|---|-----|-------------|
| 40 | autoShades() CSRF | Confirmado que CsrfMiddleware global ya lo cubre — sin cambios |
| 41 | SEO modal JS escaping | `addslashes()` reemplazado por `json_encode(JSON_HEX_APOS\|JSON_HEX_TAG)` en onclick |
| 42 | saveMetaTags() whitelist | Validacion: solo home, mapa, buscar, noticias, cat_N, fecha_N |
| 43 | Email config validacion | `filter_var(FILTER_VALIDATE_EMAIL)` en email_from y email_reply_to |
| 44 | FileExplorer bloquear PHP | Blocklist de 16 extensiones peligrosas (php, phtml, phar, exe, sh, bat...) |
| 45 | CHECKLIST.txt BD | purranque_regalos_v2 corregido a purranque_regalos_purranque |
| 46 | Password min store() | min:6 corregido a min:8 en validacion de crear usuario admin |
| 47 | phpinfo() restriccion | Agregado Auth::role() !== 'admin' ademas de APP_ENV !== 'development' |

---

## Migracion Turnstile (6 tareas)

| # | Tarea | Que se hizo |
|---|-------|-------------|
| 1 | Eliminar hCaptcha | Removidos todos los scripts, divs, constantes y validaciones de hCaptcha |
| 2 | Frontend Turnstile | `Captcha::widget()` genera div cf-turnstile, `Captcha::script()` carga el JS |
| 3 | Backend Turnstile | `Captcha::verify()` valida token con API de Cloudflare |
| 4 | Configuracion | `config/captcha.php` con TURNSTILE_SITE_KEY y TURNSTILE_SECRET_KEY |
| 5 | CSP actualizado | Dominios hCaptcha reemplazados por challenges.cloudflare.com en .htaccess |
| 6 | Limpieza | Grep confirmo 0 referencias residuales a hCaptcha |

---

## Totales

| Metrica | Valor |
|---|---|
| Auditorias completadas | 8 de 8 |
| Tareas implementadas | ~63 |
| Archivos modificados/creados | ~50+ |
| Vulnerabilidades cerradas | ~20 (CSRF, XSS, brute force, uploads, rate limiting, headers) |
| Cobertura CAPTCHA | 5 formularios (resenas, contacto, ARCO, login admin, login comerciante) |
| Schemas implementados | 3 (LocalBusiness, BreadcrumbList, Event) |
