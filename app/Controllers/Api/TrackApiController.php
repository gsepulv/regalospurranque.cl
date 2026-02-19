<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Comercio;
use App\Services\VisitTracker;

/**
 * API para tracking de eventos (WhatsApp, visitas)
 */
class TrackApiController extends Controller
{
    /**
     * POST /api/track
     */
    public function track(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['tipo'])) {
            $this->json(['error' => 'Tipo requerido'], 400);
            return;
        }

        $tipo = $data['tipo'];
        $comercioId = !empty($data['comercio_id']) ? (int) $data['comercio_id'] : null;

        // Incrementar contador de WhatsApp si aplica
        if ($tipo === 'whatsapp' && $comercioId) {
            Comercio::incrementWhatsappClicks($comercioId);
        }

        // Registrar en visitas_log
        VisitTracker::track(
            $comercioId,
            $data['pagina'] ?? $_SERVER['HTTP_REFERER'] ?? '',
            $tipo
        );

        $this->json(['success' => true]);
    }
}
