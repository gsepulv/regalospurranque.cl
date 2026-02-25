<?php
/**
 * Definición de rutas
 * Formato: [método, URI, controlador@acción, [middlewares]]
 */

return [

    // ── Públicas ──────────────────────────────────────────────
    ['GET',  '/',                    'Public\\HomeController@index'],
    ['GET',  '/comercios',          'Public\\ComercioController@index'],
    ['GET',  '/comercio/{slug}',    'Public\\ComercioController@show'],
    ['GET',  '/categorias',         'Public\\CategoriaController@index'],
    ['GET',  '/categoria/{slug}',   'Public\\CategoriaController@show'],
    ['GET',  '/celebraciones',      'Public\\FechaController@index'],
    ['GET',  '/fecha/{slug}',       'Public\\FechaController@show'],
    ['GET',  '/buscar',             'Public\\BuscarController@index'],
    ['GET',  '/noticias',           'Public\\NoticiaController@index'],
    ['GET',  '/noticia/{slug}',     'Public\\NoticiaController@show'],
    ['GET',  '/mapa',               'Public\\MapaController@index'],
    ['GET',  '/sitemap.xml',        'Public\\HomeController@sitemap'],
    ['GET',  '/feed/rss.xml',       'Public\\FeedController@rss'],
    ['GET',  '/planes',              'Public\\PlanesController@index'],

    // ── Contacto ──────────────────────────────────────────────────
    ['GET',  '/contacto',           'Public\\ContactoController@index'],
    ['POST', '/contacto/enviar',    'Public\\ContactoController@send'],

    // ── Páginas legales ─────────────────────────────────────────
    ['GET',  '/terminos',           'Public\\PageController@terminos'],
    ['GET',  '/privacidad',         'Public\\PageController@privacidad'],
    ['GET',  '/cookies',            'Public\\PageController@cookies'],
    ['GET',  '/contenidos',         'Public\\PageController@contenidos'],
    ['GET',  '/derechos',           'Public\\DerechosController@index'],
    ['POST', '/derechos',           'Public\\DerechosController@store'],

    // ── Mis reseñas ─────────────────────────────────────────────
    ['GET',  '/mis-resenas',        'Public\\ReviewController@misResenas'],

    // ── Compartir ───────────────────────────────────────────────
    ['GET',  '/compartir/{tipo}/{slug}', 'Public\\ShareController@show'],

    // ── Auth ──────────────────────────────────────────────────
    ['GET',  '/admin',              'Admin\\AuthController@loginForm'],
    ['GET',  '/admin/login',        'Admin\\AuthController@loginForm'],
    ['POST', '/admin/login',        'Admin\\AuthController@login'],
    ['GET',  '/admin/logout',       'Admin\\AuthController@logout'],

    // ── Dashboard ─────────────────────────────────────────────
    ['GET',  '/admin/dashboard',    'Admin\\DashboardController@index', ['auth']],

    // ── Comercios CRUD ────────────────────────────────────────
    ['GET',  '/admin/comercios',                       'Admin\\ComercioAdminController@index',         ['auth']],
    ['GET',  '/admin/comercios/crear',                 'Admin\\ComercioAdminController@create',        ['auth']],
    ['POST', '/admin/comercios/store',                 'Admin\\ComercioAdminController@store',         ['auth']],
    ['GET',  '/admin/comercios/editar/{id}',           'Admin\\ComercioAdminController@edit',          ['auth']],
    ['POST', '/admin/comercios/update/{id}',           'Admin\\ComercioAdminController@update',        ['auth']],
    ['POST', '/admin/comercios/toggle/{id}',           'Admin\\ComercioAdminController@toggleActive',  ['auth']],
    ['POST', '/admin/comercios/eliminar/{id}',           'Admin\\ComercioAdminController@delete',        ['auth']],
    ['GET',  '/admin/comercios/{id}/galeria',          'Admin\\ComercioAdminController@gallery',       ['auth']],
    ['POST', '/admin/comercios/{id}/foto',             'Admin\\ComercioAdminController@storePhoto',    ['auth']],
    ['POST', '/admin/comercios/{id}/foto/eliminar',    'Admin\\ComercioAdminController@deletePhoto',   ['auth']],
    ['GET',  '/admin/comercios/{id}/horarios',         'Admin\\ComercioAdminController@horarios',      ['auth']],
    ['POST', '/admin/comercios/{id}/horarios',         'Admin\\ComercioAdminController@updateHorarios',['auth']],

    // ── Categorías CRUD ───────────────────────────────────────
    ['GET',  '/admin/categorias',              'Admin\\CategoriaAdminController@index',  ['auth']],
    ['GET',  '/admin/categorias/crear',        'Admin\\CategoriaAdminController@create', ['auth']],
    ['POST', '/admin/categorias/store',        'Admin\\CategoriaAdminController@store',  ['auth']],
    ['GET',  '/admin/categorias/editar/{id}',  'Admin\\CategoriaAdminController@edit',   ['auth']],
    ['POST', '/admin/categorias/update/{id}',  'Admin\\CategoriaAdminController@update', ['auth']],
    ['POST', '/admin/categorias/eliminar/{id}',  'Admin\\CategoriaAdminController@delete', ['auth']],

    // ── Fechas Especiales CRUD ────────────────────────────────
    ['GET',  '/admin/fechas',              'Admin\\FechaAdminController@index',  ['auth']],
    ['GET',  '/admin/fechas/crear',        'Admin\\FechaAdminController@create', ['auth']],
    ['POST', '/admin/fechas/store',        'Admin\\FechaAdminController@store',  ['auth']],
    ['GET',  '/admin/fechas/editar/{id}',  'Admin\\FechaAdminController@edit',   ['auth']],
    ['POST', '/admin/fechas/update/{id}',  'Admin\\FechaAdminController@update', ['auth']],
    ['POST', '/admin/fechas/eliminar/{id}',  'Admin\\FechaAdminController@delete', ['auth']],

    // ── Noticias CRUD ─────────────────────────────────────────
    ['GET',  '/admin/noticias',              'Admin\\NoticiaAdminController@index',        ['auth']],
    ['GET',  '/admin/noticias/crear',        'Admin\\NoticiaAdminController@create',       ['auth']],
    ['POST', '/admin/noticias/store',        'Admin\\NoticiaAdminController@store',        ['auth']],
    ['GET',  '/admin/noticias/editar/{id}',  'Admin\\NoticiaAdminController@edit',         ['auth']],
    ['POST', '/admin/noticias/update/{id}',  'Admin\\NoticiaAdminController@update',       ['auth']],
    ['POST', '/admin/noticias/toggle/{id}',  'Admin\\NoticiaAdminController@toggleActive', ['auth']],
    ['POST', '/admin/noticias/upload-imagen',  'Admin\\NoticiaAdminController@uploadImage',  ['auth']],
    ['POST', '/admin/noticias/eliminar/{id}',  'Admin\\NoticiaAdminController@delete',       ['auth']],

    // ── Banners CRUD ──────────────────────────────────────────
    ['GET',  '/admin/banners',              'Admin\\BannerAdminController@index',        ['auth']],
    ['GET',  '/admin/banners/crear',        'Admin\\BannerAdminController@create',       ['auth']],
    ['POST', '/admin/banners/store',        'Admin\\BannerAdminController@store',        ['auth']],
    ['GET',  '/admin/banners/editar/{id}',  'Admin\\BannerAdminController@edit',         ['auth']],
    ['POST', '/admin/banners/update/{id}',  'Admin\\BannerAdminController@update',       ['auth']],
    ['POST', '/admin/banners/toggle/{id}',  'Admin\\BannerAdminController@toggleActive', ['auth']],
    ['POST', '/admin/banners/eliminar/{id}',  'Admin\\BannerAdminController@delete',       ['auth']],
    ['POST', '/admin/banners/reset/{id}',   'Admin\\BannerAdminController@resetStats',   ['auth']],

    // ── Usuarios CRUD ─────────────────────────────────────────
    ['GET',  '/admin/usuarios',              'Admin\\UsuarioAdminController@index',        ['auth']],
    ['GET',  '/admin/usuarios/crear',        'Admin\\UsuarioAdminController@create',       ['auth']],
    ['POST', '/admin/usuarios/store',        'Admin\\UsuarioAdminController@store',        ['auth']],
    ['GET',  '/admin/usuarios/editar/{id}',  'Admin\\UsuarioAdminController@edit',         ['auth']],
    ['POST', '/admin/usuarios/update/{id}',  'Admin\\UsuarioAdminController@update',       ['auth']],
    ['POST', '/admin/usuarios/toggle/{id}',  'Admin\\UsuarioAdminController@toggleActive', ['auth']],
    ['POST', '/admin/usuarios/eliminar/{id}',  'Admin\\UsuarioAdminController@delete',       ['auth']],

// ── Planes ────────────────────────────────────────────────
    ['GET',  '/admin/planes',                'Admin\\PlanAdminController@index',      ['auth']],
    ['GET',  '/admin/planes/crear',          'Admin\\PlanAdminController@create',     ['auth']],
    ['POST', '/admin/planes/store',          'Admin\\PlanAdminController@store',      ['auth']],
    ['GET',  '/admin/planes/editar/{id}',    'Admin\\PlanAdminController@edit',       ['auth']],
    ['POST', '/admin/planes/update/{id}',    'Admin\\PlanAdminController@update',     ['auth']],
    ['POST', '/admin/planes/eliminar/{id}',  'Admin\\PlanAdminController@delete',     ['auth']],
    ['POST', '/admin/planes/update-plan',    'Admin\\PlanAdminController@updatePlan', ['auth']],
    ['POST', '/admin/planes/assign',         'Admin\\PlanAdminController@assignPlan', ['auth']],
    ['POST', '/admin/planes/validar',        'Admin\\PlanAdminController@validar',    ['auth']],
    ['POST', '/admin/planes/toggle-sello',   'Admin\\PlanAdminController@toggleSello',['auth']],

// ── Cambios pendientes de comerciantes ────────────────────
    ['GET',  '/admin/cambios-pendientes',              'Admin\\CambiosPendientesController@index',    ['auth']],
    ['GET',  '/admin/cambios-pendientes/{id}',         'Admin\\CambiosPendientesController@show',     ['auth']],
    ['POST', '/admin/cambios-pendientes/aprobar/{id}', 'Admin\\CambiosPendientesController@aprobar',  ['auth']],
    ['POST', '/admin/cambios-pendientes/rechazar/{id}','Admin\\CambiosPendientesController@rechazar', ['auth']],

    // ── Renovaciones de planes ──────────────────────────────
    ['GET',  '/admin/renovaciones',               'Admin\\RenovacionAdminController@index',    ['auth']],
    ['GET',  '/admin/renovaciones/ver/{id}',      'Admin\\RenovacionAdminController@show',     ['auth']],
    ['POST', '/admin/renovaciones/aprobar/{id}',  'Admin\\RenovacionAdminController@aprobar',  ['auth']],
    ['POST', '/admin/renovaciones/rechazar/{id}', 'Admin\\RenovacionAdminController@rechazar', ['auth']],

    // ── Reseñas ───────────────────────────────────────────────
    ['GET',  '/admin/resenas',                         'Admin\\ResenaAdminController@index',        ['auth']],
    ['GET',  '/admin/resenas/reportes',                'Admin\\ResenaAdminController@reportes',     ['auth']],
    ['GET',  '/admin/resenas/configuracion',           'Admin\\ResenaConfigController@index',       ['auth']],
    ['POST', '/admin/resenas/configuracion',           'Admin\\ResenaConfigController@update',      ['auth']],
    ['GET',  '/admin/resenas/{id}',                    'Admin\\ResenaAdminController@show',         ['auth']],
    ['POST', '/admin/resenas/aprobar/{id}',            'Admin\\ResenaAdminController@aprobar',      ['auth']],
    ['POST', '/admin/resenas/rechazar/{id}',           'Admin\\ResenaAdminController@rechazar',     ['auth']],
    ['POST', '/admin/resenas/responder/{id}',          'Admin\\ResenaAdminController@responder',    ['auth']],
    ['POST', '/admin/resenas/eliminar/{id}',           'Admin\\ResenaAdminController@eliminar',     ['auth']],
    ['POST', '/admin/resenas/bulk',                    'Admin\\ResenaAdminController@bulk',         ['auth']],
    ['POST', '/admin/resenas/reportes/eliminar/{id}',  'Admin\\ResenaAdminController@deleteReport', ['auth']],

    // ── Reportes ──────────────────────────────────────────────
    ['GET',  '/admin/reportes',             'Admin\\ReporteAdminController@index',      ['auth']],
    ['GET',  '/admin/reportes/visitas',     'Admin\\ReporteAdminController@visitas',    ['auth']],
    ['GET',  '/admin/reportes/comercios',   'Admin\\ReporteAdminController@comercios',  ['auth']],
    ['GET',  '/admin/reportes/categorias',  'Admin\\ReporteAdminController@categorias', ['auth']],
    ['GET',  '/admin/reportes/fechas',      'Admin\\ReporteAdminController@fechas',     ['auth']],
    ['GET',  '/admin/reportes/banners',     'Admin\\ReporteAdminController@banners',    ['auth']],
    ['GET',  '/admin/reportes/export',      'Admin\\ReporteAdminController@exportCsv',  ['auth']],

    // ── SEO ───────────────────────────────────────────────────
    ['GET',  '/admin/seo',                          'Admin\\SeoAdminController@index',          ['auth']],
    ['POST', '/admin/seo/config',                   'Admin\\SeoAdminController@saveConfig',     ['auth']],
    ['POST', '/admin/seo/metatags',                 'Admin\\SeoAdminController@saveMetaTags',   ['auth']],
    ['POST', '/admin/seo/schema',                   'Admin\\SeoAdminController@saveSchema',     ['auth']],
    ['POST', '/admin/seo/redirects',                'Admin\\SeoAdminController@createRedirect', ['auth']],
    ['POST', '/admin/seo/redirects/eliminar/{id}',  'Admin\\SeoAdminController@deleteRedirect', ['auth']],
    ['POST', '/admin/seo/redirects/toggle/{id}',    'Admin\\SeoAdminController@toggleRedirect', ['auth']],
    ['POST', '/admin/seo/sitemap',                  'Admin\\SeoAdminController@generateSitemap',['auth']],

    // ── Contacto (admin) ────────────────────────────────────
    ['GET',  '/admin/contacto',        'Admin\\ContactoAdminController@index',   ['auth']],

    // ── Correos (admin) ─────────────────────────────────────
    ['GET',  '/admin/correos/enviar',  'Admin\\CorreoAdminController@enviar',    ['auth']],
    ['POST', '/admin/correos/enviar',  'Admin\\CorreoAdminController@send',      ['auth']],
    ['POST', '/admin/correos/preview', 'Admin\\CorreoAdminController@preview',   ['auth']],

    // ── Notificaciones ──────────────────────────────────────
    ['GET',  '/admin/notificaciones',                'Admin\\NotificacionAdminController@index',      ['auth']],
    ['POST', '/admin/notificaciones/config',         'Admin\\NotificacionAdminController@saveConfig', ['auth']],
    ['POST', '/admin/notificaciones/test',           'Admin\\NotificacionAdminController@test',       ['auth']],
    ['GET',  '/admin/notificaciones/log',            'Admin\\NotificacionAdminController@logview',        ['auth']],
    ['POST', '/admin/notificaciones/log/limpiar',    'Admin\\NotificacionAdminController@cleanLog',   ['auth']],

    // ── Compartidos ─────────────────────────────────────────
    ['GET',  '/admin/share',  'Admin\\ShareAdminController@index', ['auth']],

    // ── Redes Sociales ──────────────────────────────────────
    ['GET',  '/admin/redes-sociales',         'Admin\\RedesAdminController@index',  ['auth']],
    ['POST', '/admin/redes-sociales/update',  'Admin\\RedesAdminController@update', ['auth']],

    // ── Apariencia ──────────────────────────────────────────
    ['GET',  '/admin/apariencia',             'Admin\\AparienciaAdminController@index',      ['auth']],
    ['POST', '/admin/apariencia/update',      'Admin\\AparienciaAdminController@update',     ['auth']],
    ['POST', '/admin/apariencia/preset',      'Admin\\AparienciaAdminController@preset',     ['auth']],
    ['POST', '/admin/apariencia/auto-shades', 'Admin\\AparienciaAdminController@autoShades', ['auth']],

    // ── Mantenimiento ─────────────────────────────────────────
    ['GET',  '/admin/mantenimiento',                       'Admin\\MantenimientoController@index',        ['auth']],

    // Backups
    ['GET',  '/admin/mantenimiento/backups',               'Admin\\BackupController@listBackups',         ['auth']],
    ['POST', '/admin/mantenimiento/backup/db',             'Admin\\BackupController@backupDb',            ['auth']],
    ['POST', '/admin/mantenimiento/backup/archivos',       'Admin\\BackupController@backupFiles',         ['auth']],
    ['POST', '/admin/mantenimiento/backup/completo',       'Admin\\BackupController@backupFull',          ['auth']],
    ['GET',  '/admin/mantenimiento/backup/descargar/{file}', 'Admin\\BackupController@downloadBackup',    ['auth']],
    ['POST', '/admin/mantenimiento/backup/eliminar/{file}',  'Admin\\BackupController@deleteBackup',      ['auth']],

    // Google Drive backup operations
    ['POST', '/admin/mantenimiento/backup/drive/subir/{file}',      'Admin\\BackupController@uploadToDrive',       ['auth']],
    ['POST', '/admin/mantenimiento/backup/drive/eliminar/{fileId}', 'Admin\\BackupController@deleteDriveBackup',   ['auth']],
    ['POST', '/admin/mantenimiento/backup/drive/test',              'Admin\\BackupController@testDriveConnection', ['auth']],

    // Explorador de archivos
    ['GET',  '/admin/mantenimiento/archivos',              'Admin\\FileExplorerController@browse',        ['auth']],
    ['GET',  '/admin/mantenimiento/archivos/ver',          'Admin\\FileExplorerController@viewFile',      ['auth']],
    ['GET',  '/admin/mantenimiento/archivos/descargar',    'Admin\\FileExplorerController@downloadFile',  ['auth']],
    ['POST', '/admin/mantenimiento/archivos/subir',        'Admin\\FileExplorerController@uploadFile',    ['auth']],
    ['POST', '/admin/mantenimiento/archivos/crear-carpeta','Admin\\FileExplorerController@createFolder',  ['auth']],
    ['POST', '/admin/mantenimiento/archivos/renombrar',    'Admin\\FileExplorerController@renameFile',    ['auth']],
    ['POST', '/admin/mantenimiento/archivos/eliminar',     'Admin\\FileExplorerController@deleteFile',    ['auth']],

    // Salud del sistema
    ['GET',  '/admin/mantenimiento/salud',                 'Admin\\HealthController@index',               ['auth']],
    ['POST', '/admin/mantenimiento/salud/refresh',         'Admin\\HealthController@refresh',             ['auth']],

    // Logs de actividad
    ['GET',  '/admin/mantenimiento/logs',                  'Admin\\LogsController@index',                 ['auth']],
    ['GET',  '/admin/mantenimiento/logs/exportar',         'Admin\\LogsController@export',                ['auth']],
    ['POST', '/admin/mantenimiento/logs/limpiar',          'Admin\\LogsController@clean',                 ['auth']],
    ['GET',  '/admin/mantenimiento/logs/{id}',             'Admin\\LogsController@show',                  ['auth']],

    // Herramientas
    ['GET',  '/admin/mantenimiento/herramientas',          'Admin\\ToolsController@index',                ['auth']],
    ['POST', '/admin/mantenimiento/sitemap/regenerar',     'Admin\\ToolsController@regenerateSitemap',    ['auth']],
    ['POST', '/admin/mantenimiento/cache/limpiar',         'Admin\\ToolsController@clearCache',           ['auth']],
    ['POST', '/admin/mantenimiento/mantenimiento/toggle',  'Admin\\ToolsController@toggleMaintenance',    ['auth']],
    ['GET',  '/admin/mantenimiento/phpinfo',               'Admin\\ToolsController@phpinfo',              ['auth']],
    ['POST', '/admin/mantenimiento/sesiones/limpiar',      'Admin\\ToolsController@clearSessions',        ['auth']],
    ['POST', '/admin/mantenimiento/tablas/optimizar',      'Admin\\ToolsController@optimizeTables',       ['auth']],
    ['POST', '/admin/mantenimiento/imagenes/verificar',    'Admin\\ToolsController@checkImages',          ['auth']],
    ['POST', '/admin/mantenimiento/stats/recalcular',      'Admin\\ToolsController@recalcStats',          ['auth']],

    // Configuración
    ['GET',  '/admin/mantenimiento/configuracion',         'Admin\\ConfigController@index',               ['auth']],
    ['POST', '/admin/mantenimiento/configuracion',         'Admin\\ConfigController@update',              ['auth']],

    // ── Perfil ───────────────────────────────────────────────
    ['GET',  '/admin/perfil',            'Admin\\PerfilController@index',          ['auth']],
    ['POST', '/admin/perfil/password',   'Admin\\PerfilController@updatePassword', ['auth']],

    // ── Sitios (superadmin) ─────────────────────────────────
    ['GET',  '/admin/sitios',              'Admin\\SitioAdminController@index',        ['auth']],
    ['GET',  '/admin/sitios/crear',        'Admin\\SitioAdminController@create',       ['auth']],
    ['POST', '/admin/sitios/store',        'Admin\\SitioAdminController@store',        ['auth']],
    ['GET',  '/admin/sitios/editar/{id}',  'Admin\\SitioAdminController@edit',         ['auth']],
    ['POST', '/admin/sitios/update/{id}',  'Admin\\SitioAdminController@update',       ['auth']],
    ['POST', '/admin/sitios/toggle/{id}',  'Admin\\SitioAdminController@toggleActive', ['auth']],
    ['POST', '/admin/sitios/cambiar',      'Admin\\SitioAdminController@switchSite',   ['auth']],

    // ── API ───────────────────────────────────────────────────
    ['POST', '/api/reviews/create',     'Api\\ReviewApiController@create'],
    ['GET',  '/api/reviews/list/{id}',  'Api\\ReviewApiController@list'],
    ['POST', '/api/reviews/report',     'Api\\ReviewApiController@report'],
    ['POST', '/api/track',              'Api\\TrackApiController@track'],
    ['POST', '/api/banner-track',       'Api\\BannerApiController@track'],
    ['POST', '/api/share-track',        'Api\\ShareApiController@track'],
    ['POST', '/api/consentimiento',     'Api\\ConsentimientoApiController@store'],

// ── Registro público de comercios ─────────────────────────
    ['GET',  '/registrar-comercio',         'Public\\RegistroComercioController@index',       []],
    ['POST', '/registrar-comercio/cuenta',  'Public\\RegistroComercioController@storeCuenta', []],
    ['GET',  '/registrar-comercio/datos',   'Public\\RegistroComercioController@datos',       []],
    ['POST', '/registrar-comercio/store',   'Public\\RegistroComercioController@storeDatos',  []],
    ['GET',  '/registrar-comercio/gracias', 'Public\\RegistroComercioController@gracias',     []],

    // ── Panel del comerciante ─────────────────────────────────
    ['GET',  '/mi-comercio',                'Public\\ComercianteController@dashboard',    []],
    ['GET',  '/mi-comercio/login',          'Public\\ComercianteController@loginForm',    []],
    ['POST', '/mi-comercio/login',          'Public\\ComercianteController@login',        []],
    ['GET',  '/mi-comercio/logout',         'Public\\ComercianteController@logout',       []],
    ['GET',  '/mi-comercio/editar',             'Public\\ComercianteController@editar',            []],
    ['POST', '/mi-comercio/guardar',            'Public\\ComercianteController@guardar',           []],
    ['POST', '/mi-comercio/solicitar-renovacion', 'Public\\ComercianteController@solicitarRenovacion', []],
    ['GET',  '/mi-comercio/olvide-contrasena',  'Public\\ComercianteController@forgotPasswordForm', []],
    ['POST', '/mi-comercio/olvide-contrasena',  'Public\\ComercianteController@sendResetLink',      []],
    ['GET',  '/mi-comercio/reset/{token}',      'Public\\ComercianteController@resetPasswordForm',  []],
    ['POST', '/mi-comercio/reset/{token}',      'Public\\ComercianteController@resetPassword',      []],
];