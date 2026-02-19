<?php
namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;

/**
 * Verificar modo mantenimiento
 * Si está activo, muestra página de mantenimiento para rutas públicas
 * Las rutas /admin y /api se permiten siempre
 */
class MaintenanceMiddleware extends Middleware
{
    public function handle(Request $request): void
    {
        $uri = $request->uri();

        // Permitir acceso a admin y API siempre
        if (str_starts_with($uri, '/admin') || str_starts_with($uri, '/api')) {
            return;
        }

        // Verificar si existe el flag de mantenimiento
        $flagFile = BASE_PATH . '/storage/cache/maintenance.flag';
        if (!file_exists($flagFile)) {
            return;
        }

        // Mostrar página de mantenimiento
        http_response_code(503);
        header('Retry-After: 3600');

        $maintPage = BASE_PATH . '/mantenimiento.html';
        if (file_exists($maintPage)) {
            readfile($maintPage);
        } else {
            echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Mantenimiento</title>';
            echo '<style>body{font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#f8fafc;color:#1e293b;text-align:center}';
            echo 'h1{color:#2563eb;font-size:2rem}p{color:#64748b;font-size:1.1rem}</style></head>';
            echo '<body><div><h1>Sitio en mantenimiento</h1><p>Estamos realizando mejoras. Volvemos pronto.</p></div></body></html>';
        }
        exit;
    }
}
