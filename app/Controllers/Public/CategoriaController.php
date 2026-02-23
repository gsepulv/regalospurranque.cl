<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Categoria;
use App\Models\Comercio;
use App\Models\Banner;
use App\Services\VisitTracker;
use App\Services\Seo;

/**
 * Comercios por categoria
 */
class CategoriaController extends Controller
{
    /**
     * GET /categorias
     */
    public function index(): void
    {
        $categorias = Categoria::getWithComerciosCount();

        VisitTracker::track(null, '/categorias', 'categorias');

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Categorias'],
        ];

        $this->render('public/categorias', [
            'title'       => 'Categorías de Comercios en Purranque · ' . SITE_NAME,
            'description' => 'Explora todas las categorías de comercios y servicios en Purranque, Chile. Gastronomía, flores, regalos y más.',
            'og_image'    => asset('img/og/categoria-default.jpg'),
            'categorias'  => $categorias,
            'breadcrumbs' => $breadcrumbs,
            'schemas'     => [Seo::schemaBreadcrumbs($breadcrumbs)],
        ]);
    }

    /**
     * GET /categoria/{slug}
     */
    public function show(string $slug): void
    {
        $categoria = Categoria::getBySlug($slug);

        if (!$categoria) {
            Response::error(404);
            return;
        }

        $page    = max(1, (int) $this->request->get('page', 1));
        $total   = Comercio::countByCategoria($categoria['id']);
        $totalPages = max(1, (int) ceil($total / PER_PAGE));
        $offset  = ($page - 1) * PER_PAGE;

        $comercios  = Comercio::getByCategoria($categoria['id'], PER_PAGE, $offset);
        $categorias = Categoria::getWithComerciosCount();
        $banners    = Banner::getByTipo('sidebar');

        VisitTracker::track(null, "/categoria/{$slug}", 'categoria');

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Categorias', 'url' => '/categorias'],
            ['label' => $categoria['nombre']],
        ];

        // noindex para categorías sin comercios (thin content)
        $noindex = $total === 0;
        $seoDesc = $categoria['descripcion']
            ?: ($total > 0
                ? "Los mejores comercios de {$categoria['nombre']} en Purranque, Chile. Encuentra {$total} opciones con ubicación, contacto y reseñas."
                : "Próximamente: comercios de {$categoria['nombre']} en Purranque, Chile. Directorio en crecimiento.");

        $this->render('public/categoria', [
            'title'       => $categoria['nombre'] . ' en Purranque · Comercios y Regalos · ' . SITE_NAME,
            'description' => $seoDesc,
            'og_image'    => asset('img/og/categoria-default.jpg'),
            'noindex'     => $noindex,
            'categoria'   => $categoria,
            'comercios'   => $comercios,
            'categorias'  => $categorias,
            'banners'     => $banners,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'baseUrl'     => "/categoria/{$slug}",
            'breadcrumbs' => $breadcrumbs,
            'schemas'     => [
                Seo::schemaItemList($comercios, $categoria['nombre'] . ' en Purranque'),
                Seo::schemaBreadcrumbs($breadcrumbs),
            ],
        ]);
    }
}
