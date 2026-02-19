<?php
namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Database;

/**
 * Verificar tabla seo_redirects antes de ruteo
 * Si la URL actual coincide con una redirección activa, redirige
 */
class RedirectMiddleware extends Middleware
{
    public function handle(Request $request): void
    {
        $uri = $request->uri();

        // No procesar admin ni API
        if (str_starts_with($uri, '/admin') || str_starts_with($uri, '/api')) {
            return;
        }

        try {
            $db = Database::getInstance();
            $redirect = $db->fetch(
                "SELECT id, url_destino, tipo FROM seo_redirects WHERE url_origen = ? AND activo = 1 LIMIT 1",
                [$uri]
            );

            if ($redirect) {
                // Incrementar hits
                $db->execute(
                    "UPDATE seo_redirects SET hits = hits + 1 WHERE id = ?",
                    [$redirect['id']]
                );

                $destino = $redirect['url_destino'];
                if (!str_starts_with($destino, 'http')) {
                    $destino = SITE_URL . $destino;
                }

                http_response_code((int) $redirect['tipo']);
                header('Location: ' . $destino);
                exit;
            }
        } catch (\Throwable $e) {
            // Si hay error en la consulta, no interrumpir el flujo normal
            error_log('RedirectMiddleware error: ' . $e->getMessage());
        }
    }
}
