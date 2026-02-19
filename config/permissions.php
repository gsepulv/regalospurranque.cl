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
