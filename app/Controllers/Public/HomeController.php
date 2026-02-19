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

        try {
            $categorias          = Categoria::getWithComerciosCount();
            $comerciosDestacados = Comercio::getDestacados(8);
            $noticias            = Noticia::getDestacadas(3);
            $banners             = Banner::getByTipo('hero', 5);
            $fechasPersonales    = FechaEspecial::getAllByTipo('personal');
            $fechasCalendario    = FechaEspecial::getAllByTipo('calendario');
            $fechasComerciales   = FechaEspecial::getAllByTipo('comercial');
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
            'schemas'            => [Seo::schemaWebSite()],
        ]);
    }

    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $urls = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => url('/noticias'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => url('/mapa'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => url('/buscar'), 'priority' => '0.6', 'changefreq' => 'weekly'],
        ];

        try {
            // Categorias
            $categorias = $this->db->fetchAll("SELECT slug FROM categorias WHERE activo = 1");
            foreach ($categorias as $cat) {
                $urls[] = ['loc' => url('/categoria/' . $cat['slug']), 'priority' => '0.8', 'changefreq' => 'weekly'];
            }

            // Fechas especiales
            $fechas = $this->db->fetchAll("SELECT slug FROM fechas_especiales WHERE activo = 1");
            foreach ($fechas as $fe) {
                $urls[] = ['loc' => url('/fecha/' . $fe['slug']), 'priority' => '0.7', 'changefreq' => 'weekly'];
            }

            // Comercios
            $comercios = $this->db->fetchAll("SELECT slug FROM comercios WHERE activo = 1");
            foreach ($comercios as $com) {
                $urls[] = ['loc' => url('/comercio/' . $com['slug']), 'priority' => '0.7', 'changefreq' => 'weekly'];
            }

            // Noticias
            $noticias = $this->db->fetchAll("SELECT slug FROM noticias WHERE activo = 1");
            foreach ($noticias as $not) {
                $urls[] = ['loc' => url('/noticia/' . $not['slug']), 'priority' => '0.6', 'changefreq' => 'monthly'];
            }
        } catch (\Throwable $e) {
            // Sin BD, solo URLs estaticas
        }

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            echo '<url>';
            echo '<loc>' . e($u['loc']) . '</loc>';
            echo '<priority>' . $u['priority'] . '</priority>';
            echo '<changefreq>' . $u['changefreq'] . '</changefreq>';
            echo '</url>';
        }
        echo '</urlset>';
        exit;
    }
}
