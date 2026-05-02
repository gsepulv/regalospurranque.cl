<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Comercio;
use App\Models\Noticia;
use App\Models\Banner;
use App\Models\FechaEspecial;
use App\Models\Producto;
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
        $productosDestacados = [];

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
            $productosDestacados = Producto::getDestacadosParaHome(8);
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
            'productosDestacados' => $productosDestacados,
            'og_image'           => asset('img/og/og-regalos-purranque.jpg'),
            'keywords'           => 'comercios purranque, directorio comercial purranque, tiendas purranque, regalos purranque, servicios purranque',
            'schemas'            => [Seo::schemaWebSite()],
        ]);
    }

    /**
     * GET /sitemap.xml — Servir sitemap
     * Sirve el archivo físico si existe y es reciente (<1h).
     * En caso contrario regenera vía SitemapService::generateAndSave().
     * Si la regeneración falla y existe un archivo viejo, lo sirve como fallback.
     */
    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $sitemapPath = BASE_PATH . '/sitemap.xml';
        $maxAge      = 3600; // 1 hora

        $needsRegen = !file_exists($sitemapPath)
                    || (time() - filemtime($sitemapPath) > $maxAge);

        if ($needsRegen) {
            try {
                $service = new \App\Services\SitemapService();
                $service->generateAndSave();
            } catch (\Throwable $e) {
                error_log('[sitemap] Regen failed: ' . $e->getMessage());
                if (!file_exists($sitemapPath)) {
                    http_response_code(500);
                    echo '<?xml version="1.0" encoding="UTF-8"?><error/>';
                    return;
                }
                // Si existe el archivo viejo, lo servimos como fallback.
            }
        }

        readfile($sitemapPath);
    }
}
