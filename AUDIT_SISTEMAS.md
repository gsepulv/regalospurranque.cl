# AUDIT_SISTEMAS.md — Regalos Purranque v2
**Fecha:** 2026-04-11 | **Auditor:** Claude Opus 4.6 | **Modo:** Solo lectura

---

## Índice

- [Sistema 1: Registro de Comercio](#sistema-1-registro-de-comercio)
  - [1.1 Paso 1 — Crear cuenta](#11-paso-1--crear-cuenta)
  - [1.2 Paso 2 — Datos del comercio](#12-paso-2--datos-del-comercio)
  - [1.3 Paso 3 — Confirmación](#13-paso-3--confirmación)
  - [1.4 Controlador de registro](#14-controlador-de-registro)
  - [1.5 Modelo de comercio](#15-modelo-de-comercio)
  - [1.6 Rutas de registro](#16-rutas-de-registro)
  - [1.7 Flujo de aprobación por admin](#17-flujo-de-aprobación-por-admin)
- [Sistema 2: Ficha Pública del Comercio](#sistema-2-ficha-pública-del-comercio)
  - [2.1 Ruta y controlador](#21-ruta-y-controlador)
  - [2.2 Vista principal](#22-vista-principal)
  - [2.3–2.16 Módulos de la ficha](#23-módulo-header)
- [Sistema 3: Dashboard de Gestión](#sistema-3-dashboard-de-gestión)
  - [3.1 Dashboard del comerciante](#31-dashboard-del-comerciante)
  - [3.2 Panel admin — Comercios](#32-panel-admin--gestión-de-comercios)
  - [3.3 Panel admin — Reseñas](#33-panel-admin--gestión-de-reseñas)
  - [3.4 Panel admin — Reportes](#34-panel-admin--reportes-y-analytics)
  - [3.5 Sistema de permisos](#35-sistema-de-permisos-y-roles)
  - [3.6 Sistema de autenticación](#36-sistema-de-autenticación)
- [Hallazgos Generales](#hallazgos-generales)
- [Recomendaciones](#recomendaciones)

---

## Sistema 1: Registro de Comercio

### 1.1 Paso 1 — Crear cuenta

**Vista:** `views/public/registro-comercio/cuenta.php`
**Ruta:** `GET /registrar-comercio` → `RegistroComercioController@index`
**Ruta POST:** `POST /registrar-comercio/cuenta` → `RegistroComercioController@storeCuenta`

#### Campos del formulario

| Campo | name | Tipo | Atributos |
|---|---|---|---|
| Nombre completo | `nombre` | text | required, minlength=3, maxlength=100 |
| Email | `email` | email | required |
| Teléfono | `telefono` | text | required, minlength=9, maxlength=15 |
| Contraseña | `password` | password | required, minlength=8 |
| Confirmar contraseña | `password_confirm` | password | required, minlength=8 |
| CSRF token | `csrf_token` | hidden | auto-generado |
| Turnstile captcha | `cf-turnstile-response` | widget | Cloudflare Turnstile |
| Política: Términos | `politica_terminos` | radio (acepto/rechazo) | required |
| Política: Privacidad | `politica_privacidad` | radio | required |
| Política: Contenidos | `politica_contenidos` | radio | required |
| Política: Derechos | `politica_derechos` | radio | required |
| Política: Cookies | `politica_cookies` | radio | required |

#### Captcha

- **Sistema:** Cloudflare Turnstile
- **Config:** `config/captcha.php` → `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY`, `TURNSTILE_ENABLED`
- **Servicio:** `app/Services/Captcha.php` → `Captcha::verify($token)` valida server-side via `challenges.cloudflare.com/turnstile/v0/siteverify`
- **Frontend:** Widget renderizado por `Captcha::widget()` con `data-sitekey`

#### CSRF

- Helper `csrf_field()` en `app/helpers.php` genera `<input type="hidden" name="csrf_token">`
- Validado por `CsrfMiddleware` en `App::run()` para todos los POST excepto `/api/*`
- Token regenerado después de cada validación exitosa (single-use)

#### Aceptación de políticas

- **5 políticas obligatorias** definidas en `PoliticaAceptacion::POLITICAS` (constante array)
- Cada una tiene radio buttons: acepto/rechazo
- **Todas deben ser "acepto"** para continuar
- Se validan en controlador con `PoliticaAceptacion::validarAceptaciones($decisiones)`
- Se registran en tabla `politicas_aceptacion` con: usuario_id, email, politica, decision, ip_address, user_agent, fecha_decision
- **48 registros** actuales en la tabla

#### Validaciones servidor (`storeCuenta`)

```
nombre: mb_strlen < 3 || > 100 → "El nombre debe tener entre 3 y 100 caracteres."
email: !filter_var(FILTER_VALIDATE_EMAIL) → "Ingresa un email válido."
email: mb_strlen > 100 → "El email no puede superar los 100 caracteres."
telefono: mb_strlen < 9 || > 15 → "El teléfono debe tener entre 9 y 15 caracteres."
password: strlen < 8 → "La contraseña debe tener al menos 8 caracteres."
password !== password_confirm → "Las contraseñas no coinciden."
Turnstile: !Captcha::verify() → "Verificación anti-bot fallida. Intenta nuevamente."
Políticas: PoliticaAceptacion::validarAceptaciones() → errores por política rechazada
Email existente: AdminUsuario::findByEmail() → "Ya existe una cuenta con este email."
```

#### Post-submit exitoso

1. Crea usuario en `admin_usuarios` con: nombre, email, telefono, password_hash (PASSWORD_DEFAULT), rol='comerciante', activo=1, site_id=1
2. Registra decisiones de políticas en `politicas_aceptacion` (5 registros)
3. Guarda en sesión: `registro_uid`, `registro_nombre`, `registro_email`
4. Redirige a `/registrar-comercio/datos`

#### Post-submit con error

- Errores en `$_SESSION['flash_errors']` (array)
- Datos previos en `$_SESSION['flash_old']` (array con nombre, email, telefono + políticas)
- Redirige a `/registrar-comercio` donde se muestran errores y se repoblan campos

---

### 1.2 Paso 2 — Datos del comercio

**Vista:** `views/public/registro-comercio/datos.php`
**Ruta:** `GET /registrar-comercio/datos` → `RegistroComercioController@datos`
**Ruta POST:** `POST /registrar-comercio/store` → `RegistroComercioController@storeDatos`

#### Pre-requisito

- Requiere `registro_uid` en sesión (del paso 1)
- Si ya tiene comercio registrado: muestra `ya-registrado.php`
- Si no tiene sesión de registro: redirige a paso 1

#### Campos del formulario

| Campo | name | Tipo | Atributos |
|---|---|---|---|
| Nombre comercio | `nombre` | text | required, minlength=3, maxlength=100 |
| Descripción | `descripcion` | textarea | required, minlength=20, maxlength=5000 |
| WhatsApp | `whatsapp` | text | required, minlength=9, maxlength=15 |
| Teléfono | `telefono` | text | optional, minlength=9, maxlength=15 |
| Dirección | `direccion` | text | required, minlength=5, maxlength=255 |
| Email comercio | `email_comercio` | email | optional |
| Sitio web | `sitio_web` | url | optional |
| Latitud | `lat` | hidden | desde mapa Leaflet |
| Longitud | `lng` | hidden | desde mapa Leaflet |
| Categorías | `categorias[]` | checkboxes | múltiples, dinámicas desde BD |
| Categoría principal | `categoria_principal` | radio | 1 selección |
| Fechas especiales | `fechas[]` | checkboxes | múltiples, dinámicas desde BD |
| Red social tipo | `red_social_tipo` | select | facebook/instagram/tiktok/youtube/x_twitter/linkedin/telegram/pinterest |
| Red social URL | `red_social_url` | url | optional |
| Logo | `logo` | file | image/*, max 5MB |
| Portada | `portada` | file | image/*, max 5MB |
| CSRF | `csrf_token` | hidden | |
| Turnstile | `cf-turnstile-response` | widget | |

#### Categorías

- **Fuente:** `Categoria::getActiveForSelect()` — categorías activas, ordenadas
- **Presentación:** Checkboxes con emoji + nombre
- **Límite:** Sin límite explícito en frontend (freemium permite todas)
- **Principal:** Radio button para marcar 1 como principal
- **BD:** Tabla `comercio_categoria` (pivot) con campo `es_principal`
- **Actual:** 13 categorías, 14 registros en pivot

#### Fechas especiales

- **Fuente:** `FechaEspecial::getActiveForSelect()` — 27 fechas activas
- **Presentación:** Checkboxes con emoji + nombre
- **BD:** Tabla `comercio_fecha` (pivot) — 72 registros actuales
- **Sin límite** de selección

#### Redes sociales (plan freemium)

- **Límite freemium:** 1 red social (tipo + URL)
- **Opciones:** facebook, instagram, tiktok, youtube, x_twitter, linkedin, telegram, pinterest
- **Validación:** URL válida con `FILTER_VALIDATE_URL`
- **BD:** Se guarda en la columna correspondiente de `comercios` (ej: `facebook`, `instagram`)

#### Upload de imágenes

- **Servicio:** `app/Services/FileManager.php` → `FileManager::subirImagen($file, $carpeta, $maxWidth)`
- **Límite tamaño:** `UPLOAD_MAX_SIZE` = 5MB (definido en `config/app.php`)
- **Formatos:** image/jpeg, image/png, image/webp, image/gif
- **Proceso:**
  1. Valida UPLOAD_ERR_OK, tamaño, MIME type (vía finfo)
  2. Genera nombre: `slug-timestamp-randomhex.ext`
  3. Previene path traversal con `realpath()`
  4. `move_uploaded_file()` al directorio
  5. Redimensiona si excede maxWidth (logo=800px, portada=1200px)
  6. Genera thumbnail en subcarpeta `thumbs/`
  7. Genera versión WebP si no es WebP
- **Destino:** `assets/img/logos/` y `assets/img/portadas/`
- **Si no sube:** El comercio se crea sin logo/portada (campos NULL)

#### Mapa Leaflet

- Se carga condicionalmente en la vista con `$usarLeaflet = true` pasado al layout
- JS inline en la vista: inicializa mapa centrado en Purranque (-40.913, -73.159), zoom 15
- Click en mapa actualiza campos hidden `lat` y `lng`
- Marcador draggable para ajustar posición

#### Transacción de BD

El método `storeDatos()` usa `$this->db->transaction()`:

1. **Dentro de transacción:**
   - Genera slug único vía `$this->generarSlug($nombre)` (consulta DB dentro de transacción)
   - `Comercio::create()` → INSERT en `comercios` con:
     - nombre, slug, descripcion, telefono, whatsapp, email, sitio_web, direccion
     - lat, lng, logo=null, portada=null
     - plan='freemium', plan_inicio=date('Y-m-d'), plan_fin=date('+30 days')
     - activo=0, destacado=0, registrado_por=$uid
     - Redes sociales según selección
   - `Comercio::syncCategorias($id, $catIds, $principal)` → DELETE + INSERT en `comercio_categoria`
   - `Comercio::syncFechas($id, $fechaIds)` → DELETE + INSERT en `comercio_fecha`
   - `Comercio::recalcularCalidad($id)` → UPDATE `calidad_ok` en comercios

2. **Después de transacción:**
   - Upload logo → UPDATE comercios SET logo
   - Upload portada → UPDATE comercios SET portada
   - `Notification::registroComercianteAdmin($comercioId, $nombre)` → email a admins

#### Calidad del comercio

**Método:** `Comercio::checkCompletitud($comercio)` evalúa 4 campos:
1. **Descripción** ≥ 100 caracteres → `$items['descripcion']`
2. **Portada** no vacía → `$items['imagen']`
3. **Contacto** (telefono OR whatsapp OR email) → `$items['contacto']`
4. **Categoría** (al menos 1 en `comercio_categoria`) → `$items['categoria']`

Retorna: `porcentaje` (0-100), `completa` (bool), `items` (array), `faltantes` (array)
`recalcularCalidad()` → si 4/4 completos: `calidad_ok = 1`, sino `calidad_ok = 0`

#### Notificación al admin

- **Servicio:** `Notification::registroComercianteAdmin($id, $nombre)`
- **Template email:** `views/emails/registro-comerciante-admin.php`
- **Destinatarios:** Todos los admin_usuarios con rol admin/superadmin
- **Contenido:** comercioId, nombreComercio
- **Condición:** Solo si `notif_nuevo_comercio` está habilitado en configuración

---

### 1.3 Paso 3 — Confirmación

**Vista:** `views/public/registro-comercio/gracias.php`
**Ruta:** `GET /registrar-comercio/gracias` → `RegistroComercioController@gracias`

**Contenido:**
- Ícono check verde
- Título: "¡Registro exitoso!"
- Mensaje: revisión en máximo 48 horas
- Info: datos registrados
- Contacto: email + WhatsApp
- Botón: Volver al inicio

**Email al comerciante:** Se envía un email de bienvenida desde `storeDatos()` justo después de crear el comercio. No desde `gracias()`.
- **Template:** `views/emails/comercio-bienvenida.php`
- **Asunto:** incluye nombre del comercio

---

### 1.4 Controlador de registro

**Archivo:** `app/Controllers/Public/RegistroComercioController.php`

**Métodos públicos:**
| Método | Ruta | Función |
|---|---|---|
| `index()` | GET /registrar-comercio | Muestra paso 1 o redirige si ya logueado |
| `storeCuenta()` | POST /registrar-comercio/cuenta | Valida + crea usuario + políticas |
| `datos()` | GET /registrar-comercio/datos | Muestra paso 2 con categorías y fechas |
| `storeDatos()` | POST /registrar-comercio/store | Valida + transacción BD + uploads + notificación |
| `gracias()` | GET /registrar-comercio/gracias | Página de confirmación |

**Métodos privados:**
- `tieneSessionRegistro()` — verifica sesión de registro o comerciante logueado sin comercio
- `generarSlug($nombre)` — genera slug único consultando BD
- `notificarAdmin($id, $nombre)` — wrapper try/catch para Notification service

**Modelos invocados:** AdminUsuario, Comercio, Categoria, FechaEspecial, PoliticaAceptacion
**Servicios:** Captcha, FileManager, Notification

---

### 1.5 Modelo de comercio (para registro)

**Archivo:** `app/Models/Comercio.php`

**Métodos usados en registro:**

```php
// CREATE — inserta en tabla comercios
Comercio::create($data) // usa Database::getInstance()->insert()

// SYNC CATEGORÍAS — DELETE + INSERT en comercio_categoria
Comercio::syncCategorias($comercioId, $categorias, $principal)
// DELETE FROM comercio_categoria WHERE comercio_id = ?
// INSERT INTO comercio_categoria (comercio_id, categoria_id, es_principal) VALUES (...)

// SYNC FECHAS — DELETE + INSERT en comercio_fecha
Comercio::syncFechas($comercioId, $fechas, $ofertas)
// DELETE FROM comercio_fecha WHERE comercio_id = ?
// INSERT INTO comercio_fecha (comercio_id, fecha_id, activo, oferta_especial, precio_desde, precio_hasta)

// CALIDAD — evalúa completitud y actualiza calidad_ok
Comercio::recalcularCalidad($comercioId)
// SELECT * FROM comercios WHERE id = ?
// → checkCompletitud() evalúa 4 criterios
// UPDATE comercios SET calidad_ok = ? WHERE id = ?

// BUSCAR POR REGISTRADOR
Comercio::findByRegistradoPor($userId)
// SELECT * FROM comercios WHERE registrado_por = ? LIMIT 1
```

---

### 1.6 Rutas de registro

```php
['GET',  '/registrar-comercio',         'Public\\RegistroComercioController@index',       []],
['POST', '/registrar-comercio/cuenta',  'Public\\RegistroComercioController@storeCuenta', []],
['GET',  '/registrar-comercio/datos',   'Public\\RegistroComercioController@datos',       []],
['POST', '/registrar-comercio/store',   'Public\\RegistroComercioController@storeDatos',  []],
['GET',  '/registrar-comercio/gracias', 'Public\\RegistroComercioController@gracias',     []],
```

**Middlewares:** Ninguno (`[]`). CSRF se aplica globalmente por `App::run()` en POST.

---

### 1.7 Flujo de aprobación por admin

1. **Comercio creado con:** `activo=0`, `plan='freemium'`, `plan_fin=+30 días`
2. **Admin ve en:** `/admin/comercios` con filtro `estado=pendiente` (campo `activo`)
3. **Aprobación:** POST `/admin/comercios/toggle/{id}` → `ComercioAdminController@toggleActive`
   - Cambia `activo` de 0 a 1
   - Si tiene email de template aprobado: envía notificación via `Notification::comercioAprobado()`
   - Template: `views/emails/comercio-aprobado.php`
4. **Rechazo:** No hay ruta explícita de rechazo. Se usa toggle para desactivar.
5. **Notificación comerciante:** Email de aprobación con template, incluye link al sitio

---

## Sistema 2: Ficha Pública del Comercio

### 2.1 Ruta y controlador

**Ruta:** `['GET', '/comercio/{slug}', 'Public\\ComercioController@show']`
**Archivo:** `app/Controllers/Public/ComercioController.php`

**Datos que carga el método `show()`:**

```php
$comercio     = Comercio::getBySlug($slug);        // Datos + categorías + promedio reseñas
$fotos        = Comercio::getFotos($id);            // SELECT * FROM comercio_fotos
$horarios     = Comercio::getHorarios($id);         // SELECT FROM comercio_horarios
$resenas      = Resena::getByComercio($id);         // SELECT WHERE estado='aprobada' LIMIT 10
$distribucion = Resena::getDistribucion($id);       // COUNT GROUP BY calificacion
$relacionados = Comercio::getRelacionados($id, 4);  // JOIN categorías compartidas, RAND()
$banners      = Banner::getByTipo('sidebar');       // Banners activos tipo sidebar
$productos    = Producto::findByComercioId($id);    // SELECT WHERE activo=1
```

**Variables pasadas a la vista:**
title, description, keywords, og_image, og_type='business.business', noindex (si inactivo), comercio, inactivo, fotos, horarios, resenas, distribucion, relacionados, banners, productos, breadcrumbs, schemas (LocalBusiness + Breadcrumbs)

---

### 2.2 Vista principal

**Archivo:** `views/public/comercio.php` (1169 líneas)

**Secciones en orden de aparición:**
1. Meta Pixel Facebook (tracking ViewContent)
2. Header: portada, logo, nombre, badges (plan, verificado, delivery, envíos)
3. Share buttons (position: above_content)
4. Descripción: "Sobre este negocio"
5. Trust badges (delivery_local, envios_chile)
6. Catálogo de productos (accordion con galería, zoom, share, WhatsApp)
7. Share buttons (position: below_content)
8. Galería de fotos (carousel + lightbox)
9. Horarios de atención (tabla lun-dom)
10. Mapa Leaflet
11. Contacto: teléfono, WhatsApp, email, web, dirección
12. Reseñas: distribución + listado + formulario
13. Comercios relacionados
14. Sidebar: card de contacto rápido + banners + share (position: sidebar)
15. Schema.org JSON-LD
16. Scripts: mapa, galería lightbox, carousel, share tracking, WhatsApp tracking

---

### 2.3 Módulo: Header

**Estructura:** Portada (imagen fullwidth 400px o gradient si no hay), logo circular 72px superpuesto, nombre H1, badges.

**Badges mostrados:**
- **Plan:** Sponsor → "🏆 Sponsor", Premium → "⭐ Premium", Básico → "✅ Básico", Banner → "📢 Banner"
- **Verificado:** si `validado=1` → badge verde "✓ Verificado"
- **Delivery:** si `delivery_local=1` → "🛵 Delivery local"
- **Envíos:** si `envios_chile=1` → "📦 Envíos a todo Chile"
- **Categorías:** badges con emoji + nombre de cada categoría asociada

**Lógica condicional:** Solo muestra portada si `$comercio['portada']` no está vacío. Solo muestra logo si existe. Badges solo si el campo tiene valor.

---

### 2.4 Módulo: Información de contacto

**Campos mostrados (si no vacíos):**
- Teléfono → `<a href="tel:...">`
- WhatsApp → botón verde `<a href="https://wa.me/{numero}?text=...">`
- Email → `<a href="mailto:...">`
- Sitio web → `<a href="..." target="_blank">`
- Dirección → texto plano
- Mapa Google → link "Cómo llegar" si tiene coordenadas

**Tracking WhatsApp:** Sí. Función JS `trackWhatsApp(comercioId)` envía POST a `/api/track` con `{ comercio_id, tipo: 'whatsapp' }`. Incrementa `whatsapp_clicks` en tabla comercios.

---

### 2.5 Módulo: Descripción

**Campo:** `$comercio['descripcion']`
**Renderizado:** `sanitize_html($comercio['descripcion'])` — función helper que limpia HTML peligroso pero permite tags básicos (p, br, strong, em, ul, li, a). Usa strip_tags con allowlist.
**Sección:** "Sobre este negocio" con padding y borde

---

### 2.6 Módulo: Redes sociales

**Redes soportadas:** facebook, instagram, tiktok, youtube, x_twitter, linkedin, telegram, pinterest
**Iconos:** SVG inline con colores específicos por plataforma
**Lógica:** Se itera sobre las 8 columnas. Solo muestra las que tienen valor no vacío.
**Si no tiene ninguna:** La sección se oculta completamente.

---

### 2.7 Módulo: Mapa

**Leaflet:** Se carga condicionalmente. El controlador pasa `$usarLeaflet = true` si `lat` y `lng` no son vacíos.
**Inicialización:** JS inline al final de la vista. `L.map('mapa').setView([lat, lng], 15)`.
**Tiles:** OpenStreetMap
**Marcador:** Pin con popup mostrando nombre del comercio
**Sin coordenadas:** No se renderiza la sección del mapa ni se carga Leaflet

---

### 2.8 Módulo: Galería de fotos

**Tabla:** `comercio_fotos` — campos: id, comercio_id, ruta, ruta_thumb, titulo, orden
**Presentación:** Carousel horizontal con flechas + dots de navegación. Click abre lightbox fullscreen.
**Lightbox:** Overlay oscuro, imagen centrada, nav con flechas + teclado (Escape, ←, →)
**Estado actual:** **0 fotos** en toda la tabla. Si no hay fotos, la sección no se renderiza.

---

### 2.9 Módulo: Horarios

**Tabla:** `comercio_horarios` — campos: id, comercio_id, dia (0-6), hora_apertura, hora_cierre, cerrado
**Presentación:** Tabla con 7 filas (Lunes a Domingo). Día actual resaltado con clase `horario-hoy`.
**Formato:** "09:00 — 18:00" o "Cerrado"
**Estado actual:** **0 registros**. Si no hay horarios, la sección no se renderiza.

---

### 2.10 Módulo: Catálogo de productos

**Fuente:** `Producto::findByComercioId($id)` → SELECT WHERE activo=1 ORDER BY estado, orden, created_at
**Presentación:** Accordion (acordeón) expandible por producto.
**Cada producto muestra:**
- Imagen principal + galería de fotos con thumbnails (lightbox incluido)
- Nombre, precio formateado, condición (nuevo/usado/reacondicionado)
- Descripción + descripción detallada
- **Tipo servicio:** modalidad (presencial/domicilio/online/mixto), horario atención
- **Tipo inmueble:** tipo propiedad, operación, superficie, dormitorios, baños, estacionamientos, bodegas, amoblado, mascotas, leñera, áreas verdes, calefacción, rural, servicios básicos, gastos comunes
- Botón WhatsApp con mensaje personalizado según tipo
- Botones compartir: Facebook, X, WhatsApp, Copiar enlace (con tracking vía `shProd()`)
- Filtros por tipo si hay múltiples tipos
- Badges: "Vendido", "Reservado", "Agotado" según estado

**Límites por plan:** freemium=5, básico=10, premium=20, sponsor=40 productos
**Link individual:** `/producto/{id}` → página con OG tags para compartir en redes
**OG Image dinámico:** `/producto/{id}/og-image` genera imagen 1200x630 con GD
**Estado actual:** **1 solo producto** (Cocina Fensa, $80.000, comercio_id=28)

---

### 2.11 Módulo: Reseñas

**Fuente:** `Resena::getByComercio($id, 'aprobada', 10, 0)`
**Distribución:** `Resena::getDistribucion($id)` — cuenta por calificación 1-5
**Presentación:**
- Promedio general con número grande + estrellas + total reseñas
- Barras de distribución (5★ a 1★) con porcentaje visual
- Listado de reseñas: nombre_autor, calificacion en estrellas, fecha, comentario
- Formulario de envío (para usuarios logueados y visitantes anónimos)

**Formulario campos:** puntuacion (estrellas 1-5), visitante_nombre, visitante_email, visitante_origen, comentario, acepta_publicacion
**Estado actual:** **0 reseñas**. Muestra "Sé el primero en opinar" y el formulario.

---

### 2.12 Módulo: Compartir

**3 posiciones de share en la ficha:**
1. `above_content` — `views/partials/share-buttons.php` arriba de la descripción
2. `below_content` — mismos share-buttons debajo del contenido
3. `sidebar` — share-buttons en el sidebar
4. `floating_circle` — `views/partials/share-float.php` botón flotante inferior

**Redes:** Facebook, X, WhatsApp, LinkedIn, Telegram, Pinterest, Email, Copiar, Native Share API
**Data attributes:** `data-share="facebook"`, `data-share-slug="..."`, `data-share-type="comercio"`
**Tracking:** JS en `app.js` captura click en `[data-share]` → POST `/api/share-track`
**Controlado por:** `RedesSociales::shouldShowShare($pageType)` y `RedesSociales::shouldShowShareAt($position)` — configurables desde admin

---

### 2.13 Módulo: Comercios relacionados

**Query:** JOIN entre `comercio_categoria` para encontrar comercios con categorías compartidas
**Selección:** Excluye el comercio actual, prioriza destacados, luego RAND()
**Límite:** 4 comercios
**Presentación:** Grid de cards con portada, nombre, categorías

---

### 2.14 Módulo: Banners

**Fuente:** `Banner::getByTipo('sidebar')`
**Query:** WHERE activo=1 AND tipo='sidebar' AND fecha_inicio <= NOW AND fecha_fin >= NOW
**Presentación:** Imágenes con link, en el sidebar derecho
**Tracking:** Impresiones contadas al renderizar, clicks via `/api/banner-track`
**Estado actual:** 4 banners activos

---

### 2.15 SEO de la ficha

**Meta tags:** Generados por `views/partials/seo-head.php`
- **Title:** `seo_titulo` del comercio, o auto-generado: "Nombre · Categoría en Purranque · SITE_NAME"
- **Description:** `seo_descripcion` o auto: "Nombre: descripción. Categoría en Purranque."
- **Keywords:** `seo_keywords` del comercio
- **OG Image:** portada del comercio o default
- **OG Type:** `business.business`
- **Canonical:** `SITE_URL/comercio/{slug}`
- **Twitter Cards:** summary_large_image
- **Noindex:** Si `$inactivo` (comercio no activo)

**Schema.org:**
- `LocalBusiness` via `Seo::schemaLocalBusiness($comercio, $horarios)` — incluye name, description, url, address, telephone, geo, image, openingHoursSpecification, aggregateRating
- `BreadcrumbList` via `Seo::schemaBreadcrumbs($breadcrumbs)`
- `Organization` auto-inyectado en todas las páginas desde seo-head.php

---

### 2.16 Tracking de visitas

- **`Comercio::incrementVisitas($id)`** — UPDATE comercios SET visitas = visitas + 1
- **`VisitTracker::track($id, "/comercio/{slug}", 'comercio')`** — INSERT en `visitas_log` con: comercio_id, pagina, tipo, ip, user_agent, referrer
- **`Producto::incrementVistas($id)`** — si hay productos, incrementa vistas
- **Exclusión:** NO se trackean fichas inactivas (`if (!$inactivo)`)
- **NO se excluyen:** bots, admins ni el propio comerciante (potencial mejora)

---

## Sistema 3: Dashboard de Gestión

### 3.1 Dashboard del comerciante

**Rutas (16):**
```
GET  /mi-comercio                     → dashboard
GET  /mi-comercio/login               → loginForm
POST /mi-comercio/login               → login
GET  /mi-comercio/logout              → logout
GET  /mi-comercio/editar              → editar
POST /mi-comercio/guardar             → guardar (cambios pendientes)
POST /mi-comercio/solicitar-renovacion → solicitarRenovacion
GET  /mi-comercio/perfil              → perfil
POST /mi-comercio/perfil/password     → updatePassword
POST /mi-comercio/perfil/datos        → updateDatos
GET  /mi-comercio/olvide-contrasena   → forgotPasswordForm
POST /mi-comercio/olvide-contrasena   → sendResetLink
GET  /mi-comercio/reset/{token}       → resetPasswordForm
POST /mi-comercio/reset/{token}       → resetPassword
GET  /mi-comercio/productos           → productos
GET  /mi-comercio/productos/crear     → productoCrear
POST /mi-comercio/productos/despacho  → productoDespacho
POST /mi-comercio/productos/guardar   → productoGuardar
GET  /mi-comercio/productos/editar/{id} → productoEditar
POST /mi-comercio/productos/actualizar/{id} → productoActualizar
POST /mi-comercio/productos/eliminar/{id}   → productoEliminar
POST /mi-comercio/productos/{id}/foto-eliminar/{fid}  → productoFotoEliminar
POST /mi-comercio/productos/{id}/foto-principal/{fid} → productoFotoPrincipal
```

**Controlador:** `app/Controllers/Public/ComercianteController.php`

**Dashboard muestra:**
- Indicador de completitud (barra de progreso)
- Stats: visitas, WhatsApp clicks
- Estado del plan (nombre, fecha vencimiento)
- Acciones rápidas: editar, productos, perfil
- Renovación de plan (si aplica)
- Info de planes disponibles + métodos de pago

**Edición del comercio:**
- El comerciante edita sus datos
- Los cambios NO se aplican directamente → se guardan en `comercio_cambios_pendientes` como JSON
- Admin recibe notificación y aprueba/rechaza desde `/admin/cambios-pendientes`
- **8 cambios pendientes** actuales en la tabla

**Gestión de productos:**
- CRUD completo: crear, editar, eliminar, toggle activo
- Upload de fotos con galería
- Límites por plan verificados al crear

**Login comerciante:** Sistema separado del admin. Sesión `$_SESSION['comerciante']` distinta de `$_SESSION['admin']`.
**Reset de contraseña:** Token aleatorio, email con link, expiración.

**Lo que NO puede hacer el comerciante:**
- Aprobar su propio comercio
- Cambiar su plan directamente (debe solicitar renovación)
- Aplicar cambios inmediatos a su ficha (pasan por aprobación)
- Gestionar otros comercios
- Acceder al panel admin
- Ver reportes globales
- Gestionar reseñas, banners, categorías, fechas, noticias, etc.

---

### 3.2 Panel admin — Gestión de comercios

**Rutas (21):** Todas con middleware `['auth']`
```
GET  /admin/comercios                → index (listado con filtros)
GET  /admin/comercios/crear          → create
POST /admin/comercios/store          → store
GET  /admin/comercios/editar/{id}    → edit
POST /admin/comercios/update/{id}    → update
POST /admin/comercios/toggle/{id}    → toggleActive
POST /admin/comercios/eliminar/{id}  → delete
GET  /admin/comercios/{id}/galeria   → gallery
POST /admin/comercios/{id}/foto      → storePhoto
POST /admin/comercios/{id}/foto/eliminar → deletePhoto
GET  /admin/comercios/{id}/horarios  → horarios
POST /admin/comercios/{id}/horarios  → updateHorarios
+ 9 rutas de productos (crear, guardar, editar, actualizar, eliminar, toggle, foto-eliminar, foto-principal)
```

**Controlador:** `app/Controllers/Admin/ComercioAdminController.php`

**Listado:** Tabla con columnas: nombre, categoría, plan (badge color), estado (activo/inactivo), validado, visitas, acciones. Filtros: búsqueda texto, categoría, plan, estado, validación. Paginación.

**Admin tiene acceso adicional vs comerciante:**
- Editar TODOS los campos directamente (sin cambios pendientes)
- Gestionar galería de fotos (tabla `comercio_fotos`)
- Gestionar horarios (tabla `comercio_horarios`)
- Toggle activo/inactivo
- Eliminar comercio
- Cambiar plan
- Validar/aprobar comercio (toggle `validado`)
- Ver y gestionar productos de cualquier comercio

---

### 3.3 Panel admin — Gestión de reseñas

**Rutas (11):**
```
GET  /admin/resenas                  → index
GET  /admin/resenas/reportes         → reportes
GET  /admin/resenas/configuracion    → ResenaConfigController@index
POST /admin/resenas/configuracion    → ResenaConfigController@update
GET  /admin/resenas/{id}             → show (detalle)
POST /admin/resenas/aprobar/{id}     → aprobar
POST /admin/resenas/rechazar/{id}    → rechazar
POST /admin/resenas/responder/{id}   → responder
POST /admin/resenas/eliminar/{id}    → eliminar
POST /admin/resenas/bulk             → bulk (acción masiva)
POST /admin/resenas/reportes/eliminar/{id} → deleteReport
```

**Flujo de moderación:**
1. Reseña llega con estado `pendiente`
2. Admin ve en listado: negocio, autor, puntuación, comentario truncado, estado (badge), fecha, IP
3. Click en reseña → detalle completo con datos del autor, email, IP, user_agent
4. Acciones: Aprobar (→ `aprobada`), Rechazar (→ `rechazada`), Responder (texto), Eliminar
5. Bulk: seleccionar múltiples + acción masiva

**Configuración:** Admin puede configurar parámetros de reseñas desde `/admin/resenas/configuracion`
**Reportes:** Vista de reseñas reportadas por usuarios (`resenas_reportes`)
**Estado actual:** 0 reseñas, 0 reportes

---

### 3.4 Panel admin — Reportes y analytics

**Rutas (7):**
```
GET /admin/reportes           → index (dashboard general)
GET /admin/reportes/visitas   → visitas (detalle)
GET /admin/reportes/comercios → comercios (por comercio)
GET /admin/reportes/categorias → categorias
GET /admin/reportes/fechas    → fechas especiales
GET /admin/reportes/banners   → banners (clicks, impresiones)
GET /admin/reportes/export    → exportCsv
```

**Datos disponibles:**
- Visitas por día/semana/mes desde `visitas_log` (16,329 registros) y `analytics_diario` (1,840)
- Visitas por comercio con ranking
- Visitas por categoría
- Visitas por fecha especial
- Banners: clicks + impresiones
- Export CSV con rango de fechas

---

### 3.5 Sistema de permisos y roles

**Archivo:** `config/permissions.php`

```php
'superadmin'  => ['*'],           // Todo
'admin'       => ['*'],           // Todo
'editor'      => [17 módulos],    // dashboard, comercios, categorias, fechas, noticias,
                                  // banners, reportes, share, resenas, planes, contacto,
                                  // mensajes, nurturing, correos, notificaciones, redes,
                                  // apariencia, perfil
'comerciante' => ['dashboard', 'perfil'],  // Solo dashboard y perfil
```

**Middleware:** `app/Middleware/AuthMiddleware.php`
- Verifica `$_SESSION['admin']['id']`
- Verifica expiración: `$_SESSION['admin_expires']`
- Extrae módulo de la URI: `/admin/{módulo}/...`
- Consulta `Permission::can($rol, $modulo)`
- Si no tiene permiso → `Response::error(403)`

**Middleware adicional:** `PermissionMiddleware.php` — redundante con AuthMiddleware (ambos hacen la misma verificación). AuthMiddleware ya incluye el check de permisos.

**¿Qué pasa si comerciante accede a /admin/?**
El comerciante usa sesión separada (`$_SESSION['comerciante']`). Si intenta acceder a `/admin/`, AuthMiddleware verifica `$_SESSION['admin']` que estará vacío → redirige a `/admin/login`. Los roles son del sistema admin, no del comerciante.

---

### 3.6 Sistema de autenticación

**Login admin vs comerciante: SEPARADOS**

| Aspecto | Admin | Comerciante |
|---|---|---|
| Ruta login | `/admin/login` | `/mi-comercio/login` |
| Controlador | `Admin\AuthController` | `Public\ComercianteController` |
| Tabla | `admin_usuarios` (rol: admin/superadmin/editor/comerciante) | misma tabla, filtro rol='comerciante' |
| Sesión | `$_SESSION['admin']` | `$_SESSION['comerciante']` |
| Servicio | `Auth::attempt()` | Método propio en controlador |

**Configuración de sesión** (`config/app.php`):
- `SESSION_NAME` = 'regalos_sess'
- `SESSION_LIFETIME` = 7200 (2 horas)
- `cookie_httponly` = true, `cookie_secure` = true (producción), `cookie_samesite` = 'Lax'

**Tabla `sesiones_admin`:** 13 registros — tracking de sesiones activas con token, IP, user_agent, expiración

**Protección brute-force:**
- Tabla `login_intentos`: 157 registros con ip, email, exitoso, created_at
- El registro de intentos existe pero la verificación de límites se maneja implícitamente por el RateLimiter
- Intentos fallidos se registran, intentos exitosos también

---

## Hallazgos Generales

### Inconsistencias

1. **PermissionMiddleware es redundante** — AuthMiddleware ya verifica permisos. Ambos hacen exactamente lo mismo.
2. **Tabla comercios no tiene columna `status`** — El AUDIT_REPORT anterior mencionaba `status` pero no existe. Se usa `activo` (0/1) + `validado` (0/1).
3. **Login comerciante usa misma tabla** que admin (`admin_usuarios` con rol='comerciante') pero sistema de sesión completamente separado.

### Campos/tablas sin uso

| Tabla/Campo | Registros | Observación |
|---|---|---|
| `comercio_fotos` | 0 | Galería implementada en admin pero ningún comercio ha subido fotos |
| `comercio_horarios` | 0 | Horarios implementados en admin pero no ingresados |
| `comercio_renovaciones` | 0 | Sistema de renovación implementado pero sin uso |
| `resenas` | 0 | Sistema completo pero sin reseñas |
| `resenas_reportes` | 0 | Sin reportes |
| `productos` | 1 | Solo 1 producto de 1 comercio |
| `producto_fotos` | 7 | 7 fotos del único producto |

### Diferencias admin vs comerciante

| Capacidad | Admin | Comerciante |
|---|---|---|
| Editar comercio | Directo | Cambios pendientes |
| Galería fotos | Sí (CRUD) | No |
| Horarios | Sí (CRUD) | No |
| Productos | Sí (de cualquier comercio) | Solo los suyos |
| Toggle activo | Sí | No |
| Cambiar plan | Sí | Solicitar renovación |
| Validar | Sí | No |
| Eliminar | Sí | No |
| Reseñas | Moderar todas | No |
| Reportes | Todos | Solo sus stats |

---

## Recomendaciones

### Prioridad Alta

1. **Incentivar carga de datos:** 0 fotos en galería, 0 horarios, 0 reseñas, 1 producto. Los sistemas están implementados pero vacíos.
2. **Dar acceso a galería y horarios al comerciante** desde `/mi-comercio` — actualmente solo el admin puede gestionarlos.
3. **Implementar exclusión de bots en VisitTracker** — user_agent filtering para no inflar estadísticas.

### Prioridad Media

4. **Eliminar PermissionMiddleware redundante** o diferenciarlo de AuthMiddleware.
5. **Agregar rechazo explícito de comercios** con motivo y notificación al comerciante.
6. **Configurar Google Analytics** — campos vacíos en seo_config, CSP ya permite los dominios.

### Prioridad Baja

7. **Agregar campos de despacho al registro** (delivery_local, envios_chile) — existen en BD pero no se piden.
8. **Limitar reseñas por IP** sin cuenta de usuario para prevenir spam.
9. **Agregar vista de stats para el comerciante** — actualmente el dashboard muestra stats básicos pero podría ser más detallado.

---

*Auditoría generada el 2026-04-11. Solo lectura — no se realizaron modificaciones.*
