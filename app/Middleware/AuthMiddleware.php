<?php
namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

/**
 * Verificar que el usuario tiene sesión admin activa
 */
class AuthMiddleware extends Middleware
{
    public function handle(Request $request): void
    {
        // Sin sesión → login
        if (empty($_SESSION['admin']['id'])) {
            $_SESSION['flash']['error'] = 'Debes iniciar sesión para acceder';
            Response::redirect('/admin/login');
        }

        // Sesión expirada
        if (isset($_SESSION['admin_expires']) && time() > $_SESSION['admin_expires']) {
            unset($_SESSION['admin'], $_SESSION['admin_expires']);
            $_SESSION['flash']['error'] = 'Tu sesión ha expirado';
            Response::redirect('/admin/login');
        }

        // Renovar expiración
        $_SESSION['admin_expires'] = time() + SESSION_LIFETIME;

        // Verificar permisos del módulo actual
        $uri = $request->uri();
        $segments = explode('/', trim($uri, '/'));
        $module = $segments[1] ?? 'dashboard';

        $permission = new \App\Services\Permission();
        if (!$permission->can($_SESSION['admin']['rol'], $module)) {
            Response::error(403);
        }
    }
}
