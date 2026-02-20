<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Comercio;
use App\Models\Categoria;
use App\Services\VisitTracker;

/**
 * Mapa interactivo de comercios
 */
class MapaController extends Controller
{
    /**
     * GET /mapa
     */
    public function index(): void
    {
        $comercios  = Comercio::getParaMapa();
        $categorias = Categoria::getAll(true);

        VisitTracker::track(null, '/mapa', 'mapa');

        $this->render('public/mapa', [
            'title'       => 'Mapa de Comercios en Purranque · ' . SITE_NAME,
            'description' => 'Mapa interactivo con todos los comercios de Purranque, Chile. Encuentra ubicación, contacto y horarios.',
            'comercios'   => $comercios,
            'categorias'  => $categorias,
            'centerLat'   => CITY_LAT,
            'centerLng'   => CITY_LNG,
            'zoom'        => CITY_ZOOM,
        ]);
    }
}
