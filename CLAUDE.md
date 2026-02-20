# Regalos Purranque v2

Directorio comercial de Purranque, Chile. PHP vanilla MVC, sin Composer ni frameworks.

## Stack

- **PHP 8.3** vanilla MVC (no Composer, no frameworks)
- **MySQL 8.4** (local: `regalos_v2`, producción: `purranque_regalos_purranque`)
- **Hosting**: HostGator compartido, cPanel, sin SSH, sin `exec()`
- **Frontend**: CSS vanilla (`assets/css/main.css`), JS vanilla (`assets/js/app.js`)
- **PWA**: manifest.json + service worker (sw.js)

## Setup local

```bash
# Servidor de desarrollo
php -S localhost:8000 router.php

# MySQL (Laragon)
mysql -u root regalos_v2
```

- PHP: `C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe`
- MySQL: `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root regalos_v2`
- Config: `config/database.php` (host, db, user, pass)
- Entorno auto-detectado en `config/app.php` por `HTTP_HOST`

## Estructura del proyecto

```
├── index.php              # Front controller
├── router.php             # Dev server + auto_prepend_file en producción
├── config/
│   ├── app.php            # Constantes globales (SITE_NAME, SITE_URL, etc.)
│   ├── database.php       # Credenciales DB
│   ├── routes.php         # [método, URI, Controller@acción, [middlewares]]
│   ├── middleware.php      # Registro nombre → clase
│   └── permissions.php    # ACL: rol → módulos permitidos
├── app/
│   ├── Core/
│   │   ├── App.php        # Bootstrap: sesión, middleware global, dispatch
│   │   ├── Router.php     # Match URI con {params} y dispatch
│   │   ├── Controller.php # Base: $db, render(), redirect(), validate(), log()
│   │   ├── Database.php   # Singleton PDO: fetch, fetchAll, insert, update, delete
│   │   ├── View.php       # Render vista + layout auto-detect
│   │   ├── Request.php    # Wrapper $_GET/$_POST/$_SERVER
│   │   └── Response.php   # JSON, redirect, error pages
│   ├── Controllers/
│   │   ├── Admin/         # Panel admin (requiere middleware 'auth')
│   │   ├── Public/        # Sitio público
│   │   └── Api/           # Endpoints JSON (sin CSRF)
│   ├── Middleware/
│   │   ├── AuthMiddleware.php       # Sesión admin + permisos por módulo
│   │   ├── CsrfMiddleware.php       # Token CSRF en POST (single-use)
│   │   ├── MaintenanceMiddleware.php
│   │   ├── RedirectMiddleware.php   # SEO redirects desde DB
│   │   └── PermissionMiddleware.php
│   ├── Models/            # (solo para queries complejas, no ORM)
│   ├── Services/
│   │   ├── Auth.php       # Login/logout admin
│   │   ├── Seo.php        # Schema.org JSON-LD builders
│   │   ├── Permission.php # ACL: can(rol, modulo)
│   │   ├── Theme.php      # CSS variables dinámicas
│   │   ├── RedesSociales.php # Config redes, OG defaults, share buttons
│   │   ├── Validator.php  # Validación de datos
│   │   ├── Logger.php     # admin_log
│   │   ├── Mailer.php     # Envío email con templates
│   │   ├── Notification.php
│   │   └── FileManager.php
│   └── helpers.php        # e(), url(), asset(), csrf_token(), slugify(), etc.
├── views/
│   ├── layouts/
│   │   ├── public.php     # Layout público (nav, footer, SEO head)
│   │   ├── admin.php      # Layout admin (sidebar, topbar)
│   │   └── login.php      # Layout login (minimal)
│   ├── partials/          # Componentes reutilizables
│   │   └── seo-head.php   # Meta tags, OG, Twitter Cards, JSON-LD, GSC
│   ├── public/            # Vistas públicas
│   ├── comerciante/       # Panel del comerciante (usa layout public)
│   ├── admin/             # Vistas admin
│   └── errors/            # 404, 403, 500
├── assets/                # CSS, JS, imágenes
├── storage/logs/          # Logs de errores
├── database/schema.sql    # Esquema completo (29 tablas)
└── deploy/                # Scripts de deploy
```

## Rutas y convenciones

### Formato de rutas
```php
// config/routes.php
['GET',  '/ruta/{param}', 'Namespace\\Controller@metodo', ['middleware1']]
```

### Namespaces
- Público: `Public\\NombreController@metodo`
- Admin: `Admin\\NombreController@metodo`
- API: `Api\\NombreController@metodo`

### URLs canónicas
- Comercios: `/comercio/{slug}`, `/comercios`
- Categorías: `/categoria/{slug}`, `/categorias`
- Fechas: `/fecha/{slug}`, `/celebraciones`
- Noticias: `/noticia/{slug}`, `/noticias`
- Registro: `/registrar-comercio` → `/registrar-comercio/datos` → `/registrar-comercio/gracias`
- Panel comerciante: `/mi-comercio`, `/mi-comercio/login`, `/mi-comercio/editar`
- Admin: `/admin/*` (requiere middleware `auth`)
- API: `/api/*` (sin CSRF)

### Layouts auto-detectados en View.php
- `admin/login*` → layout `login`
- `admin/*` → layout `admin`
- Todo lo demás → layout `public`
- `comerciante/*` vistas usan layout `public`

## Middlewares

| Nombre | Scope | Función |
|--------|-------|---------|
| `maintenance` | Global | Modo mantenimiento |
| `redirect` | Global | Redirecciones SEO desde `seo_redirects` |
| `csrf` | POST no-API | Token CSRF single-use |
| `auth` | Rutas admin | Sesión admin + permisos ACL por módulo |

## Roles y permisos

| Rol | Acceso |
|-----|--------|
| `superadmin` | Todo + gestión de sitios |
| `admin` | Todo excepto sitios |
| `editor` | Dashboard, comercios, categorías, fechas, noticias, banners, reportes, reseñas, planes, notificaciones, redes, apariencia, perfil |
| `comerciante` | Dashboard admin y perfil (pero usa panel propio en `/mi-comercio`) |

## Tablas principales (29 total)

### Core
- `admin_usuarios` — usuarios del sistema (superadmin/admin/editor/comerciante)
- `sesiones_admin` — tokens de sesión
- `admin_log` — auditoría de acciones
- `configuracion` — clave-valor agrupados

### Comercios
- `comercios` — directorio principal (incluye 8 redes sociales como columnas, plan, validación, datos tributarios)
- `categorias` — clasificación de comercios
- `comercio_categoria` — M:N comercios ↔ categorías (con `es_principal`)
- `fechas_especiales` — eventos del calendario (personal/calendario/comercial)
- `comercio_fecha` — M:N comercios ↔ fechas (con oferta y precios)
- `comercio_fotos` — galería de fotos
- `comercio_horarios` — horarios por día (0=Dom, 6=Sáb)
- `comercio_cambios_pendientes` — JSON de cambios para revisión admin

### Contenido
- `noticias` — artículos con SEO propio
- `noticia_categoria` / `noticia_fecha` — M:N
- `banners` — publicidad (hero/sidebar/entre_comercios/footer)

### Reseñas
- `resenas` — calificación 1-5, estado pendiente/aprobada/rechazada
- `resenas_reportes` — reportes de contenido inapropiado

### SEO y analytics
- `seo_config` — meta tags, GSC, GA, robots.txt
- `seo_redirects` — 301/302 personalizados
- `visitas_log` — registro detallado de visitas
- `analytics_diario` — resumen agregado

### Planes
- `planes_config` — definición de planes (freemium/basico/premium/sponsor/banner)

### Sistema
- `configuracion_mantenimiento`
- `mensajes_contacto`
- `notificaciones_log`
- `redes_sociales_config` — config redes del sitio
- `share_log` — tracking de compartidos
- `sitios` — multi-sitio

## Flujos principales

### Registro de comerciante
1. `GET /registrar-comercio` → form cuenta (nombre, email, password)
2. `POST /registrar-comercio/cuenta` → crea `admin_usuarios` con `rol=comerciante`, `activo=0`
3. `GET /registrar-comercio/datos` → form datos del comercio (categorías, fechas, redes)
4. `POST /registrar-comercio/store` → crea `comercios` con `plan=freemium`, `activo=0`
5. Redirige a `/registrar-comercio/gracias`
6. Admin recibe email y activa el comercio + usuario desde el panel

### Panel del comerciante
- Login propio en `/mi-comercio/login` (sesión `$_SESSION['comerciante']`, separada de admin)
- Dashboard: `/mi-comercio` — ve datos de su comercio, plan, cambios pendientes
- Editar: `/mi-comercio/editar` → `/mi-comercio/guardar`
- Los cambios NO se aplican directo: se guardan en `comercio_cambios_pendientes` como JSON
- Admin revisa en `/admin/cambios-pendientes` y aprueba/rechaza

### SEO
- Meta tags en `views/partials/seo-head.php` (OG, Twitter Cards, JSON-LD)
- Google Search Console: meta tag inyectado desde `seo_config.google_search_console`
- Sitemap dinámico: `GET /sitemap.xml` → `HomeController@sitemap`
- `robots.txt` estático con Sitemap apuntando al dominio canónico
- Redirecciones SEO desde `seo_redirects` via `RedirectMiddleware`

## Convenciones de código

- Sin Composer, autoloader PSR-4 manual en `index.php`
- Controladores extienden `App\Core\Controller` (acceso a `$this->db`, `$this->render()`)
- Database singleton: `Database::getInstance()->fetch/fetchAll/insert/update/delete`
- Helpers globales: `e()`, `url()`, `asset()`, `csrf_field()`, `slugify()`
- Flash messages: `$_SESSION['flash_error']`, `$_SESSION['flash_success']`, `$_SESSION['flash_errors']` (array)
- CSRF: campo `_csrf` en forms, token regenerado después de cada POST
- Sin `exec()` (restricción del hosting)
