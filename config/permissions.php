<?php
/**
 * ACL: roles → módulos permitidos
 * '*' = acceso total
 */

return [
    'superadmin'  => ['*'],
    'admin'       => ['*'],
    'editor'      => [
        'dashboard',
        'comercios',
        'categorias',
        'fechas',
        'noticias',
        'banners',
        'reportes',
        'share',
        'resenas',
        'planes',
        'contacto',
        'correos',
        'notificaciones',
        'redes',
        'apariencia',
        'perfil',
    ],
    'comerciante' => [
        'dashboard',
        'perfil',
    ],
];
