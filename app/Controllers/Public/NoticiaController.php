<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Noticia;
use App\Models\Banner;
use App\Services\VisitTracker;
use App\Services\Seo;

/**
 * Noticias publicas
 */
class NoticiaController extends Controller
{
    /**
     * GET /noticias
     */
    public function index(): void
    {
        $page       = max(1, (int) $this->request->get('page', 1));
        $total      = Noticia::countAll();
        $totalPages = max(1, (int) ceil($total / PER_PAGE));
        $offset     = ($page - 1) * PER_PAGE;

        $noticias   = Noticia::getAll(PER_PAGE, $offset);
        $destacadas = Noticia::getDestacadas(5);
        $banners    = Banner::getByTipo('sidebar');

        VisitTracker::track(null, '/noticias', 'noticias');

        $this->render('public/noticias', [
            'title'       => 'Noticias - ' . SITE_NAME,
            'description' => 'Las ultimas noticias y novedades del comercio local de Purranque',
            'og_image'    => asset('img/og/noticia-default.jpg'),
            'noticias'    => $noticias,
            'destacadas'  => $destacadas,
            'banners'     => $banners,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'baseUrl'     => '/noticias',
        ]);
    }

    /**
     * GET /noticia/{slug}
     */
    public function show(string $slug): void
    {
        $noticia = Noticia::getBySlug($slug);

        if (!$noticia) {
            Response::error(404);
            return;
        }

        $relacionadas = Noticia::getRelacionadas($noticia['id'], 3);
        $banners      = Banner::getByTipo('sidebar');

        VisitTracker::track(null, "/noticia/{$slug}", 'noticia');

        $title = $noticia['seo_titulo'] ?: $noticia['titulo'] . ' - ' . SITE_NAME;
        $description = $noticia['seo_descripcion'] ?: ($noticia['extracto'] ?? truncate($noticia['contenido'] ?? '', 160));
        $ogImage = null;
        if (!empty($noticia['seo_imagen_og'])) {
            $ogImage = asset('img/noticias/' . $noticia['seo_imagen_og']);
        } elseif (!empty($noticia['imagen'])) {
            $ogImage = asset('img/noticias/' . $noticia['imagen']);
        }

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Noticias', 'url' => '/noticias'],
            ['label' => $noticia['titulo']],
        ];

        $this->render('public/noticia', [
            'title'        => $title,
            'description'  => $description,
            'keywords'     => $noticia['seo_keywords'] ?? '',
            'og_image'     => $ogImage,
            'og_type'      => 'article',
            'noindex'      => (bool) ($noticia['seo_noindex'] ?? false),
            'noticia'      => $noticia,
            'relacionadas' => $relacionadas,
            'banners'      => $banners,
            'breadcrumbs'  => $breadcrumbs,
            'schemas'      => [
                Seo::schemaArticle($noticia),
                Seo::schemaBreadcrumbs($breadcrumbs),
            ],
        ]);
    }
}
