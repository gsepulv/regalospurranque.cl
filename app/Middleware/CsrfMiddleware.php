<?php
namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

/**
 * Validar token CSRF en peticiones POST
 */
class CsrfMiddleware extends Middleware
{
    public function handle(Request $request): void
    {
        if ($request->method() !== 'POST') {
            return;
        }

        $token = $request->post('_csrf')
              ?? $request->header('X-CSRF-TOKEN');

        if (!$token || empty($_SESSION['csrf_token'])) {
            Response::error(403);
        }

        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            Response::error(403);
        }

        // Regenerar token después de validar (single-use)
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}
