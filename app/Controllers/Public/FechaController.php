<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Response;
use App\Models\FechaEspecial;
use App\Models\Comercio;
use App\Models\Banner;
use App\Services\VisitTracker;
use App\Services\Seo;

/**
 * Comercios por fecha especial
 */
class FechaController extends Controller
{
    /**
     * GET /celebraciones
     */
    public function index(): void
    {
        $fechasPersonales  = FechaEspecial::getAllByTipo('personal');
        $fechasCalendario  = FechaEspecial::getAllByTipo('calendario');
        $fechasComerciales = FechaEspecial::getAllByTipo('comercial');

        VisitTracker::track(null, '/celebraciones', 'celebraciones');

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Celebraciones'],
        ];

        $this->render('public/celebraciones', [
            'title'             => 'Celebraciones y Fechas Especiales - ' . SITE_NAME,
            'description'       => 'Descubre las mejores ofertas y comercios para cada celebracion y fecha especial en Purranque',
            'og_image'          => asset('img/og/fecha-default.jpg'),
            'fechasPersonales'  => $fechasPersonales,
            'fechasCalendario'  => $fechasCalendario,
            'fechasComerciales' => $fechasComerciales,
            'breadcrumbs'       => $breadcrumbs,
            'schemas'           => [Seo::schemaBreadcrumbs($breadcrumbs)],
        ]);
    }

    /**
     * GET /fecha/{slug}
     */
    public function show(string $slug): void
    {
        $fecha = FechaEspecial::getBySlug($slug);

        if (!$fecha) {
            Response::error(404);
            return;
        }

        $page       = max(1, (int) $this->request->get('page', 1));
        $total      = Comercio::countByFecha($fecha['id']);
        $totalPages = max(1, (int) ceil($total / PER_PAGE));
        $offset     = ($page - 1) * PER_PAGE;

        $comercios = Comercio::getByFecha($fecha['id'], PER_PAGE, $offset);
        $banners   = Banner::getByTipo('sidebar');

        VisitTracker::track(null, "/fecha/{$slug}", 'fecha');

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Celebraciones', 'url' => '/celebraciones'],
            ['label' => $fecha['nombre']],
        ];

        $this->render('public/fecha', [
            'title'       => $fecha['nombre'] . ' - ' . SITE_NAME,
            'description' => $fecha['descripcion'] ?: "Descubre las ofertas y comercios para {$fecha['nombre']} en Purranque",
            'og_image'    => asset('img/og/fecha-default.jpg'),
            'fecha'       => $fecha,
            'comercios'   => $comercios,
            'banners'     => $banners,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'baseUrl'     => "/fecha/{$slug}",
            'breadcrumbs' => $breadcrumbs,
            'schemas'     => [
                Seo::schemaEvent($fecha),
                Seo::schemaBreadcrumbs($breadcrumbs),
            ],
        ]);
    }
}
