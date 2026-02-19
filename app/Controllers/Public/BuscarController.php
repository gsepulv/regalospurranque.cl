<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Comercio;
use App\Models\Categoria;
use App\Models\FechaEspecial;
use App\Models\Banner;
use App\Services\VisitTracker;

/**
 * Busqueda y listado general de comercios
 */
class BuscarController extends Controller
{
    /**
     * GET /buscar
     */
    public function index(): void
    {
        $filters = [
            'query'        => trim($this->request->get('q', '')),
            'categoria_id' => $this->request->get('categoria'),
            'fecha_id'     => $this->request->get('fecha'),
            'plan'         => $this->request->get('plan'),
            'destacado'    => $this->request->get('destacado'),
        ];

        $page       = max(1, (int) $this->request->get('page', 1));
        $total      = Comercio::countSearch($filters);
        $totalPages = max(1, (int) ceil($total / PER_PAGE));
        $offset     = ($page - 1) * PER_PAGE;

        $comercios  = Comercio::search($filters, PER_PAGE, $offset);
        $categorias = Categoria::getAll(true);
        $fechas     = FechaEspecial::getActivas();
        $banners    = Banner::getByTipo('entre_comercios');

        VisitTracker::track(null, '/buscar' . ($filters['query'] ? '?q=' . $filters['query'] : ''), 'buscar');

        // Preservar filtros en la paginacion
        $queryParams = array_filter([
            'q'         => $filters['query'],
            'categoria' => $filters['categoria_id'],
            'fecha'     => $filters['fecha_id'],
            'plan'      => $filters['plan'],
            'destacado' => $filters['destacado'],
        ]);

        $hasFilters = !empty($filters['query']) || !empty($filters['categoria_id']) ||
                      !empty($filters['fecha_id']) || !empty($filters['plan']) || !empty($filters['destacado']);

        $this->render('public/buscar', [
            'title'       => 'Buscar Comercios - ' . SITE_NAME,
            'description' => 'Busca y encuentra los mejores comercios y servicios en Purranque',
            'noindex'     => $hasFilters,
            'comercios'   => $comercios,
            'categorias'  => $categorias,
            'fechas'      => $fechas,
            'banners'     => $banners,
            'filters'     => $filters,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'baseUrl'     => '/buscar',
            'queryParams' => $queryParams,
        ]);
    }
}
