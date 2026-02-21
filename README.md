# Regalos Purranque — Directorio Comercial

Directorio comercial de Purranque, Chile. Permite a comerciantes registrar sus negocios y a usuarios encontrar comercios, regalos y servicios locales.

## Stack

- PHP 8.x MVC propio (sin frameworks, sin Composer)
- MySQL 8
- Apache (HostGator compartido)
- OpenStreetMap + Leaflet (mapa interactivo)
- Cloudflare Turnstile (captcha)

## Estructura

```
app/
  Core/           ← Framework: Router, Controller, Database, View, Request, Response, Middleware
  Controllers/
    Public/       ← Controllers de páginas públicas
    Admin/        ← Controllers del panel admin (CRUD)
    Api/          ← Endpoints API (reseñas, analytics)
  Models/         ← Acceso a datos (métodos estáticos, PDO singleton)
  Services/       ← Servicios: Auth, Validator, Seo, Mailer, Logger, Captcha, etc.
  Middleware/     ← Auth, CSRF, Permissions, Maintenance, Redirect
  helpers.php     ← Funciones globales: e(), url(), asset(), csrf, slugify, etc.
config/
  app.php         ← Constantes y auto-detección de entorno
  routes.php      ← Definición de rutas (~100 rutas)
  middleware.php  ← Registro de middleware
  permissions.php ← Matriz de roles y permisos
  database.php    ← Credenciales BD (en .gitignore)
  captcha.php     ← Keys Turnstile (en .gitignore)
views/
  layouts/        ← Layout base: public, admin, login
  partials/       ← Componentes reutilizables (nav, footer, sidebar, breadcrumbs, etc.)
  public/         ← Vistas de páginas públicas
  admin/          ← Vistas del panel admin
  comerciante/    ← Portal del comerciante
  emails/         ← Templates de email transaccional
  errors/         ← 403, 404, 500
assets/
  css/            ← Estilos
  js/             ← Scripts
  img/            ← Imágenes y uploads
storage/
  logs/           ← Logs de errores y auditoría (en .gitignore)
```

## Deploy

```
git push → cPanel "Update from Remote" → "Deploy HEAD Commit"
```

## Entornos

| Entorno     | URL                              | BD                        |
|-------------|----------------------------------|---------------------------|
| Producción  | https://regalospurranque.cl       | purranque_regalos_v2 |
| Desarrollo  | http://regalos-v2.test            | regalos_v2                |
