<?php

namespace App\Services;

use App\Core\Database;

/**
 * SitemapService — Generador unificado de sitemap.xml
 * 
 * Fuente única de verdad para la generación del sitemap.
 * Usado por:
 *   - SeoAdminController::generateSitemap()   → POST /admin/seo/sitemap
 *   - ToolsController::regenerateSitemap()     → POST /admin/mantenimiento/sitemap/regenerar
 *   - HomeController::sitemap()                → GET  /sitemap.xml (dinámico)
 * 
 * Criterios de inclusión:
 *   - Páginas estáticas principales (no bloqueadas por robots.txt)
 *   - Categorías activas CON al menos 1 comercio activo y calidad_ok
 *   - Fechas especiales activas (todas, tengan o no comercios)
 *   - Comercios activos con calidad_ok
 *   - Noticias activas sin noindex
 *   - Páginas legales
 * 
 * @since 2026-02-26
 */
class SitemapService
{
    private Database $db;
    private string $siteUrl;

    public function __construct()
    {
        $this->db      = Database::getInstance();
        $this->siteUrl = SITE_URL;
    }

    /**
     * Generar el XML completo del sitemap
     */
    public function generateXml(): string
    {
        $today = date('Y-m-d');
        $urls  = [];

        // ── 1. Páginas estáticas principales ──────────────────────────
        $staticPages = [
            ['path' => '/',              'priority' => '1.0', 'freq' => 'daily',   'lastmod' => $today],
            ['path' => '/categorias',    'priority' => '0.8', 'freq' => 'weekly',  'lastmod' => $today],
            ['path' => '/celebraciones', 'priority' => '0.8', 'freq' => 'weekly',  'lastmod' => $today],
            ['path' => '/comercios',     'priority' => '0.8', 'freq' => 'weekly',  'lastmod' => $today],
            ['path' => '/noticias',      'priority' => '0.8', 'freq' => 'daily',   'lastmod' => $today],
            ['path' => '/mapa',          'priority' => '0.7', 'freq' => 'weekly',  'lastmod' => $today],
            ['path' => '/contacto',      'priority' => '0.6', 'freq' => 'monthly', 'lastmod' => $today],
            ['path' => '/planes',        'priority' => '0.5', 'freq' => 'monthly', 'lastmod' => $today],
            // /registrar-comercio: landing de captación, debe indexarse
            ['path' => '/registrar-comercio', 'priority' => '0.6', 'freq' => 'monthly', 'lastmod' => $today],
        ];

        // Páginas legales
        $legalPages = [
            ['path' => '/terminos',   'priority' => '0.3', 'freq' => 'yearly', 'lastmod' => null],
            ['path' => '/privacidad', 'priority' => '0.3', 'freq' => 'yearly', 'lastmod' => null],
            ['path' => '/cookies',    'priority' => '0.3', 'freq' => 'yearly', 'lastmod' => null],
            ['path' => '/contenidos', 'priority' => '0.3', 'freq' => 'yearly', 'lastmod' => null],
            ['path' => '/derechos',   'priority' => '0.3', 'freq' => 'yearly', 'lastmod' => null],
        ];

        $urls = array_merge($staticPages, $legalPages);

        // ── 2. Categorías con comercios activos + calidad_ok ──────────
        $categorias = $this->db->fetchAll(
            "SELECT c.slug, c.updated_at 
             FROM categorias c 
             WHERE c.activo = 1
               AND EXISTS (
                   SELECT 1 FROM comercio_categoria cc
                   JOIN comercios com ON cc.comercio_id = com.id
                   WHERE cc.categoria_id = c.id 
                     AND com.activo = 1 
                     AND com.calidad_ok = 1
               )
             ORDER BY c.nombre"
        );
        foreach ($categorias as $c) {
            $urls[] = [
                'path'     => '/categoria/' . $c['slug'],
                'priority' => '0.8',
                'freq'     => 'weekly',
                'lastmod'  => $c['updated_at'] ?? null,
            ];
        }

        // ── 3. Fechas especiales activas ──────────────────────────────
        $fechas = $this->db->fetchAll(
            "SELECT slug, updated_at 
             FROM fechas_especiales 
             WHERE activo = 1
             ORDER BY nombre"
        );
        foreach ($fechas as $f) {
            $urls[] = [
                'path'     => '/fecha/' . $f['slug'],
                'priority' => '0.7',
                'freq'     => 'weekly',
                'lastmod'  => $f['updated_at'] ?? null,
            ];
        }

        // ── 4. Comercios activos + calidad_ok ─────────────────────────
        $comercios = $this->db->fetchAll(
            "SELECT slug, updated_at 
             FROM comercios 
             WHERE activo = 1 AND calidad_ok = 1
             ORDER BY nombre"
        );
        foreach ($comercios as $c) {
            $urls[] = [
                'path'     => '/comercio/' . $c['slug'],
                'priority' => '0.7',
                'freq'     => 'weekly',
                'lastmod'  => $c['updated_at'] ?? null,
            ];
        }

        // ── 5. Noticias activas (sin noindex) ─────────────────────────
        $noticias = $this->db->fetchAll(
            "SELECT slug, updated_at 
             FROM noticias 
             WHERE activo = 1 AND (seo_noindex = 0 OR seo_noindex IS NULL)
             ORDER BY id DESC"
        );
        foreach ($noticias as $n) {
            $urls[] = [
                'path'     => '/noticia/' . $n['slug'],
                'priority' => '0.6',
                'freq'     => 'monthly',
                'lastmod'  => $n['updated_at'] ?? null,
            ];
        }

        // ── Construir XML ─────────────────────────────────────────────
        return $this->buildXml($urls);
    }

    /**
     * Generar XML y guardarlo como archivo físico
     * @return int Cantidad de URLs generadas
     */
    public function generateAndSave(): int
    {
        $xml = $this->generateXml();

        $sitemapPath = BASE_PATH . '/sitemap.xml';
        file_put_contents($sitemapPath, $xml);

        return substr_count($xml, '<url>');
    }

    /**
     * Construir el XML a partir del array de URLs
     */
    private function buildXml(array $urls): string
    {
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $u) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($this->siteUrl . $u['path']) . '</loc>' . "\n";

            if (!empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . date('Y-m-d', strtotime($u['lastmod'])) . '</lastmod>' . "\n";
            }

            $xml .= '    <changefreq>' . $u['freq'] . '</changefreq>' . "\n";
            $xml .= '    <priority>' . $u['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>' . "\n";

        return $xml;
    }
}
