# AUDIT_REPORT.md — Regalos Purranque v2
**Fecha:** 2026-04-11 | **Auditor:** Claude Opus 4.6 | **Modo:** Solo lectura

---

## RESUMEN EJECUTIVO

Regalos Purranque v2 es una aplicación PHP vanilla MVC madura y bien arquitecturada, desplegada en HostGator compartido sobre PHP 8.3. El sistema tiene **42 tablas**, **~160 rutas**, **6 comercios activos** y **16,329 registros de visitas**. La seguridad es sólida (CSRF, CSP, HSTS, prepared statements), el SEO está muy bien implementado (OG, Twitter Cards, Schema.org, sitemap dinámico), y hay un sistema de backup automatizado diario. Los puntos críticos son: **0 reseñas**, **1 solo producto**, **comercio_fotos vacío**, **comercio_horarios vacío**, y archivos de configuración con permisos 644 (legibles por otros usuarios del servidor). El sitio está funcional y listo para crecer, pero necesita contenido real y algunas optimizaciones.

---

## 1. ARQUITECTURA GENERAL

### Estado: ✅ OK

### 1.1 Estructura de directorios

```
/home/purranque/v2.regalos.purranque.info/
├── index.php                          ← Front controller único
├── router.php                         ← Auto prepend fallback (dev + producción)
├── .htaccess                          ← Rewrite, seguridad, caché, MIME
├── manifest.json                      ← PWA manifest
├── sw.js                              ← Service Worker (Network First)
├── robots.txt                         ← Bien configurado
├── sitemap.xml                        ← Generado dinámicamente (352 líneas)
├── favicon.ico
├── ig.php                             ← Helper Instagram (standalone, no framework)
├── app/
│   ├── Core/                          ← App, Controller, Database, Middleware, Request, Response, Router, View
│   ├── Controllers/
│   │   ├── Admin/    (31 controladores)
│   │   ├── Api/      (5 controladores: Review, Track, Banner, Share, Consentimiento)
│   │   └── Public/   (16 controladores)
│   ├── Models/       (22 modelos)
│   ├── Services/     (17 servicios)
│   ├── Middleware/    (5: Auth, Csrf, Maintenance, Permission, Redirect)
│   └── helpers.php                    ← Funciones globales
├── config/
│   ├── app.php                        ← Config principal + detección entorno automática
│   ├── database.php                   ← Credenciales DB
│   ├── routes.php                     ← ~160 rutas (32KB)
│   ├── middleware.php                 ← Mapa de middlewares
│   ├── permissions.php                ← Permisos por rol
│   ├── mail.php                       ← SMTP config
│   ├── backup.php                     ← Config backups
│   ├── captcha.php                    ← Cloudflare Turnstile
│   ├── google-credentials.json        ← Google Drive API
│   ├── database.php.example           ← Templates de ejemplo
│   ├── mail.example.php
│   ├── captcha.php.example
│   └── captcha.php_00                 ← ⚠️ Archivo residual
├── views/
│   ├── layouts/     (admin.php, public.php, login.php)
│   ├── public/      (20+ vistas)
│   ├── admin/       (20+ carpetas con form+index)
│   ├── comerciante/ (dashboard, editar, perfil, productos, login, etc.)
│   ├── emails/      (25 templates de email)
│   ├── partials/    (17 partials incluyendo seo-head, share-buttons, share-float)
│   ├── errors/      (403, 404, 500)
│   └── desuscripcion/
├── assets/
│   ├── css/         (main.css, rp2.css + minificados, admin.css, derechos.css, mapa.css)
│   ├── js/          (app.js + minificado, admin.js, mapa.js + minificado)
│   ├── img/         (banners, galeria, hero, icons, logo, logos, noticias, og, og-cache, placeholder, portadas, productos)
│   └── vendor/      (leaflet, tinymce)
├── lib/PHPMailer/   (Exception, PHPMailer, SMTP)
├── storage/
│   ├── backups/     (60 archivos: 30 DB .sql.gz + 30 full .zip)
│   ├── cache/
│   ├── comprobantes/
│   ├── logs/
│   └── temp/
├── cron/            (7 scripts: analytics-daily, backup-auto, notificaciones, email-*, expiracion, aviso-vencimiento)
├── database/migrations/
├── deploy/          (check-css.php, verify.php)
├── scripts/         (minify.php, optimizar-imagenes.php)
└── legales/         (documentación legal, prompts, auditorías previas)
```

### 1.2 Framework y patrón

- **PHP 8.3 vanilla MVC** — sin Composer, sin frameworks
- **Namespaces PSR-4** con autoloader custom: `App\Controllers\Public\ComercioController` → `app/Controllers/Public/ComercioController.php`
- **Front Controller**: todo pasa por `index.php` → `App\Core\App::run()` → Router → Middleware → Controller
- **Router**: array de tuples `[method, uri, handler, middlewares]` en `config/routes.php` con soporte `{param}`
- **Database**: singleton PDO con prepared statements (emulate_prepares = false)
- **Views**: sistema propio via `App\Core\View` con layouts y partials
- **Servicios**: capa de servicios (Auth, Seo, Mailer, VisitTracker, Backup, GoogleDrive, etc.)

### 1.3 Entry point y flujo de request

```
Request → .htaccess (HTTPS + www redirect + front controller)
       → index.php (define BASE_PATH, carga configs, autoloader)
       → App\Core\App::run()
            → MaintenanceMiddleware (modo mantenimiento)
            → RedirectMiddleware (redirecciones SEO desde seo_redirects)
            → Router::match() (busca ruta que coincida)
            → CsrfMiddleware (si POST y no /api/)
            → Middlewares de ruta (auth, permission)
            → Cache-Control headers
            → Router::dispatch() → Controller@method
```

### 1.4 Dependencias

- **Sin composer.json** — no hay dependencias externas vía Composer
- **PHPMailer**: incluido manualmente en `lib/PHPMailer/` (3 archivos)
- **Leaflet 1.9.4**: incluido en `assets/vendor/leaflet/`
- **TinyMCE**: incluido en `assets/vendor/tinymce/` (editor WYSIWYG para admin)
- **Cloudflare Turnstile**: captcha externo (JS)
- **Google Drive API**: via `config/google-credentials.json` para backups

---

## 2. BASE DE DATOS

### Estado: ✅ OK (con observaciones)

### 2.1 Tablas y conteos (42 tablas)

| Tabla | Registros | Descripción |
|---|---|---|
| `admin_log` | **1,338** | Log de acciones admin |
| `admin_usuarios` | **13** | 1 admin + 12 comerciantes |
| `analytics_diario` | **1,840** | Analíticas agregadas diarias |
| `banners` | **4** | Banners publicitarios |
| `categorias` | **13** | Categorías de comercios |
| `comercio_cambios_pendientes` | **8** | Cambios esperando aprobación |
| `comercio_categoria` | **14** | Pivot comercio↔categoría |
| `comercio_fecha` | **72** | Pivot comercio↔fecha especial |
| `comercio_fotos` | **0** | ⚠️ VACÍA — galería no usada |
| `comercio_horarios` | **0** | ⚠️ VACÍA — horarios no ingresados |
| `comercio_renovaciones` | **0** | Sin renovaciones aún |
| `comercios` | **6** | 6 comercios activos |
| `configuracion` | **74** | Config clave-valor |
| `configuracion_mantenimiento` | **5** | Config modo mantenimiento |
| `consentimientos` | **116** | Consentimientos GDPR/legal |
| `fechas_especiales` | **27** | Celebraciones (cumpleaños, navidad, etc.) |
| `login_intentos` | **157** | Protección brute-force |
| `mensajes_contacto` | **8** | Mensajes de contacto |
| `mensajes_respuestas` | **24** | Respuestas a mensajes |
| `noticia_categoria` | **1** | Pivot noticia↔categoría |
| `noticia_fecha` | **2** | Pivot noticia↔fecha |
| `noticias` | **4** | Noticias publicadas |
| `notificaciones_log` | **133** | Log de emails enviados |
| `nurturing_config` | **12** | Config de nurturing |
| `nurturing_log` | **16** | Log de nurturing enviados |
| `nurturing_plantillas` | **4** | Templates de nurturing |
| `planes_config` | **5** | Freemium, Básico, Premium, Sponsor, Banner |
| `politicas_aceptacion` | **48** | Aceptaciones de políticas |
| `producto_fotos` | **7** | Fotos de productos |
| `productos` | **1** | ⚠️ Solo 1 producto registrado |
| `redes_sociales_config` | **103** | Config de redes sociales + share |
| `registro_tratamiento` | **10** | Registro tratamiento datos |
| `resenas` | **0** | 🔴 VACÍA — no hay reseñas |
| `resenas_reportes` | **0** | Sin reportes |
| `seguimiento_conversiones` | **35** | Tracking de conversiones |
| `seo_config` | **189** | SEO config extensivo |
| `seo_redirects` | **10** | Redirecciones 301 |
| `sesiones_admin` | **13** | Sesiones admin activas |
| `share_log` | **34** | Log de shares |
| `sitios` | **1** | Multi-sitio (1 activo) |
| `solicitudes_arco` | **0** | Solicitudes ARCO (datos personales) |
| `visitas_log` | **16,329** | Log de visitas completo |

### 2.2 Esquema tabla `comercios` (54 columnas)

Campos destacados:
- **Identidad**: id, site_id, nombre, slug, descripcion
- **Contacto**: telefono, whatsapp, email, sitio_web
- **Redes** (8 columnas individuales): facebook, instagram, tiktok, youtube, x_twitter, linkedin, telegram, pinterest
- **Ubicación**: direccion, lat, lng, delivery_local, envios_chile
- **Media**: logo, portada
- **Plan**: plan (enum: freemium/basico/premium/sponsor/banner), plan_precio, plan_inicio, plan_fin, max_fotos
- **Estado**: activo, calidad_ok, registrado_por, destacado, validado, validado_fecha, validado_notas
- **Métricas**: visitas, whatsapp_clicks
- **SEO**: seo_titulo, seo_descripcion, seo_keywords
- **Tributario**: razon_social, rut_empresa, giro, direccion_tributaria, comuna_tributaria
- **Contacto propietario**: contacto_nombre, contacto_rut, contacto_telefono, contacto_email
- **Contrato**: contrato_inicio, contrato_monto, metodo_pago

### 2.3 Esquema tabla `productos` (42 columnas)

Soporta 3 tipos: `producto`, `servicio`, `inmueble`
- **Inmuebles**: tipo_propiedad (12 opciones), operacion (5 opciones), superficie, dormitorios, baños, estacionamientos, bodegas, amoblado, mascotas, leñera, áreas verdes, calefacción, rural, servicios básicos, gastos comunes
- **Servicios**: modalidad (presencial/domicilio/online/mixto), horario_atencion
- **Común**: nombre, descripcion, descripcion_detallada, precio, stock, condicion, imagen, imagen2, activo, estado, orden, vistas

### 2.4 Comercios actuales

| ID | Nombre | Plan | Visitas | Validado |
|---|---|---|---|---|
| 28 | Regalos Purranque | sponsor | 399 | ✅ |
| 29 | Tejidos Carolina Kauak | freemium | 119 | ✅ |
| 30 | CorteAlto Artesanías SPA | freemium | 353 | ✅ |
| 31 | Solrosa | freemium | 350 | ✅ |
| 32 | Bolichito del Té | freemium | 361 | ✅ |
| 33 | MimoSpapet | freemium | 444 | ✅ |

### 2.5 Planes comerciales

| Plan | Precio intro | Precio regular | Max fotos | Max redes | Max productos | Posición |
|---|---|---|---|---|---|---|
| Freemium | $0 | $0 | 2 | 1 | 5 | Normal |
| Básico | $1,990 | $2,990 | 3 | 99 | 10 | Normal |
| Premium | $4,990 | $7,990 | 5 | 99 | 20 | Prioritaria |
| Sponsor | $9,990 | $15,990 | 10 | 99 | 40 | Siempre primero |
| Banner | $19,990 | $19,990 | 1 | 0 | 0 | Normal |

### 2.6 Tablas vacías o subutilizadas

| Tabla | Registros | Observación |
|---|---|---|
| `comercio_fotos` | 0 | Galería de fotos no usada por ningún comercio |
| `comercio_horarios` | 0 | Ningún comercio ha ingresado horarios |
| `comercio_renovaciones` | 0 | No ha habido renovaciones de plan |
| `resenas` | 0 | Sistema de reseñas sin uso |
| `resenas_reportes` | 0 | Sin reportes de reseñas |
| `solicitudes_arco` | 0 | Sin solicitudes de datos personales |
| `productos` | 1 | Solo 1 producto (Cocina Fensa usada, $80,000) |

---

## 3. SISTEMA DE REGISTRO Y GESTIÓN DE COMERCIOS

### Estado: ✅ Completo

### 3.1 Flujo de registro público

**Archivo**: `app/Controllers/Public/RegistroComercioController.php`
**Rutas**: `/registrar-comercio` → `/registrar-comercio/datos` → `/registrar-comercio/gracias`

**Paso 1 — Crear cuenta** (`/registrar-comercio`):
- Campos: nombre, email, teléfono, contraseña + confirmación
- Validación: Cloudflare Turnstile + CSRF (middleware global)
- Verificación email único vía `AdminUsuario::findByEmail()`
- Aceptación de políticas (GDPR): `PoliticaAceptacion::POLITICAS` con validación obligatoria
- Crea usuario con `rol = 'comerciante'`, `activo = 1`
- Registra decisiones de políticas con IP y user_agent
- Almacena `registro_uid` en sesión para paso 2

**Paso 2 — Datos del comercio** (`/registrar-comercio/datos`):
- Campos: nombre comercio, descripción, WhatsApp, teléfono, dirección, email comercio, sitio web
- Mapa Leaflet para lat/lng
- Selección de categorías (múltiples) + categoría principal
- Selección de fechas especiales (múltiples)
- 1 red social (limitación freemium)
- Upload logo + portada (máx 5MB, webp/jpg/png/gif)
- Validación completa de todos los campos
- Transacción DB: crea comercio con `activo = 0`, `plan = 'freemium'`, `plan_fin = +30 días`
- Sincroniza categorías y fechas, recalcula calidad
- Notifica admin por email

**Paso 3 — Confirmación** (`/registrar-comercio/gracias`):
- Página estática de agradecimiento

### 3.2 Planes y asignación

- Plan se asigna en campo `plan` de tabla `comercios` (enum)
- Configuración de límites en `planes_config`
- Admin puede cambiar plan desde `/admin/planes`
- Renovaciones: tabla `comercio_renovaciones` (vacía — sin uso aún)
- Cron `expiracion-comercios.php` verifica vencimientos diariamente
- Cron `aviso-vencimiento.php` envía avisos

### 3.3 Dashboard del comerciante

**Rutas**: `/mi-comercio/*` (16 rutas)
**Controlador**: `app/Controllers/Public/ComercianteController.php`

Funcionalidades:
- Dashboard principal con stats del comercio
- Editar datos del comercio (con sistema de cambios pendientes para aprobación admin)
- Gestión de productos (CRUD completo)
- Perfil: cambiar contraseña, actualizar datos personales
- Solicitar renovación de plan
- Sistema de login independiente del admin
- Reset de contraseña con token por email

### 3.4 Sistema de verificación

- Campo `validado` (tinyint) en tabla `comercios`
- Campo `validado_fecha` y `validado_notas`
- Admin aprueba/rechaza desde panel
- Badge "Verificado" en ficha pública
- Todos los 6 comercios actuales están validados

---

## 4. FICHA/PÁGINA DE COMERCIO

### Estado: ✅ Completo

### 4.1 Ruta y controlador

**Ruta**: `GET /comercio/{slug}`
**Controlador**: `app/Controllers/Public/ComercioController@show`
**Vista**: `views/public/comercio.php`

### 4.2 Datos que se cargan

```php
$comercio    = Comercio::getBySlug($slug);     // Datos principales + categorías
$fotos       = Comercio::getFotos($id);         // Galería (vacía — 0 fotos subidas)
$horarios    = Comercio::getHorarios($id);      // Horarios (vacíos — 0 registros)
$resenas     = Resena::getByComercio($id, 'aprobada', 10, 0);  // Reseñas (vacío — 0 reseñas)
$distribucion = Resena::getDistribucion($id);   // Distribución estrellas
$relacionados = Comercio::getRelacionados($id, 4);  // Comercios similares
$banners     = Banner::getByTipo('sidebar');    // Banners laterales
$productos   = Producto::findByComercioId($id); // Catálogo de productos
```

### 4.3 Campos mostrados en ficha pública

- Nombre, logo, portada, descripción
- Categorías con badges
- Dirección
- Teléfono, WhatsApp (con botón directo), email, sitio web
- Redes sociales (hasta 8: facebook, instagram, tiktok, youtube, x_twitter, linkedin, telegram, pinterest)
- Mapa Leaflet (si tiene lat/lng)
- Galería de fotos
- Horarios de atención
- Productos/catálogo
- Reseñas con distribución de estrellas
- Comercios relacionados
- Badges: delivery_local, envios_chile, plan badge
- Botones de compartir (share-buttons partial)

### 4.4 Sistema de productos/catálogo

**Modelo**: `app/Models/Producto.php`
**Tipos**: producto, servicio, inmueble
**Estado actual**: 1 producto registrado (Cocina Fensa)

- CRUD desde panel comerciante (`/mi-comercio/productos/*`)
- CRUD desde panel admin (`/admin/comercios/{id}/productos/*`)
- Límites por plan: freemium=5, básico=10, premium=20, sponsor=40
- Fotos: tabla `producto_fotos` (7 registros), múltiples fotos por producto
- Compartir individual: `/producto/{id}` genera página con OG tags
- OG Image dinámico: `/producto/{id}/og-image` genera imagen 1200x630 con GD

### 4.5 Tracking

- `Comercio::incrementVisitas($id)` — contador en tabla comercios
- `VisitTracker::track($id, url, tipo)` — log detallado en visitas_log
- No cuenta visitas de fichas inactivas

---

## 5. SISTEMA DE SHARE / COMPARTIR

### Estado: ✅ Completo y sofisticado

### 5.1 Componentes de share

**Tres sistemas de share** implementados:

1. **Share Float** (`views/partials/share-float.php`):
   - Botón circular flotante inferior derecho
   - Redes: WhatsApp, Facebook, X/Twitter, Copiar enlace
   - Controlado por `RedesSociales::shouldShowShareAt('floating_circle')`
   - Dinámico: usa `location.href` y `document.title`

2. **Share Buttons** (`views/partials/share-buttons.php`):
   - Botones inline "Compartir:" debajo del contenido
   - Redes configurables: Facebook, X, WhatsApp, LinkedIn, Telegram, Pinterest, Email, Copiar, Native Share API
   - Dual check: por tipo de página + por posición
   - Data attributes para tracking: `data-share="facebook" data-share-slug="..." data-share-type="..."`

3. **Share Page** (`/compartir/{tipo}/{slug}`):
   - Página intermedia optimizada para crawlers de redes sociales
   - Genera OG tags correctos para el contenido compartido

### 5.2 Tracking de shares

**API**: `POST /api/share-track`
**Controlador**: `app/Controllers/Api/ShareApiController@track`
**Tabla**: `share_log` (34 registros)

Registra: comercio_id, producto_id, pagina, red_social, ip, user_agent, created_at

**Shares recientes**:
```
facebook  → /noticia/nuevas-funciones-catalogo-productos-badges-confianza (2026-04-09)
pinterest → /comercio/regalos-purranque (2026-04-07)
native    → /comercio/regalos-purranque (2026-04-07)
facebook  → /producto/regalos-purranque (2026-04-06)  ← ⚠️ URL incorrecta: slug en vez de ID
facebook  → /comercio/regalos-purranque (2026-04-05)
```

### 5.3 OG tags para productos

**Ruta**: `/producto/{id}` → `ComercioController@productoShare`
**Vista**: `views/public/producto-share.php`
**OG Image**: `/producto/{id}/og-image` genera imagen JPEG 1200x630 con:
- Foto del producto centrada
- Barra inferior con logo del comercio, nombre y dominio
- Pill de precio en esquina superior derecha
- Caché en `assets/img/og-cache/producto-{id}.jpg`

### 5.4 Bug conocido en share_log

⚠️ **Hallazgo**: En `share_log` id=31, la URL registrada es `/producto/regalos-purranque` (usa slug en vez de ID numérico). Esto sugiere que el JS de tracking envía `slug` del comercio cuando debería enviar `id` del producto. El campo `producto_id` es NULL en ese registro.

**Admin de shares**: `/admin/share` — panel de visualización de shares

---

## 6. SISTEMA DE RESEÑAS

### Estado: ⚠️ Implementado pero sin uso

### 6.1 Tablas

- `resenas` — 0 registros (12 columnas: id, site_id, comercio_id, nombre_autor, email_autor, calificacion 1-5, comentario, estado enum, respuesta_comercio, fecha_respuesta, ip, created_at)
- `resenas_reportes` — 0 registros

### 6.2 Controladores

- **API crear reseña**: `POST /api/reviews/create` → `Api\ReviewApiController@create`
- **API listar**: `GET /api/reviews/list/{id}` → `Api\ReviewApiController@list`
- **API reportar**: `POST /api/reviews/report` → `Api\ReviewApiController@report`
- **Mis reseñas**: `GET /mis-resenas` → `Public\ReviewController@misResenas`
- **Admin moderación**: `/admin/resenas` — index, show, aprobar, rechazar, responder, eliminar, bulk
- **Admin reportes**: `/admin/resenas/reportes`
- **Admin config**: `/admin/resenas/configuracion`

### 6.3 Flujo

1. Visitante envía reseña vía API (calificación 1-5 + comentario)
2. Estado inicial: `pendiente`
3. Admin aprueba/rechaza desde panel
4. Aprobadas se muestran en ficha del comercio
5. Schema.org `AggregateRating` se genera si hay reseñas

### 6.4 Diagnóstico

El sistema está **completamente implementado** pero no tiene uso real. Ningún comercio tiene reseñas. La ficha pública carga reseñas y distribución, pero siempre están vacías.

---

## 7. SEO

### Estado: ✅ Excelente

### 7.1 Meta tags dinámicos

**Archivo principal**: `views/partials/seo-head.php` (~160 líneas)

Genera para TODAS las páginas:
- `<title>` dinámico
- `<meta name="description">`
- `<meta name="keywords">`
- `<meta name="robots">` (index,follow o noindex según contexto)
- `<meta name="author">`
- `<link rel="canonical">`
- Paginación: `<link rel="prev">`, `<link rel="next">`
- Hreflang: `es-CL` + `x-default`

### 7.2 Open Graph — Completo

```html
<meta property="og:title">
<meta property="og:description">
<meta property="og:image">
<meta property="og:image:secure_url">
<meta property="og:image:type">        ← Detecta jpeg/png/webp
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt">
<meta property="og:url">
<meta property="og:type">              ← website / business.business / article
<meta property="og:site_name">
<meta property="og:locale" content="es_CL">
<meta property="fb:app_id" content="1223892215809376">
```

Para artículos: `article:published_time`, `article:modified_time`, `article:author`

### 7.3 Twitter Cards — Completo

```html
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title">
<meta name="twitter:description">
<meta name="twitter:image">
<meta name="twitter:image:alt">
<meta name="twitter:site">             ← Desde config
<meta name="twitter:creator">
```

### 7.4 Schema.org JSON-LD

**Servicio**: `app/Services/Seo.php`

Schemas implementados:
1. **Organization** — en TODAS las páginas (con sameAs de redes sociales)
2. **WebSite** — en home (con SearchAction para buscador)
3. **LocalBusiness** — en ficha de comercio (dirección, teléfono, geo, horarios, AggregateRating)
4. **NewsArticle** — en noticias (headline, dates, author, publisher, image)
5. **BreadcrumbList** — en todas las páginas con breadcrumbs
6. **ItemList** — en listados de comercios
7. **Event** — en fechas especiales (startDate, endDate, location, offers)

### 7.5 Sitemap.xml

- **Dinámico**: generado por `app/Services/SitemapService.php`
- **352 líneas** actualmente
- Incluye: home, categorías, celebraciones, comercios, noticias, mapa, contacto
- Herramienta de regeneración en admin: `/admin/mantenimiento/sitemap/regenerar`
- Referenciado en robots.txt

### 7.6 robots.txt

```
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /api/
Disallow: /storage/
Disallow: /config/
Disallow: /mis-resenas
Disallow: /mi-comercio
Disallow: /mi-comercio/
Disallow: /compartir/
Disallow: /*?q=
Disallow: /*?page=
Sitemap: https://regalospurranque.cl/sitemap.xml
```

### 7.7 SEO Admin

**Ruta**: `/admin/seo`
**Config en DB**: `seo_config` (189 registros) — SEO por página, por categoría, por fecha especial
- Incluye: Google Search Console verification, GTM ID, GA4, default OG image
- Títulos y descripciones personalizados para 13 categorías y 27 fechas especiales
- Redirecciones 301 en `seo_redirects` (10 activas) — gestionadas por `RedirectMiddleware`

### 7.8 URLs canónicas

- Todas las URLs son amigables: `/comercio/{slug}`, `/categoria/{slug}`, `/fecha/{slug}`
- Canonical tag en cada página
- Redirects configurados: `v2.regalos.purranque.info` → `regalospurranque.cl`, `www` → non-www

---

## 8. SEGURIDAD

### Estado: ✅ Sólida (con observaciones menores)

### 8.1 Headers HTTP

Todos presentes y correctos:
```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: camera=(), microphone=(), geolocation=(self)
Strict-Transport-Security: max-age=31536000; includeSubDomains
Content-Security-Policy: [completa con whitelist]
```

Nota: `X-Powered-By` eliminado via `Header unset X-Powered-By`

### 8.2 Content-Security-Policy (detalle)

```
default-src 'self';
script-src 'self' 'unsafe-inline' googletagmanager cdn.jsdelivr.net cdnjs.cloudflare.com challenges.cloudflare.com connect.facebook.net;
style-src 'self' 'unsafe-inline' cdnjs.cloudflare.com;
img-src 'self' data: https: *.tile.openstreetmap.org;
font-src 'self' fonts.gstatic.com;
connect-src 'self' https: *.tile.openstreetmap.org *.google-analytics.com *.googletagmanager.com challenges.cloudflare.com;
frame-src challenges.cloudflare.com googletagmanager.com;
```

⚠️ `'unsafe-inline'` en script-src es necesario para inline scripts pero reduce la protección XSS. Considerar migrar a nonces en el futuro.

### 8.3 Protección CSRF

- `CsrfMiddleware` valida token en TODOS los POST excepto `/api/*`
- Token generado por sesión
- Aplicado globalmente en `App::run()`

### 8.4 SQL Injection

- `App\Core\Database` usa **prepared statements** exclusivamente
- PDO con `ATTR_EMULATE_PREPARES = false`
- Modelo base provee métodos seguros: `fetch()`, `fetchAll()`, `insert()`, `update()`

### 8.5 Protección de archivos

**.htaccess bloquea**:
- Carpetas del framework: `app/`, `config/`, `storage/`, `database/`, `cron/`, `deploy/`, `views/`, `legales/`, `legal/`, `lib/`
- Archivos ocultos: `^\.` (deny all)
- Extensiones sensibles: `.sql`, `.md`, `.log`, `.sh`, `.bak`, `.env`, `.yml`, `.yaml`, `.lock`, `.ini`, `.php~`, `.zip`, `.gz`
- Scripts de debug explícitos por nombre

### 8.6 Sesiones

```php
session_name('regalos_sess');
cookie_lifetime: 7200 (2h)
cookie_httponly: true
cookie_secure: true (en producción)
cookie_samesite: 'Lax'
```

### 8.7 Login brute-force

- Tabla `login_intentos` (157 registros) — tracking de intentos fallidos
- Rate limiting implícito

### 8.8 Permisos de archivos

⚠️ **Observación**: Archivos de configuración tienen permisos `644` (legibles por otros usuarios del servidor compartido):
- `config/database.php` — contiene credenciales DB (`purranque_v2user` / password)
- `config/mail.php` — contiene contraseña SMTP Gmail en texto plano (`uegtwhvoropeabpx` — app password)
- `config/google-credentials.json` — contiene credenciales Google Drive API (client_id, client_secret, refresh_token)
- `config/backup.php` — contiene credenciales OAuth Google Drive en texto plano (GDRIVE_CLIENT_ID, GDRIVE_CLIENT_SECRET, GDRIVE_REFRESH_TOKEN)
- `config/database.php.production` — config DB alternativa, también 644

**Recomendación**: Cambiar a `640` o `600` para archivos con credenciales. En hosting compartido, otros usuarios del mismo servidor podrían leer estos archivos.

### 8.9 Archivos especiales en raíz

- `ig.php` — Helper de Instagram (standalone, no pasa por framework). Sanitiza input con `preg_replace`. **Bajo riesgo** pero es un archivo extra fuera del MVC.
- `router.php` — Fallback para entornos sin mod_rewrite. No es un riesgo.

### 8.10 Carpeta legales

Contiene documentación de auditorías previas, prompts, y archivos SQL. Está **bloqueada por .htaccess** (`legales/` → 403). Sin riesgo de exposición.

### 8.11 Archivo residual

- `config/captcha.php_00` — Archivo residual de configuración anterior. Sin riesgo pero debería eliminarse.

---

## 9. SISTEMA DE BACKUP

### Estado: ✅ Excelente

### 9.1 Backup automatizado

**Cron**: `0 3 * * *` → `cron/backup-auto.php` (diario a las 3 AM)

**Storage**: `/home/purranque/v2.regalos.purranque.info/storage/backups/`
- Protegido por `.htaccess` (deny all)
- Protegido por `.htaccess` del framework (`storage/` → 403)

### 9.2 Backups actuales

**60 archivos** en storage (30 días de retención):
- **DB backups**: `backup_db_YYYY-MM-DD_HHMMSS.sql.gz` (~500KB cada uno, creciendo)
- **Full backups**: `backup_full_YYYY-MM-DD_HHMMSS.zip` (~70MB cada uno)
- **Total en disco**: ~2 GB

### 9.3 Google Drive backup

- Cron: `15 3 * * *` → `backup-gdrive.sh`
- Credenciales: `config/google-credentials.json`
- Admin puede subir/eliminar backups desde `/admin/mantenimiento/backups`
- Operaciones: backup DB, backup archivos, backup completo, subir a Drive, descargar, eliminar

### 9.4 Admin de backups

**Ruta**: `/admin/mantenimiento/backups`
- Lista backups locales y en Google Drive
- Test de conexión a Drive
- Backup manual (DB / archivos / completo)

---

## 10. ESTADÍSTICAS / ANALYTICS

### Estado: ✅ Completo

### 10.1 Tracking de visitas

**Servicio**: `app/Services/VisitTracker.php`
**Tabla**: `visitas_log` — **16,329 registros**
- Registra: comercio_id, pagina, tipo, ip (con X-Forwarded-For), user_agent, referrer
- Se ejecuta en cada page view pública

### 10.2 Analytics agregado

**Cron**: `5 0 * * *` → `cron/analytics-daily.php`
**Tabla**: `analytics_diario` — **1,840 registros**
- Agregación diaria de visitas

### 10.3 Métricas del comerciante

- `visitas` — contador en tabla comercios
- `whatsapp_clicks` — contador de clicks en WhatsApp
- Productos: campo `vistas` en tabla productos
- Share tracking: tabla `share_log`

### 10.4 Reportes admin

**Rutas**: `/admin/reportes/*`
- Visitas generales
- Por comercio
- Por categoría
- Por fecha especial
- Por banner
- Export CSV

### 10.5 Seguimiento de conversiones

**Tabla**: `seguimiento_conversiones` — **35 registros**
- Tracking de acciones valiosas (WhatsApp clicks, llamadas, etc.)

### 10.6 Google Analytics

- `seo_config` tiene campos `google_analytics` y `gtm_id`
- Actualmente vacíos — **no hay GA4 ni GTM configurado**
- CSP permite googletagmanager.com y google-analytics.com (listo para activar)

---

## 11. ASSETS Y RENDIMIENTO

### Estado: ✅ Bueno

### 11.1 CSS

| Archivo | Tamaño | Uso |
|---|---|---|
| `rp2.css` | 114 KB | CSS compilado público |
| `rp2.min.css` | 77 KB | ← Versión minificada (se usa en producción) |
| `main.css` | 113 KB | CSS original (redundante con rp2.css) |
| `styles.css` | 113 KB | ⚠️ Duplicado exacto de main.css |
| `admin.css` | 39 KB | Panel admin |
| `derechos.css` | 7 KB | Página de derechos |
| `mapa.css` | 912 B | Estilos de mapa |
| `mapa.min.css` | 688 B | Mapa minificado |

⚠️ **`styles.css` es un duplicado exacto de `main.css`** — ambos 113,459 bytes. Uno debería eliminarse.
⚠️ **`main.css` parece la versión pre-compilada de `rp2.css`** — si `rp2.min.css` es lo que se carga, los otros son redundantes.

### 11.2 JavaScript

| Archivo | Tamaño | Uso |
|---|---|---|
| `app.js` | 37 KB | JS público principal |
| `app.min.js` | 20 KB | ← Versión minificada |
| `admin.js` | 12 KB | Panel admin |
| `mapa.js` | 3.2 KB | Leaflet mapa |
| `mapa.min.js` | 2.4 KB | Mapa minificado |
| `social-profiles.php` | 6.8 KB | ⚠️ Archivo PHP en carpeta JS |

⚠️ `social-profiles.php` en `assets/js/` es un archivo PHP que no debería estar en la carpeta de assets públicos. Verificar si se accede directamente o se incluye por el framework.

### 11.3 Vendors

- **Leaflet 1.9.4**: incluido localmente (~150KB)
- **TinyMCE**: incluido localmente (muchos plugins — solo para admin)

### 11.4 Caché

**HTTP Cache** (en .htaccess):
```
Imágenes: 1 mes
CSS/JS: 1 semana
Fonts: 1 mes
Favicon: 1 año
```

**Aplicación**: `Cache-Control: public, max-age=300, s-maxage=600` para páginas públicas

### 11.5 Compresión

- **Gzip** habilitado: `AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml`

### 11.6 PWA

- **manifest.json**: Completo (8 tamaños de iconos, shortcuts para Buscar y Mapa)
- **Service Worker** (`sw.js`): Network First strategy, cachea CSS/JS/imágenes, offline fallback
- **theme_color**: `#ea580c` (naranja)

### 11.7 Script de minificación

`scripts/minify.php` — herramienta para generar versiones minificadas de CSS/JS

---

## 12. CÓDIGO MUERTO Y LIMPIEZA

### Estado: ⚠️ Algunos archivos a limpiar

### 12.1 Archivos residuales

| Archivo | Observación |
|---|---|
| `config/captcha.php_00` | Config vieja de captcha — eliminar |
| `config/database.php.production` | Config de DB alternativa — eliminar si ya no se usa |
| `assets/css/styles.css` | Duplicado exacto de `main.css` (113,459 bytes) — eliminar |
| `assets/css/main.css` | Posiblemente redundante con `rp2.css` — verificar |
| `assets/js/social-profiles.php` | Archivo PHP en carpeta de JS — mover o eliminar |
| `deploy/fase8-update.zip` | ZIP de deploy antiguo — eliminar |
| `storage/diagnostico-*.json` (6 archivos) | Diagnósticos de 2026-02-15 a 2026-02-17 — eliminar |
| `legales/` (16 archivos) | Documentación interna — está protegida pero no pertenece al deploy |

### 12.2 Scripts de desarrollo/deploy en producción

| Archivo | Riesgo |
|---|---|
| `deploy/check-css.php` | Bajo — bloqueado por .htaccess |
| `deploy/verify.php` | Bajo — bloqueado por .htaccess |
| `scripts/minify.php` | Bajo — bloqueado por .htaccess |
| `scripts/optimizar-imagenes.php` | Bajo — bloqueado |
| `database/migrations/migrate_v1_to_v2.php` | Bajo — bloqueado |
| `ig.php` | ⚠️ ACCESIBLE públicamente — standalone fuera del framework |

### 12.3 Controladores sin ruta (AdminPlaceholder)

No se detectó `AdminPlaceholderController` en routes.php pero tampoco existe en el listado de controladores de este proyecto (a diferencia de visitapuertoctay). **Sin problemas detectados**.

### 12.4 Tablas con 0 registros relevantes

Las tablas `comercio_fotos`, `comercio_horarios`, `comercio_renovaciones`, `resenas`, `resenas_reportes`, `solicitudes_arco` están vacías. El código para usarlas está implementado — simplemente no se han usado todavía.

---

## LISTA PRIORIZADA DE ACCIONES

### 🔴 CRÍTICO

| # | Área | Acción | Justificación |
|---|---|---|---|
| 1 | Seguridad | Cambiar permisos de `config/database.php`, `config/mail.php`, `config/google-credentials.json` a 640 | Credenciales legibles por otros usuarios del hosting compartido |
| 2 | Contenido | Incentivar carga de productos — solo 1 producto en todo el sitio | El catálogo de productos es una feature clave que no se está aprovechando |
| 3 | Contenido | 0 reseñas — el sistema está implementado pero sin uso | Las reseñas son cruciales para SEO (Schema AggregateRating) y confianza |

### ⚠️ ATENCIÓN

| # | Área | Acción | Justificación |
|---|---|---|---|
| 4 | Share | Investigar bug en share_log: `/producto/regalos-purranque` (slug en vez de ID) | El JS de tracking podría estar enviando slug del comercio cuando comparte producto |
| 5 | Analytics | Configurar Google Analytics 4 o GTM | Campo vacío en seo_config, CSP ya permite los dominios |
| 6 | Contenido | comercio_fotos (0) y comercio_horarios (0) vacíos | Ningún comercio ha subido fotos a galería ni ingresado horarios |
| 7 | Limpieza | Eliminar CSS duplicados: `styles.css` = `main.css` (113KB duplicado) | Ahorra espacio y evita confusión |
| 8 | Limpieza | Mover `assets/js/social-profiles.php` fuera de assets públicos | Un .php en carpeta de JS no corresponde |
| 9 | Limpieza | Eliminar `config/captcha.php_00` y `config/database.php.production` | Archivos residuales |
| 10 | Seguridad | Revisar `ig.php` en raíz — accesible públicamente fuera del MVC | Sanitiza input pero es un punto de entrada independiente |

### 💡 MEJORAS RECOMENDADAS

| # | Área | Acción | Justificación |
|---|---|---|---|
| 11 | SEO | Eliminar `'unsafe-inline'` de CSP script-src cuando sea posible | Migrar inline scripts a archivos externos con nonces |
| 12 | Backups | El storage de backups usa ~2GB y crece — considerar retención más agresiva | 30 días de full backups de 70MB cada uno |
| 13 | Performance | Los CSS `main.css` y `rp2.css` son muy similares — consolidar en uno solo | Reducir carga y mantenimiento |
| 14 | Nurturing | Sistema de nurturing tiene 4 plantillas y 16 logs — verificar que está funcionando correctamente | Feature avanzada que necesita monitoreo |
| 15 | PWA | Verificar que `offline.html` existe (referenciado en sw.js) | El service worker precachea esta página |

---

*Reporte generado el 2026-04-11. Auditoría de solo lectura — no se realizaron modificaciones al código ni a la base de datos.*
