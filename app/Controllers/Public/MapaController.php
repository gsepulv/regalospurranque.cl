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
            'title'       => 'Mapa de Comercios - ' . SITE_NAME,
            'description' => 'Encuentra los comercios de ' . CITY_NAME . ' en el mapa interactivo',
            'comercios'   => $comercios,
            'categorias'  => $categorias,
            'centerLat'   => CITY_LAT,
            'centerLng'   => CITY_LNG,
            'zoom'        => CITY_ZOOM,
        ]);
    }
}
