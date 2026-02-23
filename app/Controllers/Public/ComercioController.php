<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Comercio;
use App\Models\Categoria;
use App\Models\Resena;
use App\Models\Banner;
use App\Services\VisitTracker;
use App\Services\Seo;

/**
 * Detalle de un comercio
 */
class ComercioController extends Controller
{
    /**
     * GET /comercios
     */
    public function index(): void
    {
        $filters = [];
        $page       = max(1, (int) $this->request->get('page', 1));
        $total      = Comercio::countSearch($filters);
        $totalPages = max(1, (int) ceil($total / PER_PAGE));
        $offset     = ($page - 1) * PER_PAGE;

        $comercios  = Comercio::search($filters, PER_PAGE, $offset);
        $categorias = Categoria::getWithComerciosCount();
        $banners    = Banner::getByTipo('sidebar');

        VisitTracker::track(null, '/comercios', 'comercios');

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Comercios'],
        ];

        $this->render('public/comercios', [
            'title'       => 'Comercios en Purranque · Directorio Comercial · ' . SITE_NAME,
            'description' => 'Directorio completo de comercios y servicios en Purranque, Chile. Encuentra tiendas, restaurantes y más con contacto y ubicación.',
            'og_image'    => asset('img/og/comercio-default.jpg'),
            'comercios'   => $comercios,
            'categorias'  => $categorias,
            'banners'     => $banners,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'baseUrl'     => '/comercios',
            'breadcrumbs' => $breadcrumbs,
            'schemas'     => [
                Seo::schemaItemList($comercios, 'Comercios en Purranque'),
                Seo::schemaBreadcrumbs($breadcrumbs),
            ],
        ]);
    }

    /**
     * GET /comercio/{slug}
     */
    public function show(string $slug): void
    {
        $comercio = Comercio::getBySlug($slug);

        if (!$comercio) {
            Response::error(404);
            return;
        }

        $id = (int) $comercio['id'];

        // Datos complementarios
        $fotos       = Comercio::getFotos($id);
        $horarios    = Comercio::getHorarios($id);
        $resenas     = Resena::getByComercio($id, 'aprobada', 10, 0);
        $distribucion = Resena::getDistribucion($id);
        $relacionados = Comercio::getRelacionados($id, 4);
        $banners     = Banner::getByTipo('sidebar');

        // Tracking
        Comercio::incrementVisitas($id);
        VisitTracker::track($id, "/comercio/{$slug}", 'comercio');

        // SEO
        $catPrincipal = '';
        if (!empty($comercio['categorias'])) {
            foreach ($comercio['categorias'] as $cat) {
                $catPrincipal = $cat['nombre'];
                if (!empty($cat['es_principal'])) break;
            }
        }
        $titleBase = $comercio['nombre'] . ($catPrincipal ? ' · ' . $catPrincipal : '') . ' en Purranque';
        if (mb_strlen($titleBase) > 55) {
            $titleBase = mb_substr($titleBase, 0, 55);
            $lastSpace = mb_strrpos($titleBase, ' ');
            if ($lastSpace) $titleBase = mb_substr($titleBase, 0, $lastSpace);
        }
        $title = $comercio['seo_titulo'] ?: $titleBase . ' · ' . SITE_NAME;
        $description = $comercio['seo_descripcion'] ?: mb_substr($comercio['nombre'] . ': ' . ($comercio['descripcion'] ?? ''), 0, 120) . '. ' . ($catPrincipal ? $catPrincipal . ' en Purranque.' : 'Comercio en Purranque.');
        $ogImage = $comercio['portada'] ? asset('img/portadas/' . $comercio['portada']) : null;

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Comercios', 'url' => '/comercios'],
            ['label' => $comercio['nombre']],
        ];

        $this->render('public/comercio', [
            'title'         => $title,
            'description'   => $description,
            'keywords'      => $comercio['seo_keywords'] ?? '',
            'og_image'      => $ogImage,
            'og_type'       => 'business.business',
            'comercio'      => $comercio,
            'fotos'         => $fotos,
            'horarios'      => $horarios,
            'resenas'       => $resenas,
            'distribucion'  => $distribucion,
            'relacionados'  => $relacionados,
            'banners'       => $banners,
            'breadcrumbs'   => $breadcrumbs,
            'schemas'       => [
                Seo::schemaLocalBusiness($comercio),
                Seo::schemaBreadcrumbs($breadcrumbs),
            ],
        ]);
    }
}
