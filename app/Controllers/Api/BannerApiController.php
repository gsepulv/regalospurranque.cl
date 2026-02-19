<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Banner;

/**
 * API para tracking de banners (clics e impresiones)
 */
class BannerApiController extends Controller
{
    /**
     * POST /api/banner-track
     */
    public function track(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['banner_id'])) {
            $this->json(['error' => 'banner_id requerido'], 400);
            return;
        }

        $bannerId = (int) $data['banner_id'];
        $tipo = $data['tipo'] ?? 'impression';

        if ($tipo === 'click') {
            Banner::incrementClicks($bannerId);
        } else {
            Banner::incrementImpresiones($bannerId);
        }

        $this->json(['success' => true]);
    }
}
