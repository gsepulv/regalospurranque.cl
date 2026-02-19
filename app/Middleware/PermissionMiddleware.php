<?php
namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Services\Permission;

/**
 * Verificar permisos ACL del usuario para el módulo actual
 * Auto-detecta el módulo desde la URI: /admin/{modulo}/...
 */
class PermissionMiddleware extends Middleware
{
    public function handle(Request $request): void
    {
        $admin = $_SESSION['admin'] ?? null;

        // Sin sesión, AuthMiddleware ya maneja esto
        if (!$admin) {
            return;
        }

        // Extraer módulo de la URI
        $uri = $request->uri();
        $segments = explode('/', trim($uri, '/'));
        $module = $segments[1] ?? 'dashboard';

        $permission = new Permission();
        if (!$permission->can($admin['rol'], $module)) {
            Response::error(403);
        }
    }
}
