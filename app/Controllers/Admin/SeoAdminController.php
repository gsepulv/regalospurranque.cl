<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\FechaEspecial;
use App\Models\SeoRedirect;

/**
 * Gestión de configuración SEO
 */
class SeoAdminController extends Controller
{
    /**
     * GET /admin/seo — Página principal con tabs
     */
    public function index(): void
    {
        $tab = $this->request->get('tab', 'config');

        $data = [
            'title' => 'SEO — ' . SITE_NAME,
            'tab'   => $tab,
        ];

        switch ($tab) {
            case 'metatags':
                $data['pages'] = $this->getPagesList();
                break;

            case 'schema':
                $data['schema'] = $this->getSchemaConfig();
                break;

            case 'redirects':
                $data['redirects'] = SeoRedirect::getAll();
                break;

            case 'sitemap':
                $sitemapPath = BASE_PATH . '/sitemap.xml';
                $data['sitemapExists'] = file_exists($sitemapPath);
                $data['sitemapDate']   = $data['sitemapExists'] ? date('d/m/Y H:i', filemtime($sitemapPath)) : null;
                break;

            case 'tools':
                // No data needed initially
                break;

            default: // config
                $data['config'] = $this->getSeoConfig();
                break;
        }

        $this->render('admin/seo/index', $data);
    }

    /**
     * POST /admin/seo/config — Guardar configuración global
     */
    public function saveConfig(): void
    {
        $keys = [
            'site_title_suffix', 'default_description', 'default_keywords',
            'og_default_image', 'google_analytics', 'google_search_console',
            'robots_txt_extra', 'canonical_base',
        ];

        foreach ($keys as $key) {
            $value = trim($this->request->post($key, ''));
            SeoRedirect::upsertConfig($key, $value);
        }

        $this->log('seo', 'config_update', 'seo_config', 0, 'Configuración SEO global actualizada');
        $this->back(['success' => 'Configuración SEO guardada correctamente']);
    }

    /**
     * POST /admin/seo/metatags — Guardar meta tags por página
     */
    public function saveMetaTags(): void
    {
        $page  = trim($this->request->post('page', ''));
        $title = trim($this->request->post('seo_title', ''));
        $desc  = trim($this->request->post('seo_description', ''));
        $keys  = trim($this->request->post('seo_keywords', ''));
        $image = trim($this->request->post('seo_image', ''));

        if ($page === '') {
            $this->back(['error' => 'Página no especificada']);
            return;
        }

        // Validar que la página es una clave válida
        $validStaticPages = ['home', 'mapa', 'buscar', 'noticias'];
        $isValid = in_array($page, $validStaticPages, true)
                || preg_match('/^cat_\d+$/', $page)
                || preg_match('/^fecha_\d+$/', $page);

        if (!$isValid) {
            $this->back(['error' => 'Página no válida']);
            return;
        }

        $fields = [
            "page_{$page}_title"       => $title,
            "page_{$page}_description" => $desc,
            "page_{$page}_keywords"    => $keys,
            "page_{$page}_image"       => $image,
        ];

        foreach ($fields as $clave => $valor) {
            SeoRedirect::upsertConfig($clave, $valor);
        }

        $this->log('seo', 'metatags_update', 'seo_config', 0, "Meta tags actualizados para: {$page}");
        $this->redirect('/admin/seo?tab=metatags', ['success' => 'Meta tags guardados para "' . $page . '"']);
    }

    /**
     * POST /admin/seo/schema — Guardar configuración Schema.org
     */
    public function saveSchema(): void
    {
        $keys = [
            'schema_type', 'schema_name', 'schema_description',
            'schema_address', 'schema_locality', 'schema_region',
            'schema_phone', 'schema_email', 'schema_logo',
            'schema_facebook', 'schema_instagram', 'schema_twitter',
        ];

        foreach ($keys as $key) {
            $value = trim($this->request->post($key, ''));
            SeoRedirect::upsertConfig($key, $value);
        }

        $this->log('seo', 'schema_update', 'seo_config', 0, 'Configuración Schema.org actualizada');
        $this->redirect('/admin/seo?tab=schema', ['success' => 'Configuración Schema.org guardada']);
    }

    /**
     * POST /admin/seo/redirects — Crear redirect
     */
    public function createRedirect(): void
    {
        $urlOrigen  = trim($this->request->post('url_origen', ''));
        $urlDestino = trim($this->request->post('url_destino', ''));
        $tipo       = (int) $this->request->post('tipo', 301);

        if ($urlOrigen === '' || $urlDestino === '') {
            $this->back(['error' => 'URL origen y destino son obligatorias']);
            return;
        }

        if (!in_array($tipo, [301, 302])) {
            $tipo = 301;
        }

        // Verificar duplicado
        $existing = SeoRedirect::findByUrlOrigen($urlOrigen);

        if ($existing) {
            $this->back(['error' => 'Ya existe una redirección para esa URL origen']);
            return;
        }

        SeoRedirect::create([
            'url_origen'  => $urlOrigen,
            'url_destino' => $urlDestino,
            'tipo'        => $tipo,
            'activo'      => 1,
        ]);

        $this->log('seo', 'crear_redirect', 'seo_redirect', 0, "Redirect {$tipo}: {$urlOrigen} → {$urlDestino}");
        $this->redirect('/admin/seo?tab=redirects', ['success' => 'Redirección creada correctamente']);
    }

    /**
     * POST /admin/seo/redirects/eliminar/{id}
     */
    public function deleteRedirect(string $id): void
    {
        $id = (int) $id;
        SeoRedirect::deleteById($id);
        $this->log('seo', 'eliminar_redirect', 'seo_redirect', $id, "Redirect eliminado");

        $this->back(['success' => 'Redirección eliminada']);
    }

    /**
     * POST /admin/seo/redirects/toggle/{id}
     */
    public function toggleRedirect(string $id): void
    {
        $id = (int) $id;
        SeoRedirect::toggleActive($id);

        $this->json(['ok' => true, 'csrf' => $_SESSION['csrf_token']]);
    }

    /**
     * POST /admin/seo/sitemap — Generar sitemap
     */
    public function generateSitemap(): void
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Páginas principales
        $today = date('Y-m-d');
        $xml .= $this->sitemapUrl('/', '1.0', 'daily', $today);
        $xml .= $this->sitemapUrl('/categorias', '0.8', 'weekly', $today);
        $xml .= $this->sitemapUrl('/celebraciones', '0.8', 'weekly', $today);
        $xml .= $this->sitemapUrl('/comercios', '0.8', 'weekly', $today);
        $xml .= $this->sitemapUrl('/noticias', '0.8', 'daily', $today);
        $xml .= $this->sitemapUrl('/mapa', '0.7', 'weekly', $today);
        $xml .= $this->sitemapUrl('/contacto', '0.6', 'monthly', $today);
        $xml .= $this->sitemapUrl('/planes', '0.5', 'monthly', $today);
        $xml .= $this->sitemapUrl('/terminos', '0.3', 'yearly');
        $xml .= $this->sitemapUrl('/privacidad', '0.3', 'yearly');

        // Categorías (solo con comercios activos y calidad_ok)
        $categorias = $this->db->fetchAll(
            "SELECT c.slug, c.updated_at FROM categorias c WHERE c.activo = 1
             AND EXISTS (SELECT 1 FROM comercio_categoria cc
                         JOIN comercios co ON co.id = cc.comercio_id
                         WHERE cc.categoria_id = c.id AND co.activo = 1 AND co.calidad_ok = 1)"
        );
        foreach ($categorias as $c) {
            $xml .= $this->sitemapUrl('/categoria/' . $c['slug'], '0.8', 'weekly', $c['updated_at']);
        }

        // Fechas especiales
        $fechas = $this->db->fetchAll(
            "SELECT slug, updated_at FROM fechas_especiales WHERE activo = 1"
        );
        foreach ($fechas as $f) {
            $xml .= $this->sitemapUrl('/fecha/' . $f['slug'], '0.7', 'weekly', $f['updated_at']);
        }

        // Comercios (solo calidad_ok)
        $comercios = $this->db->fetchAll(
            "SELECT slug, updated_at FROM comercios WHERE activo = 1 AND calidad_ok = 1"
        );
        foreach ($comercios as $c) {
            $xml .= $this->sitemapUrl('/comercio/' . $c['slug'], '0.7', 'weekly', $c['updated_at']);
        }

        // Noticias
        $noticias = $this->db->fetchAll(
            "SELECT slug, updated_at FROM noticias WHERE activo = 1"
        );
        foreach ($noticias as $n) {
            $xml .= $this->sitemapUrl('/noticia/' . $n['slug'], '0.6', 'monthly', $n['updated_at']);
        }

        $xml .= '</urlset>';

        $sitemapPath = BASE_PATH . '/sitemap.xml';
        file_put_contents($sitemapPath, $xml);

        $totalUrls = substr_count($xml, '<url>');
        $this->log('seo', 'generar_sitemap', 'sitemap', 0, "{$totalUrls} URLs generadas");

        $this->redirect('/admin/seo?tab=sitemap', ['success' => "Sitemap generado con {$totalUrls} URLs"]);
    }

    /**
     * Helper para generar una entrada de sitemap
     */
    private function sitemapUrl(string $path, string $priority, string $freq, ?string $lastmod = null): string
    {
        $url  = '  <url>' . "\n";
        $url .= '    <loc>' . htmlspecialchars(SITE_URL . $path) . '</loc>' . "\n";
        if ($lastmod) {
            $url .= '    <lastmod>' . date('Y-m-d', strtotime($lastmod)) . '</lastmod>' . "\n";
        }
        $url .= '    <changefreq>' . $freq . '</changefreq>' . "\n";
        $url .= '    <priority>' . $priority . '</priority>' . "\n";
        $url .= '  </url>' . "\n";
        return $url;
    }

    /**
     * Obtener configuración SEO actual
     */
    private function getSeoConfig(): array
    {
        $defaults = [
            'site_title_suffix'      => ' — ' . SITE_NAME,
            'default_description'    => SITE_DESCRIPTION,
            'default_keywords'       => 'purranque, comercios, directorio, chile',
            'og_default_image'       => '',
            'google_analytics'       => '',
            'google_search_console'  => '',
            'robots_txt_extra'       => '',
            'canonical_base'         => SITE_URL,
        ];

        $rows = SeoRedirect::getConfig();
        $config = $defaults;
        foreach ($rows as $row) {
            if (array_key_exists($row['clave'], $config)) {
                $config[$row['clave']] = $row['valor'];
            }
        }

        return $config;
    }

    /**
     * Obtener lista de páginas con sus meta tags y score
     */
    private function getPagesList(): array
    {
        $staticPages = [
            'home'     => ['label' => 'Página principal',    'url' => '/'],
            'mapa'     => ['label' => 'Mapa',                'url' => '/mapa'],
            'buscar'   => ['label' => 'Buscador',            'url' => '/buscar'],
            'noticias' => ['label' => 'Listado de noticias', 'url' => '/noticias'],
        ];

        // Cargar meta tags guardados desde seo_config
        $rows = SeoRedirect::getConfigPagesMeta();
        $savedMeta = [];
        foreach ($rows as $row) {
            $savedMeta[$row['clave']] = $row['valor'];
        }

        $pages = [];
        foreach ($staticPages as $key => $info) {
            $title = $savedMeta["page_{$key}_title"] ?? '';
            $desc  = $savedMeta["page_{$key}_description"] ?? '';
            $keys  = $savedMeta["page_{$key}_keywords"] ?? '';
            $image = $savedMeta["page_{$key}_image"] ?? '';

            $pages[] = [
                'key'         => $key,
                'label'       => $info['label'],
                'url'         => $info['url'],
                'title'       => $title,
                'description' => $desc,
                'keywords'    => $keys,
                'image'       => $image,
                'score'       => $this->calculateScore($title, $desc, $keys, $image),
            ];
        }

        // Categorías activas
        $categorias = Categoria::getAll(true);
        foreach ($categorias as $cat) {
            $key = 'cat_' . $cat['id'];
            $title = $savedMeta["page_{$key}_title"] ?? '';
            $desc  = $savedMeta["page_{$key}_description"] ?? '';
            $keys  = $savedMeta["page_{$key}_keywords"] ?? '';
            $image = $savedMeta["page_{$key}_image"] ?? '';

            $pages[] = [
                'key'         => $key,
                'label'       => 'Cat: ' . $cat['nombre'],
                'url'         => '/categoria/' . $cat['slug'],
                'title'       => $title,
                'description' => $desc,
                'keywords'    => $keys,
                'image'       => $image,
                'score'       => $this->calculateScore($title, $desc, $keys, $image),
            ];
        }

        // Fechas especiales activas
        $fechas = FechaEspecial::getAll(true);
        foreach ($fechas as $f) {
            $key = 'fecha_' . $f['id'];
            $title = $savedMeta["page_{$key}_title"] ?? '';
            $desc  = $savedMeta["page_{$key}_description"] ?? '';
            $keys  = $savedMeta["page_{$key}_keywords"] ?? '';
            $image = $savedMeta["page_{$key}_image"] ?? '';

            $pages[] = [
                'key'         => $key,
                'label'       => 'Fecha: ' . $f['nombre'],
                'url'         => '/fecha/' . $f['slug'],
                'title'       => $title,
                'description' => $desc,
                'keywords'    => $keys,
                'image'       => $image,
                'score'       => $this->calculateScore($title, $desc, $keys, $image),
            ];
        }

        return $pages;
    }

    /**
     * Calcular score SEO (0-100)
     */
    private function calculateScore(string $title, string $desc, string $keywords, string $image): int
    {
        $score = 0;

        // Título (max 30 pts)
        if ($title !== '') {
            $len = mb_strlen($title);
            $score += 15;
            if ($len >= 30 && $len <= 60) $score += 15;
            elseif ($len > 0) $score += 5;
        }

        // Descripción (max 30 pts)
        if ($desc !== '') {
            $len = mb_strlen($desc);
            $score += 15;
            if ($len >= 80 && $len <= 160) $score += 15;
            elseif ($len > 0) $score += 5;
        }

        // Keywords (max 20 pts)
        if ($keywords !== '') {
            $score += 20;
        }

        // Imagen OG (max 20 pts)
        if ($image !== '') {
            $score += 20;
        }

        return $score;
    }

    /**
     * Obtener configuración de Schema.org
     */
    private function getSchemaConfig(): array
    {
        $defaults = [
            'schema_type'        => 'LocalBusiness',
            'schema_name'        => SITE_NAME,
            'schema_description' => SITE_DESCRIPTION,
            'schema_address'     => '',
            'schema_locality'    => 'Purranque',
            'schema_region'      => 'Los Lagos',
            'schema_phone'       => '',
            'schema_email'       => '',
            'schema_logo'        => '',
            'schema_facebook'    => '',
            'schema_instagram'   => '',
            'schema_twitter'     => '',
        ];

        $rows = SeoRedirect::getConfigSchema();

        $config = $defaults;
        foreach ($rows as $row) {
            if (array_key_exists($row['clave'], $config)) {
                $config[$row['clave']] = $row['valor'];
            }
        }

        return $config;
    }
}
