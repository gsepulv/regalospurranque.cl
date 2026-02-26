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

    /**
     * GET /sitemap.xml — Servir sitemap
     * Prioriza archivo físico. Si no existe, genera en tiempo real.
     */
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $sitemapPath = BASE_PATH . '/sitemap.xml';

        // Si existe el archivo físico (generado desde admin), servirlo
        if (file_exists($sitemapPath)) {
            readfile($sitemapPath);
            return;
        }

        // Fallback: generar en tiempo real
        $service = new \App\Services\SitemapService();
        echo $service->generateXml();
    }
}
