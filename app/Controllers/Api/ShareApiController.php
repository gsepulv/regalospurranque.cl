<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Comercio;
use App\Models\Share;

/**
 * API para tracking de compartidos
 */
class ShareApiController extends Controller
{
    /**
     * POST /api/share-track — Registrar compartido
     * JS envía: { red, slug, tipo } o legacy: { red, url, comercio_id }
     */
    public function track(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $red  = $data['red'] ?? 'desconocida';
        $slug = $data['slug'] ?? '';
        $tipo = $data['tipo'] ?? '';
        $url  = $data['url'] ?? '';

        // Construir URL desde slug+tipo si no viene url directa
        if (empty($url) && !empty($slug) && !empty($tipo)) {
            $url = '/' . $tipo . '/' . $slug;
        }

        if (empty($url) && empty($slug)) {
            $this->json(['error' => 'Datos insuficientes'], 400);
            return;
        }

        // Resolver comercio_id desde slug si el tipo es comercio
        $comercioId = !empty($data['comercio_id']) ? (int) $data['comercio_id'] : null;
        if (!$comercioId && $tipo === 'comercio' && !empty($slug)) {
            $comercio = Comercio::getBySlug($slug);
            $comercioId = $comercio ? (int) $comercio['id'] : null;
        }

        Share::registrar($comercioId, $url ?: ('/' . $tipo . '/' . $slug), $red);

        $this->json(['success' => true]);
    }
}
