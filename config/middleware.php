<?php
/**
 * Registro de middleware disponibles
 * Clave => Clase
 */

return [
    'auth'        => App\Middleware\AuthMiddleware::class,
    'csrf'        => App\Middleware\CsrfMiddleware::class,
    'permission'  => App\Middleware\PermissionMiddleware::class,
    'maintenance' => App\Middleware\MaintenanceMiddleware::class,
    'redirect'    => App\Middleware\RedirectMiddleware::class,
];
