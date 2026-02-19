<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Share;

/**
 * API para tracking de compartidos
 */
class ShareApiController extends Controller
{
    /**
     * POST /api/share-track — Registrar compartido
     */
    public function track(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['url'])) {
            $this->json(['error' => 'Datos requeridos: url'], 400);
            return;
        }

        $red = $data['red'] ?? 'desconocida';
        $comercioId = !empty($data['comercio_id']) ? (int) $data['comercio_id'] : null;

        Share::registrar($comercioId, $data['url'], $red);

        $this->json(['success' => true]);
    }
}
