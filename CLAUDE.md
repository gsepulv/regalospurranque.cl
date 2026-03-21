# CLAUDE.md — Regalos Purranque v2

Referencia maestra del proyecto. Toda modificacion debe consultarse contra este documento.

## Proyecto

- **Nombre**: Regalos Purranque — Directorio comercial digital de Purranque, Region de Los Lagos
- **Stack**: PHP 8.3 vanilla MVC, MySQL 8.4, Apache, sin Composer ni frameworks
- **Produccion**: https://regalospurranque.cl (canonical), https://v2.regalos.purranque.info (redirect)
- **Hosting**: HostGator compartido, cPanel, sin exec()
- **Deploy**: cPanel Git Version Control > Update from Remote (usa `.cpanel.yml`)
- **Deploy path**: `/home/purranque/v2.regalos.purranque.info/`
- **SSH**: `ssh -i ~/.ssh/purranque_key purranque@162.241.53.185`
- **PHP local**: `C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe`
- **MySQL local**: `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root regalos_v2`
- **DB local**: `regalos_v2` | **DB produccion**: `purranque_regalos_v2`

---

## Estructura de directorios (2 niveles)

```
regalospurranque.cl/
├── app/
│   ├── Controllers/          # Admin/, Public/, Api/
│   ├── Core/                 # App, Controller, Database, Router, View, Request, Response, Middleware
│   ├── Middleware/            # Auth, Csrf, Maintenance, Permission, Redirect
│   ├── Models/               # 23 modelos
│   ├── Services/             # 23 servicios
│   └── helpers.php           # Funciones globales: e(), url(), asset(), csrf_field(), slugify(), picture()
├── assets/
│   ├── css/                  # rp2.css, rp2.min.css, admin.css, mapa.css
│   ├── js/                   # app.js, app.min.js, admin.js, mapa.js
│   ├── img/                  # logos/, portadas/, galeria/, banners/, noticias/, og/, iconos/
│   └── vendor/               # leaflet/ (mapas), tinymce/ (editor WYSIWYG)
├── config/
│   ├── app.php               # Constantes: APP_ENV, SITE_URL, UPLOAD_MAX_SIZE (5MB), PER_PAGE (12)
│   ├── database.php          # Credenciales BD (gitignored)
│   ├── mail.php              # SMTP Gmail (gitignored)
│   ├── backup.php            # Google Drive config (gitignored)
│   ├── captcha.php           # Cloudflare Turnstile (gitignored, HABILITADO en produccion)
│   ├── middleware.php         # Registro: auth, csrf, permission, maintenance, redirect
│   ├── permissions.php        # RBAC: superadmin/admin(*), editor(17 modulos), comerciante(dashboard,perfil)
│   └── routes.php            # ~240 rutas
├── cron/
│   ├── analytics-daily.php   # Agregacion diaria de visitas
│   ├── backup-auto.php       # Backup BD a Google Drive
│   ├── email-recordatorios.php
│   ├── email-registro-ventanas.php
│   ├── expiracion-comercios.php
│   └── notificaciones.php
├── database/
│   ├── schema.sql            # DDL completo (40 tablas en produccion, schema.sql incompleto)
│   ├── seed.sql              # Datos iniciales produccion
│   ├── seed_local.sql        # Datos desarrollo
│   └── migrations/           # Migraciones incrementales
├── deploy/
│   └── verify.php            # Verificacion post-deploy
├── legales/                  # Documentos legales + SQL parches
├── lib/PHPMailer/            # Libreria email (sin Composer)
├── scripts/                  # minify.php, optimizar-imagenes.php
├── storage/
│   ├── backups/              # Backups BD automaticos
│   ├── cache/                # Cache aplicacion
│   ├── comprobantes/         # Comprobantes de pago
│   ├── logs/                 # Logs de error
│   └── temp/
├── views/
│   ├── admin/                # 24 subdirectorios (1 por modulo)
│   ├── comerciante/          # dashboard, editar, login, perfil, olvide-contrasena
│   ├── emails/               # 26 templates de email
│   ├── errors/               # 403, 404, 500
│   ├── layouts/              # admin.php, public.php, login.php
│   ├── partials/             # 19 componentes reutilizables
│   └── public/               # 18 paginas + registro-comercio/
├── .htaccess                 # Rewrite, seguridad, HSTS, CSP, cache
├── .cpanel.yml               # Copia archivos a deploy path
├── index.php                 # Entry point (front controller)
├── manifest.json             # PWA config
├── sw.js                     # Service Worker (network-first, cache v3)
└── robots.txt                # Bloquea /admin/, /api/, /storage/
```

---

## Rutas y endpoints

### Publicas (25 rutas)

| Metodo | URL | Controlador::metodo |
|--------|-----|---------------------|
| GET | `/` | HomeController::index |
| GET | `/comercios` | ComercioController::index |
| GET | `/comercio/{slug}` | ComercioController::show |
| GET | `/categorias` | CategoriaController::index |
| GET | `/categoria/{slug}` | CategoriaController::show |
| GET | `/celebraciones` | FechaController::index |
| GET | `/fecha/{slug}` | FechaController::show |
| GET | `/buscar` | BuscarController::index |
| GET | `/noticias` | NoticiaController::index |
| GET | `/noticia/{slug}` | NoticiaController::show |
| GET | `/mapa` | MapaController::index |
| GET | `/planes` | PlanesController::index |
| GET | `/contacto` | ContactoController::index |
| POST | `/contacto/enviar` | ContactoController::send |
| GET | `/sitemap.xml` | HomeController::sitemap |
| GET | `/feed/rss.xml` | FeedController::rss |
| GET | `/terminos` | PageController::terminos |
| GET | `/privacidad` | PageController::privacidad |
| GET | `/cookies` | PageController::cookies |
| GET | `/contenidos` | PageController::contenidos |
| GET | `/derechos` | DerechosController::index |
| POST | `/derechos` | DerechosController::store |
| GET | `/mis-resenas` | ReviewController::misResenas |
| GET | `/compartir/{tipo}/{slug}` | ShareController::show |
| GET | `/desuscribir/{token}` | DesuscripcionController::confirmar |

### Registro de comerciantes (5 rutas)

| Metodo | URL | Controlador::metodo |
|--------|-----|---------------------|
| GET | `/registrar-comercio` | RegistroComercioController::index |
| POST | `/registrar-comercio/cuenta` | RegistroComercioController::storeCuenta |
| GET | `/registrar-comercio/datos` | RegistroComercioController::datos |
| POST | `/registrar-comercio/store` | RegistroComercioController::storeDatos |
| GET | `/registrar-comercio/gracias` | RegistroComercioController::gracias |

### Panel comerciante — mi-comercio (14 rutas)

| Metodo | URL | Controlador::metodo |
|--------|-----|---------------------|
| GET | `/mi-comercio` | ComercianteController::dashboard |
| GET/POST | `/mi-comercio/login` | ComercianteController::loginForm / login |
| GET | `/mi-comercio/logout` | ComercianteController::logout |
| GET | `/mi-comercio/editar` | ComercianteController::editar |
| POST | `/mi-comercio/guardar` | ComercianteController::guardar |
| POST | `/mi-comercio/solicitar-renovacion` | ComercianteController::solicitarRenovacion |
| GET | `/mi-comercio/perfil` | ComercianteController::perfil |
| POST | `/mi-comercio/perfil/password` | ComercianteController::updatePassword |
| POST | `/mi-comercio/perfil/datos` | ComercianteController::updateDatos |
| GET/POST | `/mi-comercio/olvide-contrasena` | ComercianteController::forgotPasswordForm / sendResetLink |
| GET/POST | `/mi-comercio/reset/{token}` | ComercianteController::resetPasswordForm / resetPassword |

### Admin auth (4 rutas)

| Metodo | URL | Controlador::metodo |
|--------|-----|---------------------|
| GET | `/admin`, `/admin/login` | AuthController::loginForm |
| POST | `/admin/login` | AuthController::login |
| GET | `/admin/logout` | AuthController::logout |

### Admin CRUD (~190 rutas, todas con middleware auth)

| Modulo | Prefijo | Controlador | Operaciones |
|--------|---------|-------------|-------------|
| Dashboard | `/admin/dashboard` | DashboardController | index |
| Comercios | `/admin/comercios` | ComercioAdminController | CRUD + toggle + galeria + horarios |
| Categorias | `/admin/categorias` | CategoriaAdminController | CRUD completo |
| Fechas | `/admin/fechas` | FechaAdminController | CRUD completo |
| Noticias | `/admin/noticias` | NoticiaAdminController | CRUD + toggle + upload imagen |
| Banners | `/admin/banners` | BannerAdminController | CRUD + toggle + resetStats |
| Usuarios | `/admin/usuarios` | UsuarioAdminController | CRUD + toggle |
| Planes | `/admin/planes` | PlanAdminController | CRUD + assign + validar + toggleSello |
| Cambios | `/admin/cambios-pendientes` | CambiosPendientesController | index, show, aprobar, rechazar |
| Renovaciones | `/admin/renovaciones` | RenovacionAdminController | index, show, aprobar, rechazar, comprobante |
| Resenas | `/admin/resenas` | ResenaAdminController | index, show, reportes, aprobar, rechazar, responder, eliminar, bulk |
| Resenas config | `/admin/resenas/configuracion` | ResenaConfigController | index, update |
| Reportes | `/admin/reportes` | ReporteAdminController | index, visitas, comercios, categorias, fechas, banners, exportCsv |
| SEO | `/admin/seo` | SeoAdminController | index, saveConfig, metatags, schema, redirects, sitemap |
| Contacto | `/admin/contacto` | ContactoAdminController | index, eliminar |
| Mensajes | `/admin/mensajes` | MensajeAdminController | index, dashboard, detectar, ver, responder, estado, nota, eliminar |
| Nurturing | `/admin/nurturing` | NurturingAdminController | dashboard, config, plantillas CRUD, contactos, acciones masivas |
| Correos | `/admin/correos` | CorreoAdminController | enviar, send, preview |
| Notificaciones | `/admin/notificaciones` | NotificacionAdminController | index, saveConfig, test, logView, cleanLog |
| Share | `/admin/share` | ShareAdminController | index |
| Redes | `/admin/redes-sociales` | RedesAdminController | index, update |
| Apariencia | `/admin/apariencia` | AparienciaAdminController | index, update, preset, autoShades |
| Mantenimiento | `/admin/mantenimiento` | MantenimientoController | index (hub) |
| Backups | `/admin/mantenimiento/backups` | BackupController | list, backupDb/Files/Full, download, delete, drive |
| Archivos | `/admin/mantenimiento/archivos` | FileExplorerController | browse, view, download, upload, mkdir, rename, delete |
| Salud | `/admin/mantenimiento/salud` | HealthController | index, refresh |
| Logs | `/admin/mantenimiento/logs` | LogsController | index, export, clean, show |
| Herramientas | `/admin/mantenimiento/herramientas` | ToolsController | sitemap, cache, maintenance, phpinfo, sessions, tables, images, stats |
| Configuracion | `/admin/mantenimiento/configuracion` | ConfigController | index, update |
| Perfil | `/admin/perfil` | PerfilController | index, updatePassword |
| Sitios | `/admin/sitios` | SitioAdminController | CRUD + toggle + switchSite |

### API (7 rutas)

| Metodo | URL | Controlador::metodo |
|--------|-----|---------------------|
| POST | `/api/reviews/create` | ReviewApiController::create |
| GET | `/api/reviews/list/{id}` | ReviewApiController::list |
| POST | `/api/reviews/report` | ReviewApiController::report |
| POST | `/api/track` | TrackApiController::track |
| POST | `/api/banner-track` | BannerApiController::track |
| POST | `/api/share-track` | ShareApiController::track |
| POST | `/api/consentimiento` | ConsentimientoApiController::store |

---

## Controladores (53 total)

### Public (16)

| Controlador | Modelos usados | Vistas |
|-------------|---------------|--------|
| HomeController | Categoria, Comercio, Noticia, Banner, FechaEspecial | public/home |
| ComercioController | Comercio, Categoria, Resena, Banner, VisitTracker | public/comercios, public/comercio |
| CategoriaController | Categoria, Comercio, Banner, VisitTracker | public/categorias, public/categoria |
| FechaController | FechaEspecial, Comercio, Banner, VisitTracker | public/celebraciones, public/fecha |
| NoticiaController | Noticia, Banner, VisitTracker | public/noticias, public/noticia |
| BuscarController | Comercio, Categoria, FechaEspecial, Banner | public/buscar |
| ContactoController | MensajeContacto, Captcha, Notification | public/contacto |
| MapaController | Comercio, Categoria, VisitTracker | public/mapa |
| PlanesController | — | public/planes |
| PageController | VisitTracker | public/terminos, privacidad, cookies, contenidos |
| FeedController | Database (directo) | XML output |
| DerechosController | Captcha, Notification | public/derechos |
| ReviewController | Resena | public/resenas |
| ShareController | Comercio, Noticia | public/share |
| DesuscripcionController | Database | desuscripcion/confirmacion, error |
| RegistroComercioController | AdminUsuario, Categoria, Comercio, FechaEspecial, PoliticaAceptacion, FileManager | public/registro-comercio/* |
| ComercianteController | AdminUsuario, Comercio, Categoria, FechaEspecial, CambioPendiente, PlanConfig, RenovacionComercio | comerciante/* |

### Admin (32) — ver tabla de rutas arriba para operaciones por modulo

### API (5)

| Controlador | Modelos | Output |
|-------------|---------|--------|
| ReviewApiController | Resena, Comercio, Captcha | JSON |
| TrackApiController | VisitTracker | JSON |
| BannerApiController | BannerClick | JSON |
| ShareApiController | ShareLog | JSON |
| ConsentimientoApiController | ConsentLog | JSON |

---

## Modelos (23)

| Modelo | Tabla(s) | Metodos principales |
|--------|---------|---------------------|
| AdminUsuario | admin_usuarios | find, findByEmail, findByEmailAndRol, create, updateById, deleteById, setResetToken, clearResetToken |
| AdminLog | admin_log | find, getFiltered, getRecentLimit, getTopAcciones, getWeeklyVisitStats |
| Analytics | visitas_log, analytics_diario | registrarVisita, resumenDiario, getDashboard, getVisitasPorDia, getExportData |
| Banner | banners | getByTipo, find, create, updateById, deleteById, incrementImpresiones, incrementClicks |
| CambioPendiente | comercio_cambios_pendientes | find, getPendiente, getFiltered, create, updateById |
| Categoria | categorias | getAll, getBySlug, find, create, updateById, deleteById, getWithComerciosCount |
| Comercio | comercios + 4 pivote | getBySlug, find, create, updateById, deleteById, syncCategorias, syncFechas, addFoto, saveHorarios, checkCompletitud |
| Configuracion | configuracion | getAll, getByGroup, getByKey, upsert |
| FechaEspecial | fechas_especiales | getAll, getAllByTipo, getBySlug, find, create, updateById, deleteById, getProximas |
| MensajeContacto | mensajes_contacto | create, find, getAll, countNoLeidos, marcarLeido, marcarRespondido |
| MensajeRespuesta | mensajes_respuestas | crear, getPorMensaje, countPorMensaje |
| Noticia | noticias + 2 pivote | getAll, getBySlug, getDestacadas, find, create, updateById, deleteById, syncCategorias, syncFechas |
| NotificacionLog | notificaciones_log | getFiltered, countAll, countByEstado, deleteOlderThan |
| NurturingConfig | nurturing_config | getAll, get, set, isServicioActivo, getHoraEnvio |
| NurturingLog | nurturing_log | registrar, getPorMensaje, getEstadisticas, countHoy |
| NurturingPlantilla | nurturing_plantillas | getAll, getById, crear, actualizar, eliminar, reordenar, toggleActivo |
| PlanConfig | planes_config | find, findBySlug, getAll, create, updateById, deleteById |
| PoliticaAceptacion | politicas_aceptacion | create, registrarDecisiones, validarAceptaciones |
| RenovacionComercio | comercio_renovaciones | find, findPendiente, hasPendiente, create, updateById |
| Resena | resenas + resenas_reportes | getByComercio, getPromedio, getDistribucion, crear, reportar, updateEstado |
| SeoRedirect | seo_redirects, seo_config | getAll, find, findByUrlOrigen, create, deleteById, toggleActive, getConfig |
| Share | share_log | registrar, getTotal, getPorRed, getTopComercios |
| Sitio | sitios | find, getAll, create, updateById |

---

## Base de datos (40 tablas)

### Administracion

| Tabla | Columnas clave | Notas |
|-------|---------------|-------|
| admin_usuarios | id, nombre, email (UNIQUE), password_hash, rol (ENUM: superadmin/admin/editor/comerciante), site_id, activo, reset_token, reset_expira | Autenticacion central |
| sesiones_admin | id, usuario_id (FK), token, ip, expira | Sesiones activas |
| admin_log | id, usuario_id, modulo, accion, entidad_tipo, entidad_id, detalle, datos_antes (JSON), datos_despues (JSON), ip | Auditoria |
| configuracion | clave (PK), site_id, valor, grupo | Key-value config: seo_, social_, apariencia_ |
| login_intentos | id, ip, email, exitoso, created_at | Rate limiting: max 5/15min |

### Comercios (entidad central)

| Tabla | Columnas clave | Notas |
|-------|---------------|-------|
| comercios | id, site_id, nombre, slug (UNIQUE), descripcion, telefono, whatsapp, email, sitio_web, direccion, lat (DECIMAL 10,8), lng (DECIMAL 11,8), logo, portada, plan (ENUM), plan_inicio, plan_fin, max_fotos, activo, calidad_ok, registrado_por (FK), destacado, validado, visitas, whatsapp_clicks, seo_*, contacto_*, contrato_* | Tabla mas grande, 8 redes sociales. **calidad_ok existe en BD/admin pero NO filtra listados publicos** — si activo=1, aparece en todo |
| comercio_categoria | comercio_id (FK), categoria_id (FK), es_principal | M2M |
| comercio_fecha | comercio_id (FK), fecha_id (FK), oferta_especial, precio_desde, precio_hasta | M2M |
| comercio_fotos | id, comercio_id (FK), ruta, ruta_thumb, titulo, orden | Galeria |
| comercio_horarios | id, comercio_id (FK), dia (0-6), hora_apertura, hora_cierre, cerrado | Horarios |
| comercio_cambios_pendientes | id, comercio_id (FK), usuario_id (FK), cambios_json (JSON), estado (ENUM), revisado_por (FK) | Workflow aprobacion |
| comercio_renovaciones | id, comercio_id (FK), usuario_id (FK), plan_actual, plan_solicitado, estado, comprobante_pago, monto | Renovaciones plan |

### Contenido

| Tabla | Columnas clave | Notas |
|-------|---------------|-------|
| categorias | id, site_id, nombre, slug (UNIQUE), icono, imagen, color, orden, activo | Categorias de comercios |
| fechas_especiales | id, site_id, nombre, slug (UNIQUE), tipo (ENUM: personal/calendario/comercial), fecha_inicio, fecha_fin, recurrente | Fechas especiales |
| noticias | id, site_id, titulo, slug (UNIQUE), contenido (LONGTEXT), extracto, imagen, autor, activo, destacada, seo_*, fecha_publicacion | Articulos |
| noticia_categoria | noticia_id (FK), categoria_id (FK) | M2M |
| noticia_fecha | noticia_id (FK), fecha_id (FK) | M2M |
| banners | id, site_id, titulo, tipo (ENUM: hero/sidebar/entre_comercios/footer), imagen, url, comercio_id (FK), activo, clicks, impresiones, fecha_inicio, fecha_fin | Publicidad |

### Resenas

| Tabla | Columnas clave | Notas |
|-------|---------------|-------|
| resenas | id, site_id, comercio_id (FK), nombre_autor, email_autor, calificacion (1-5), comentario, estado (ENUM: pendiente/aprobada/rechazada), respuesta_comercio | Reviews publicas |
| resenas_reportes | id, resena_id (FK), motivo, descripcion, ip | Denuncias |

### SEO y Analytics

| Tabla | Columnas clave | Notas |
|-------|---------------|-------|
| seo_config | clave (PK), site_id, valor | Config SEO |
| seo_redirects | id, url_origen, url_destino, tipo (301/302/307), activo, hits | Redirects v1-v2 |
| visitas_log | id (BIGINT), comercio_id, pagina, tipo, ip, referrer | Alto volumen |
| analytics_diario | id, fecha, pagina, visitas, visitantes_unicos | Agregado diario |

### Comunicacion

| Tabla | Columnas clave | Notas |
|-------|---------------|-------|
| mensajes_contacto | id, nombre, email, asunto, mensaje, leido, respondido, instrucciones_enviadas | Formulario contacto |
| mensajes_respuestas | id, mensaje_id (FK), tipo (ENUM: acuse_recibo/instrucciones_registro/manual/seguimiento), asunto, contenido, enviado_por (ENUM: sistema/admin), admin_id, email_destino, enviado_exitoso | Respuestas a mensajes de contacto |
| notificaciones_log | id, destinatario, asunto, template, estado, datos (JSON) | Log emails |
| nurturing_config | id, clave (UNIQUE), valor, descripcion, tipo (ENUM: text/number/boolean/time/json), grupo, orden | Config campanas nurturing |
| nurturing_plantillas | id, numero (UNIQUE), nombre, asunto, contenido_html, contenido_texto, tono, activo, variables_disponibles | Templates email nurturing |
| nurturing_log | id, mensaje_id (FK), plantilla_id (FK), numero_recordatorio, email_destino, asunto_enviado, estado_envio (ENUM: enviado/fallido/cancelado), error_detalle | Log envios nurturing |

### Legal y compliance

| Tabla | Columnas clave | Notas |
|-------|---------------|-------|
| consentimientos | id, session_id, ip, tipo (ENUM: cookies_esenciales/cookies_todas), user_agent | Registro consentimiento cookies |
| politicas_aceptacion | id, usuario_id (FK), email, politica (ENUM), decision (ENUM: acepto/rechazo), ip_address | Aceptacion politicas en registro |
| solicitudes_arco | id, tipo (ENUM: acceso/rectificacion/cancelacion/oposicion/portabilidad), nombre, email, rut, descripcion, estado (ENUM: recibida/en_proceso/resuelta/rechazada), respuesta, ip, fecha_solicitud, fecha_respuesta, fecha_limite (GENERATED) | Derechos ARCO (ley proteccion datos) |
| registro_tratamiento | id, dato_personal, fuente (ENUM), finalidad, base_legal, plazo_conservacion, medidas_seguridad | Registro de actividades de tratamiento |

### Sistema

| Tabla | Columnas clave | Notas |
|-------|---------------|-------|
| planes_config | id, slug (UNIQUE), nombre, precio_intro, precio_regular, duracion_dias, max_fotos, max_redes, tiene_mapa, tiene_horarios, tiene_sello | Definicion de planes |
| configuracion_mantenimiento | clave (PK), valor | Config modo mantenimiento |
| redes_sociales_config | id, site_id, clave, valor | Config redes sociales |
| share_log | id, comercio_id, pagina, red_social, ip | Tracking compartir |
| seguimiento_conversiones | id, fecha (UNIQUE), mensajes_recibidos/leidos/respondidos/convertidos/descartados, acuses_enviados, instrucciones_enviadas, tasa_conversion, tiempo_respuesta_avg | Metricas diarias de conversion contacto→comerciante |
| sitios | id, nombre, slug, dominio, logo, ciudad, lat, lng, activo | Multi-sitio |

---

## Middlewares

| Middleware | Archivo | Funcion |
|-----------|---------|---------|
| AuthMiddleware | app/Middleware/AuthMiddleware.php | Verifica `$_SESSION['admin']`, valida expiracion, renueva lifetime, chequea permisos del modulo. Redirige a /admin/login si no hay sesion. |
| CsrfMiddleware | app/Middleware/CsrfMiddleware.php | Solo POST. Valida `_csrf` param o header `X-CSRF-TOKEN` contra `$_SESSION['csrf_token']`. Regenera token despues de validar (single-use). |
| MaintenanceMiddleware | app/Middleware/MaintenanceMiddleware.php | Permite /admin y /api siempre. Para publicas, chequea `storage/cache/maintenance.flag`. Sirve `mantenimiento.html` con 503. |
| PermissionMiddleware | app/Middleware/PermissionMiddleware.php | Extrae modulo del URI `/admin/{modulo}`. Verifica rol vs config/permissions.php. 403 si denegado. |
| RedirectMiddleware | app/Middleware/RedirectMiddleware.php | Salta /admin y /api. Busca en tabla `seo_redirects`. Incrementa hits. Soporta 301/302/307. |

### Roles y permisos

| Rol | Acceso |
|-----|--------|
| superadmin | Todo (*) incluyendo gestion de sitios |
| admin | Todo (*) |
| editor | 17 modulos: dashboard, comercios, categorias, fechas, noticias, banners, reportes, share, resenas, planes, contacto, mensajes, nurturing, correos, notificaciones, redes, apariencia, perfil |
| comerciante | Solo dashboard y perfil (accede via /mi-comercio, sesion separada) |

### Autenticacion

- **Admin**: `$_SESSION['admin']` con id, nombre, email, rol. Lifetime: `SESSION_LIFETIME` (2h). Brute force: max 5 intentos/15min por IP (tabla `login_intentos`).
- **Comerciante**: `$_SESSION['comerciante']` con id, nombre, email. Sesion separada del admin. Password recovery: token 32-byte hex, 1 hora expiracion.

---

## Core (app/Core/)

| Clase | Funcion |
|-------|---------|
| App | Bootstrap: carga config, inicia sesion, ejecuta middleware global, despacha ruta |
| Router | Matching de URI con parametros dinamicos `{slug}`, middleware por ruta |
| Controller | Base: render(), renderAdmin(), redirect(), json(), validate(), audit() |
| View | Render: auto-detecta layout (admin/public/login), extract($data) como variables locales |
| Database | Singleton PDO: getInstance(), fetch(), fetchAll(), execute(), insert(), update(), delete(), transaction() |
| Request | Wrapper HTTP: method(), uri(), get(), post(), all(), only() |
| Response | Helpers: redirect(), json(), error(), download() |
| Middleware | Clase abstracta base: handle() |

### Patrones clave

- **Modelos**: metodos static, usan `Database::getInstance()` internamente
- **Base Controller**: `$this->db` para queries, `$this->audit($uid, 'accion', 'modulo', "detalle", $rid?, 'tipo?')` para log
- **Config dinamica**: tabla `configuracion` con patron grupo/clave/valor
- **CSRF**: token single-use, se regenera tras cada POST
- **Views**: variables disponibles globalmente: `$admin`, `$csrf`, `$flash`
- **Layouts auto-detectados**: `admin/login*` → login, `admin/*` → admin, todo lo demas → public
- **Flash messages**: `$_SESSION['flash_error']`, `$_SESSION['flash_success']`, `$_SESSION['flash_errors']` (array)
- **Sin exec()**: restriccion del hosting compartido

---

## Servicios (app/Services/)

### Core

| Servicio | Proposito |
|----------|-----------|
| Auth | Login/logout admin, verificacion sesion |
| Validator | Validacion encadenable: required, string, email, numeric, integer, min, max, in, url, slug, unique, date, latitude, longitude, phone, afterField |
| Seo | Meta tags, Open Graph, Schema.org JSON-LD |
| Theme | Colores dinamicos desde config admin (CSS variables) |
| Logger | Auditoria acciones admin (tabla admin_log) |
| Permission | RBAC: verifica rol vs modulo |

### Archivos y backup

| Servicio | Proposito |
|----------|-----------|
| FileManager | Upload imagenes, validacion MIME con finfo, redimension, thumbnails, WebP |
| GoogleDrive | Backup a Google Drive via Service Account |
| Backup | Backup BD + archivos, listar, restaurar, eliminar |

### Comunicacion

| Servicio | Proposito |
|----------|-----------|
| Mailer | Envio email SMTP (Gmail) via PHPMailer, fallback a mail() |
| Notification | Orquesta emails: nuevaResena, comercioAprobado, registroComercianteAdmin, etc. |
| RedesSociales | Config redes sociales, OG defaults |
| Captcha | Cloudflare Turnstile (HABILITADO en produccion: registro, login admin, login comerciante) |

### Tracking y SEO

| Servicio | Proposito |
|----------|-----------|
| VisitTracker | Registra visitas a paginas |
| SitemapService | Genera sitemap.xml |
| SiteManager | Multi-sitio (preparado, 1 sitio activo) |

### Pagos (stubs, no implementados)

PasarelaPago, PagoFlow, PagoMercadoPago, PagoWebpay, PagoTransferencia

---

## Helpers (app/helpers.php)

```
e($string)           # HTML escape (XSS prevention)
csrf_field()         # Hidden CSRF token input
csrf_token()         # Get/generate CSRF token
sanitize_html($html) # Remove dangerous HTML/scripts
url($path)           # Generate absolute URL
asset($path)         # Generate asset URL (assets/...)
old($field, $default)# Repopulate form fields from flash
slugify($text)       # Text to URL slug (maneja acentos espanol)
truncate($text, $len)# Truncate with ellipsis
fecha_es($date, $fmt)# Format date in Spanish
picture($img, $alt)  # <picture> con WebP + fallback, srcset, lazy loading
dd(...$vars)         # Dump and die (solo si APP_DEBUG=true)
```

---

## Vistas

### Layouts (3)

| Layout | Uso |
|--------|-----|
| layouts/public.php | Sitio publico: GTM, Meta Pixel, PWA, tema dinamico, nav + footer |
| layouts/admin.php | Panel admin: sidebar (17 items con badges), topbar, toast, modal delete |
| layouts/login.php | Login: minimal, incluye Captcha script |

### Partials (19)

nav.php (navbar), sidebar.php (admin sidebar con badges), footer.php, seo-head.php (meta+OG+Schema.org), pagination.php, breadcrumbs.php, topbar.php (admin), toast.php (flash), modal.php, cookie-banner.php (GDPR), share-buttons.php, share-float.php, whatsapp-float.php, session-bar.php, card-badges.php, comercio-redes.php, cta-comercio.php, comerciante-topbar.php, social-profiles.php

### Email templates (26 en views/emails/)

arco-admin, arco-confirmacion, backup-completado, cambios-pendientes-admin, comercio-aprobado, comercio-bienvenida, comercio-rechazado, contacto-acuse, contacto-instrucciones-registro, contacto-mensaje, error-sistema, fecha-proxima, layout (base), nueva-resena, nuevo-comercio, registro-comerciante-admin, renovacion-aprobada, renovacion-nueva-admin, renovacion-rechazada, reporte-resena, resena-aprobada, resena-rechazada, resena-respuesta, reset-password, resumen-semanal, test

---

## Assets y dependencias

### CSS/JS propios
- `rp2.css` / `rp2.min.css` — Estilos publicos (custom, sin framework)
- `admin.css` — Estilos panel admin
- `mapa.css` / `mapa.min.css` — Estilos mapa
- `app.js` / `app.min.js` — JS publico (vanilla)
- `admin.js` — JS admin
- `mapa.js` / `mapa.min.js` — Mapa Leaflet

### Librerias bundled
- **Leaflet** v1.x — `assets/vendor/leaflet/` (mapas con OpenStreetMap tiles)
- **TinyMCE** v6.x — `assets/vendor/tinymce/` (editor WYSIWYG admin)

### CDN externos
- Google Tag Manager (GTM-56D8422F)
- Meta Pixel / Facebook (ID: 1223892215809376)
- OpenStreetMap tiles
- Google Fonts
- Quill.js 1.3.7 (solo en formulario de noticias)
- jsDelivr / Cloudflare CDN (referenciados en CSP)

### Directorios de uploads (todos gitignored)
- `assets/img/logos/` — Logos comercios
- `assets/img/portadas/` — Portadas comercios
- `assets/img/galeria/` — Fotos galeria
- `assets/img/banners/` — Imagenes banners
- `assets/img/noticias/` — Imagenes noticias + inline editor
- `assets/img/og/` — Imagenes Open Graph
- `assets/img/config/` — Favicon, logos del sitio
- `storage/comprobantes/` — Comprobantes de pago

---

## Archivos especiales

### .htaccess
- Force HTTPS (excepto localhost)
- Redirect www → sin www, v2.regalos.purranque.info → regalospurranque.cl
- Front controller: todo a index.php
- Bloquea directorios: app/, config/, storage/, database/, cron/, deploy/, views/, legales/, lib/
- Bloquea extensiones: .sql, .md, .log, .sh, .bak, .env, .yml, .zip, .gz
- Headers: HSTS (1 ano), X-Content-Type-Options, X-Frame-Options, CSP, Permissions-Policy
- Cache: imagenes 1 mes, CSS/JS 1 semana, fonts 1 mes
- Gzip habilitado

### Service Worker (sw.js)
- Estrategia: Network First
- Precache: /, /offline.html, rp2.css, app.js, manifest.json, favicon.ico
- Cache name: regalos-v2-cache-v3, max 100 items
- Excluye: /admin/*, /api/*, dominios externos, tiles
- Fallback offline: /offline.html

### PWA (manifest.json)
- Display: standalone, theme: #ea580c (naranja)
- Shortcuts: Buscar comercios → /buscar, Ver mapa → /mapa

### Cron jobs

| Script | Funcion |
|--------|---------|
| analytics-daily.php | Agrega visitas del dia en analytics_diario |
| backup-auto.php | Backup BD a Google Drive |
| email-recordatorios.php | Recordatorios por email |
| email-registro-ventanas.php | Emails ventana de registro |
| expiracion-comercios.php | Gestiona comercios expirados |
| notificaciones.php | Envio notificaciones pendientes |

---

## Dependencias entre modulos

### Comercios (modulo central)
- **Tablas**: comercios, comercio_categoria, comercio_fecha, comercio_fotos, comercio_horarios
- **Depende de**: categorias, fechas_especiales, admin_usuarios (registrado_por), planes_config
- **Dependientes**: banners (comercio_id FK), resenas, visitas_log, share_log, comercio_renovaciones, comercio_cambios_pendientes

### Categorias
- **Tablas**: categorias, comercio_categoria, noticia_categoria
- **Dependientes**: comercios (M2M), noticias (M2M)
- **Impacto**: eliminar categoria rompe relaciones con comercios y noticias

### Noticias
- **Tablas**: noticias, noticia_categoria, noticia_fecha
- **Depende de**: categorias (M2M), fechas_especiales (M2M)
- **Editor**: Quill.js via CDN en form.php (textarea oculto)

### Fechas especiales
- **Tablas**: fechas_especiales, comercio_fecha, noticia_fecha
- **Dependientes**: comercios (M2M), noticias (M2M)

### Banners
- **Tablas**: banners
- **Depende de**: comercios (FK opcional)
- **Tracking**: api/banner-track incrementa clicks/impresiones

### Resenas
- **Tablas**: resenas, resenas_reportes
- **Depende de**: comercios (FK obligatorio)
- **API publica**: /api/reviews/create, /api/reviews/list/{id}, /api/reviews/report

### Planes y renovaciones
- **Tablas**: planes_config, comercio_renovaciones
- **Depende de**: comercios, admin_usuarios
- **Planes**: freemium, basico, premium, sponsor, banner

### Mensajes / Nurturing
- **Tablas**: mensajes_contacto, mensajes_respuestas, nurturing_config, nurturing_plantillas, nurturing_log
- **Nurturing depende de**: mensajes_contacto (FK)
- **Email**: usa Mailer + Notification services

### SEO
- **Tablas**: seo_config, seo_redirects
- **Genera**: sitemap.xml via SitemapService
- **Middleware**: RedirectMiddleware lee seo_redirects en cada request publico

### Usuarios admin
- **Tablas**: admin_usuarios, sesiones_admin, login_intentos
- **Dependientes**: comercios (registrado_por FK), admin_log, comercio_renovaciones, comercio_cambios_pendientes

### Registro publico de comerciantes
- **Flujo**: 2 pasos (cuenta → datos)
- **Tablas**: admin_usuarios (rol=comerciante), comercios (activo=0), politicas_aceptacion
- **Notifica**: admin via email (registro-comerciante-admin template)

### Dashboard del comerciante (mi-comercio)
- **Tablas**: admin_usuarios, comercios, comercio_cambios_pendientes, comercio_renovaciones
- **Flujo edicion**: cambios van a comercio_cambios_pendientes (estado=pendiente), admin aprueba/rechaza

### Backup automatico
- **Servicios**: Backup, GoogleDrive
- **Storage**: storage/backups/ (local) + Google Drive (remoto)
- **Cron**: backup-auto.php

---

## Flujos principales

### Registro de comerciante
1. `GET /registrar-comercio` → form cuenta (nombre, email, password)
2. `POST /registrar-comercio/cuenta` → crea admin_usuarios con rol=comerciante + politicas_aceptacion
3. `GET /registrar-comercio/datos` → form datos del comercio
4. `POST /registrar-comercio/store` → crea comercios con plan=freemium, activo=0 (dentro de transaccion)
5. Redirige a `/registrar-comercio/gracias`
6. Admin recibe email y activa comercio + usuario desde el panel

### Panel del comerciante
- Login: `/mi-comercio/login` (sesion `$_SESSION['comerciante']`, separada de admin)
- Dashboard: `/mi-comercio` — datos comercio, plan, cambios pendientes
- Edicion: cambios NO se aplican directo, van a `comercio_cambios_pendientes` como JSON
- Admin revisa en `/admin/cambios-pendientes` y aprueba/rechaza

### Renovacion de plan
1. Comerciante solicita desde `/mi-comercio` con comprobante de pago
2. Se crea registro en `comercio_renovaciones` con estado=pendiente
3. Admin revisa en `/admin/renovaciones`, aprueba o rechaza
4. Si aprobado: actualiza plan del comercio, extiende fecha, notifica por email

---

## ADVERTENCIAS

### Observaciones tecnicas vigentes

1. **Dos editores WYSIWYG**: TinyMCE (bundled en assets/vendor/) y Quill.js (CDN en form noticias). Deberia unificarse.
2. **schema.sql incompleto**: Produccion tiene 40 tablas pero schema.sql no incluye: consentimientos, registro_tratamiento, seguimiento_conversiones, solicitudes_arco, configuracion_mantenimiento, mensajes_respuestas, nurturing_config, nurturing_log, nurturing_plantillas.
3. **Servicios de pago son stubs**: PagoFlow, PagoMercadoPago, PagoWebpay, PagoTransferencia no implementados.
4. **Captcha Turnstile HABILITADO** en produccion. Protege: registro comerciante, login admin, login comerciante. Impide testing automatizado via curl.
5. **Multi-sitio preparado pero no activo**: tabla sitios existe, site_id en la mayoria de tablas, solo 1 sitio.
6. **Sitemap estatico**: No se regenera automaticamente al crear contenido. Debe regenerarse desde admin > SEO o admin > herramientas.
7. **calidad_ok no filtra**: El campo existe en BD y admin pero fue removido de todos los queries publicos (2026-03-21). Si activo=1, el comercio aparece en listados, busqueda, mapa, categorias y fechas.

### Limpieza realizada (2026-03-21)

Los siguientes archivos fueron eliminados de produccion y/o sacados del tracking de git:
- Scripts diagnostico: rastreo-fechas.php, diagnostico-directo.php, diagnostico-noticias-store.php, _diag-css.php, setup-smtp-produccion.php, clear-cache.php, buscar_tokens_google.php, buscar_tokens_v2.php, reenviar-instrucciones.php, ig.php
- Archivos legacy: app.zip, .htaccess.zip, robots.txt.zip, config/app.php.zip, seo-regalospurranque/
- Deploy: deploy/fix-permissions.php (eliminado de prod y git)
- Todos los .bak/.bak2/.bak3 en produccion
- .gitignore actualizado para prevenir reincidencia

---

## ARCHIVOS PROTEGIDOS

Archivos criticos que nunca deben modificarse sin revision cuidadosa:

| Archivo | Razon |
|---------|-------|
| `config/routes.php` | Define todas las rutas. Error rompe todo el sitio. |
| `config/permissions.php` | RBAC. Error abre acceso no autorizado. |
| `app/Core/App.php` | Bootstrap. Error impide arranque. |
| `app/Core/Database.php` | Singleton BD. Error rompe toda query. |
| `app/Core/Router.php` | Matching de rutas. Error rompe navegacion. |
| `app/Middleware/AuthMiddleware.php` | Autenticacion. Error expone panel admin. |
| `app/Middleware/CsrfMiddleware.php` | CSRF. Error permite ataques cross-site. |
| `app/Middleware/PermissionMiddleware.php` | ACL. Error da acceso no autorizado. |
| `.htaccess` | Seguridad, HTTPS, CSP, cache. Error expone archivos sensibles. |
| `index.php` | Entry point. Error impide arranque. |
| `database/schema.sql` | DDL maestro. Debe reflejar estado real de produccion. |
| `config/app.php` | Constantes globales. Error rompe paths, URLs, sesiones. |
| `app/helpers.php` | Funciones globales (e, csrf, url). Error rompe XSS protection. |
| `sw.js` | Service Worker. Error cachea contenido incorrecto indefinidamente. |

---

## Comandos utiles

```bash
# PHP local (syntax check)
C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe -l archivo.php

# Servidor desarrollo
C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe -S localhost:8000 router.php

# MySQL local
C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root regalos_v2

# SSH produccion
ssh -i C:\Users\asus\.ssh\purranque_key purranque@162.241.53.185

# Deploy: cPanel > Git Version Control > regalospurranque.cl > Update from Remote

# Minificar assets
php scripts/minify.php
```
