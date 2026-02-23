<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Comercio;
use App\Models\Noticia;
use App\Models\Banner;
use App\Models\FechaEspecial;
use App\Services\Seo;

/**
 * Pagina principal publica
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $categorias = [];
        $comerciosDestacados = [];
        $noticias = [];
        $banners = [];
        $fechasPersonales = [];
        $fechasCalendario = [];
        $fechasComerciales = [];
        $proximaFecha = null;

        $hasComercio = fn($item) => ((int)($item['comercios_count'] ?? 0)) > 0;

        try {
            $categorias          = array_values(array_filter(Categoria::getWithComerciosCount(), $hasComercio));
            $comerciosDestacados = Comercio::getDestacados(8);
            $noticias            = Noticia::getDestacadas(3);
            $banners             = Banner::getByTipo('hero', 5);
            $fechasPersonales    = array_values(array_filter(FechaEspecial::getAllByTipo('personal'), $hasComercio));
            $fechasCalendario    = array_values(array_filter(FechaEspecial::getAllByTipo('calendario'), $hasComercio));
            $fechasComerciales   = array_values(array_filter(FechaEspecial::getAllByTipo('comercial'), $hasComercio));
            $proximaFecha        = FechaEspecial::getProximaConFecha();
        } catch (\Throwable $e) {
            // Sin BD, la home muestra estructura vacia
        }

        $this->render('public/home', [
            'title'              => SITE_NAME . ' — Directorio Comercial de Purranque',
            'description'        => SITE_DESCRIPTION,
            'categorias'         => $categorias,
            'comercios'          => $comerciosDestacados,
            'noticias'           => $noticias,
            'banners'            => $banners,
            'fechasPersonales'   => $fechasPersonales,
            'fechasCalendario'   => $fechasCalendario,
            'fechasComerciales'  => $fechasComerciales,
            'proximaFecha'       => $proximaFecha,
            'og_image'           => asset('img/og/og-regalos-purranque.jpg'),
            'schemas'            => [Seo::schemaWebSite()],
        ]);
    }

    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $today = date('Y-m-d');

        $urls = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => $today],
            ['loc' => url('/categorias'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $today],
            ['loc' => url('/celebraciones'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $today],
            ['loc' => url('/comercios'), 'priority' => '0.8', 'changefreq' => 'weekly', 'lastmod' => $today],
            ['loc' => url('/noticias'), 'priority' => '0.8', 'changefreq' => 'daily', 'lastmod' => $today],
            ['loc' => url('/mapa'), 'priority' => '0.7', 'changefreq' => 'weekly', 'lastmod' => $today],
            ['loc' => url('/contacto'), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => url('/planes'), 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $today],
            ['loc' => url('/terminos'), 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => url('/privacidad'), 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];

        // Cada sección aislada: si una falla, las demás siguen generándose
        $sections = [
            'categorias' => function () use (&$urls) {
                $rows = $this->db->fetchAll(
                    "SELECT c.slug, c.updated_at FROM categorias c WHERE c.activo = 1
                     AND EXISTS (SELECT 1 FROM comercio_categoria cc
                                 JOIN comercios co ON co.id = cc.comercio_id
                                 WHERE cc.categoria_id = c.id AND co.activo = 1)"
                );
                foreach ($rows as $r) {
                    $urls[] = [
                        'loc' => url('/categoria/' . $r['slug']),
                        'priority' => '0.8',
                        'changefreq' => 'weekly',
                        'lastmod' => $r['updated_at'] ? date('Y-m-d', strtotime($r['updated_at'])) : null,
                    ];
                }
            },
            'fechas' => function () use (&$urls) {
                $rows = $this->db->fetchAll("SELECT slug, updated_at FROM fechas_especiales WHERE activo = 1");
                foreach ($rows as $r) {
                    $urls[] = [
                        'loc' => url('/fecha/' . $r['slug']),
                        'priority' => '0.7',
                        'changefreq' => 'weekly',
                        'lastmod' => $r['updated_at'] ? date('Y-m-d', strtotime($r['updated_at'])) : null,
                    ];
                }
            },
            'comercios' => function () use (&$urls) {
                $rows = $this->db->fetchAll("SELECT slug, updated_at FROM comercios WHERE activo = 1 AND calidad_ok = 1");
                foreach ($rows as $r) {
                    $urls[] = [
                        'loc' => url('/comercio/' . $r['slug']),
                        'priority' => '0.7',
                        'changefreq' => 'weekly',
                        'lastmod' => $r['updated_at'] ? date('Y-m-d', strtotime($r['updated_at'])) : null,
                    ];
                }
            },
            'noticias' => function () use (&$urls) {
                $rows = $this->db->fetchAll("SELECT slug, updated_at FROM noticias WHERE activo = 1");
                foreach ($rows as $r) {
                    $urls[] = [
                        'loc' => url('/noticia/' . $r['slug']),
                        'priority' => '0.6',
                        'changefreq' => 'monthly',
                        'lastmod' => $r['updated_at'] ? date('Y-m-d', strtotime($r['updated_at'])) : null,
                    ];
                }
            },
        ];

        foreach ($sections as $name => $fn) {
            try {
                $fn();
            } catch (\Throwable $e) {
                error_log("Sitemap error [{$name}]: " . $e->getMessage());
            }
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            echo '<url>';
            echo '<loc>' . e($u['loc']) . '</loc>';
            if (!empty($u['lastmod'])) {
                echo '<lastmod>' . $u['lastmod'] . '</lastmod>';
            }
            echo '<priority>' . $u['priority'] . '</priority>';
            echo '<changefreq>' . $u['changefreq'] . '</changefreq>';
            echo '</url>';
        }
        echo '</urlset>';
        exit;
    }
}
