<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

/**
 * Herramientas de mantenimiento del sistema
 * Sitemap, caché, modo mantenimiento, sesiones, tablas, imágenes, estadísticas
 */
class ToolsController extends Controller
{
    /**
     * GET /admin/mantenimiento/herramientas
     * Panel principal de herramientas
     */
    public function index(): void
    {
        // Estado del modo mantenimiento
        $mantenimiento = $this->db->fetch(
            "SELECT valor FROM configuracion_mantenimiento WHERE clave = 'activo'"
        );
        $mantenimientoActivo = ($mantenimiento && $mantenimiento['valor'] === '1');

        // Datos complementarios del modo mantenimiento
        $mantenimientoMensaje = $this->db->fetch(
            "SELECT valor FROM configuracion_mantenimiento WHERE clave = 'mensaje'"
        );
        $mantenimientoFin = $this->db->fetch(
            "SELECT valor FROM configuracion_mantenimiento WHERE clave = 'fecha_estimada_fin'"
        );

        // Info de sitemap
        $sitemapPath   = BASE_PATH . '/sitemap.xml';
        $sitemapExists = file_exists($sitemapPath);
        $sitemapDate   = $sitemapExists ? date('d/m/Y H:i', filemtime($sitemapPath)) : null;

        // Info de caché
        $cachePath  = BASE_PATH . '/storage/cache';
        $cacheFiles = 0;
        if (is_dir($cachePath)) {
            $items = scandir($cachePath);
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && $item !== '.htaccess') {
                    $cacheFiles++;
                }
            }
        }

        // Sesiones expiradas
        $sesionesExpiradas = $this->db->fetch(
            "SELECT COUNT(*) as total FROM sesiones_admin WHERE expira < NOW()"
        );
        $sesionesExpiradas = (int) ($sesionesExpiradas['total'] ?? 0);

        $this->render('admin/mantenimiento/herramientas', [
            'title'                  => 'Herramientas — ' . SITE_NAME,
            'tab'                    => 'herramientas',
            'mantenimientoActivo'    => $mantenimientoActivo,
            'mantenimientoMensaje'   => $mantenimientoMensaje['valor'] ?? '',
            'mantenimientoFin'       => $mantenimientoFin['valor'] ?? '',
            'sitemapExists'          => $sitemapExists,
            'sitemapDate'            => $sitemapDate,
            'cacheFiles'             => $cacheFiles,
            'sesionesExpiradas'      => $sesionesExpiradas,
        ]);
    }

    /**
     * POST /admin/mantenimiento/sitemap/regenerar
     * Regenerar sitemap.xml (usa SitemapService unificado)
     */
    public function regenerateSitemap(): void
    {
        $service   = new \App\Services\SitemapService();
        $totalUrls = $service->generateAndSave();

        $this->log('mantenimiento', 'regenerar_sitemap', 'sitemap', 0, "{$totalUrls} URLs generadas");

        $this->redirect('/admin/mantenimiento/herramientas', [
            'success' => "Sitemap regenerado con {$totalUrls} URLs",
        ]);
    }

    /**
     * POST /admin/mantenimiento/cache/limpiar
     * Eliminar archivos de caché
     */
    public function clearCache(): void
    {
        $cachePath = BASE_PATH . '/storage/cache';
        $deleted   = 0;

        if (is_dir($cachePath)) {
            $items = scandir($cachePath);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..' || $item === '.htaccess') {
                    continue;
                }
                $filePath = $cachePath . '/' . $item;
                if (is_file($filePath) && unlink($filePath)) {
                    $deleted++;
                }
            }
        }

        $this->log('mantenimiento', 'limpiar_cache', 'cache', 0, "{$deleted} archivos eliminados");

        $this->redirect('/admin/mantenimiento/herramientas', [
            'success' => "Caché limpiado: {$deleted} archivos eliminados",
        ]);
    }

    /**
     * POST /admin/mantenimiento/mantenimiento/toggle
     * Activar/desactivar modo mantenimiento
     */
    public function toggleMaintenance(): void
    {
        // Leer estado actual
        $current = $this->db->fetch(
            "SELECT valor FROM configuracion_mantenimiento WHERE clave = 'activo'"
        );
        $activo = ($current && $current['valor'] === '1') ? '0' : '1';

        $mensaje       = trim($this->request->post('mensaje', 'Sitio en mantenimiento. Volvemos pronto.'));
        $fechaEstimada = trim($this->request->post('fecha_estimada_fin', ''));

        // Upsert cada clave
        $this->db->execute(
            "INSERT INTO configuracion_mantenimiento (clave, valor) VALUES ('activo', ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
            [$activo]
        );
        $this->db->execute(
            "INSERT INTO configuracion_mantenimiento (clave, valor) VALUES ('mensaje', ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
            [$mensaje]
        );
        $this->db->execute(
            "INSERT INTO configuracion_mantenimiento (clave, valor) VALUES ('fecha_inicio', ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
            [date('Y-m-d H:i:s')]
        );
        $this->db->execute(
            "INSERT INTO configuracion_mantenimiento (clave, valor) VALUES ('fecha_estimada_fin', ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
            [$fechaEstimada]
        );

        $estado = $activo === '1' ? 'activado' : 'desactivado';
        $this->log('mantenimiento', 'toggle_mantenimiento', 'configuracion_mantenimiento', 0, "Modo mantenimiento {$estado}");

        $this->redirect('/admin/mantenimiento/herramientas', [
            'success' => "Modo mantenimiento {$estado}",
        ]);
    }

    /**
     * GET /admin/mantenimiento/phpinfo
     * Mostrar phpinfo() (solo en desarrollo)
     */
    public function phpinfo(): void
    {
        if (APP_ENV !== 'development' || \App\Services\Auth::role() !== 'admin') {
            $this->redirect('/admin/mantenimiento/herramientas', [
                'error' => 'phpinfo() solo está disponible en entorno de desarrollo para administradores',
            ]);
            return;
        }

        phpinfo();
        exit;
    }

    /**
     * POST /admin/mantenimiento/sesiones/limpiar
     * Eliminar sesiones de admin expiradas
     */
    public function clearSessions(): void
    {
        $deleted = $this->db->execute(
            "DELETE FROM sesiones_admin WHERE expira < NOW()"
        );

        $this->log('mantenimiento', 'limpiar_sesiones', 'sesiones_admin', 0, "{$deleted} sesiones expiradas eliminadas");

        $this->redirect('/admin/mantenimiento/herramientas', [
            'success' => "Se eliminaron {$deleted} sesiones expiradas",
        ]);
    }

    /**
     * POST /admin/mantenimiento/tablas/optimizar
     * Optimizar todas las tablas de la base de datos
     */
    public function optimizeTables(): void
    {
        $tables  = $this->db->fetchAll("SHOW TABLES");
        $results = [];

        foreach ($tables as $row) {
            $tableName = reset($row); // primer valor del array
            $result    = $this->db->fetchAll("OPTIMIZE TABLE `{$tableName}`");
            $results[] = [
                'table'  => $tableName,
                'status' => $result[0]['Msg_text'] ?? 'OK',
            ];
        }

        $_SESSION['flash']['optimize_results'] = json_encode($results, JSON_UNESCAPED_UNICODE);

        $totalTables = count($results);
        $this->log('mantenimiento', 'optimizar_tablas', 'database', 0, "{$totalTables} tablas optimizadas");

        $this->redirect('/admin/mantenimiento/herramientas', [
            'success' => "{$totalTables} tablas optimizadas correctamente",
        ]);
    }

    /**
     * POST /admin/mantenimiento/imagenes/verificar
     * Verificar integridad de imágenes: huérfanas en DB y en disco
     */
    public function checkImages(): void
    {
        $imgBase = BASE_PATH . '/assets/img';
        $missing = []; // en DB pero no en disco
        $orphans = []; // en disco pero no en DB

        // Mapeo: tabla => [columnas de imagen, subcarpeta en disco]
        $sources = [
            'comercios'        => [
                'columns'   => ['logo', 'portada'],
                'subfolder' => 'comercios',
            ],
            'comercio_fotos'   => [
                'columns'   => ['ruta'],
                'subfolder' => 'comercios',
            ],
            'banners'          => [
                'columns'   => ['imagen'],
                'subfolder' => 'banners',
            ],
            'noticias'         => [
                'columns'   => ['imagen', 'seo_imagen_og'],
                'subfolder' => 'noticias',
            ],
            'categorias'       => [
                'columns'   => ['imagen'],
                'subfolder' => 'categorias',
            ],
            'fechas_especiales' => [
                'columns'   => ['imagen'],
                'subfolder' => 'fechas',
            ],
        ];

        // Recopilar todas las rutas de imágenes desde la BD
        $dbImages = []; // subfolder => [filename => true]

        foreach ($sources as $table => $config) {
            $columns   = implode(', ', $config['columns']);
            $subfolder = $config['subfolder'];

            $rows = $this->db->fetchAll("SELECT id, {$columns} FROM {$table}");

            if (!isset($dbImages[$subfolder])) {
                $dbImages[$subfolder] = [];
            }

            foreach ($rows as $row) {
                foreach ($config['columns'] as $col) {
                    $value = $row[$col] ?? '';
                    if ($value === '' || $value === null) {
                        continue;
                    }
                    // Extraer solo el nombre del archivo
                    $filename = basename($value);
                    $dbImages[$subfolder][$filename] = true;

                    // Verificar si existe en disco
                    $fullPath = $imgBase . '/' . $subfolder . '/' . $filename;
                    if (!file_exists($fullPath)) {
                        $missing[] = [
                            'table'    => $table,
                            'id'       => $row['id'],
                            'column'   => $col,
                            'file'     => $filename,
                            'expected' => $subfolder . '/' . $filename,
                        ];
                    }
                }
            }
        }

        // Buscar archivos huérfanos en disco
        $subfolders = array_unique(array_column($sources, 'subfolder'));
        foreach ($subfolders as $subfolder) {
            $dirPath = $imgBase . '/' . $subfolder;
            if (!is_dir($dirPath)) {
                continue;
            }
            $files = scandir($dirPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === '.htaccess' || $file === '.gitkeep') {
                    continue;
                }
                if (!is_file($dirPath . '/' . $file)) {
                    continue;
                }
                if (!isset($dbImages[$subfolder][$file])) {
                    $orphans[] = [
                        'subfolder' => $subfolder,
                        'file'      => $file,
                        'path'      => $subfolder . '/' . $file,
                    ];
                }
            }
        }

        $results = [
            'missing'      => $missing,
            'orphans'      => $orphans,
            'missingCount' => count($missing),
            'orphanCount'  => count($orphans),
        ];

        $_SESSION['flash']['image_check_results'] = json_encode($results, JSON_UNESCAPED_UNICODE);

        $this->log(
            'mantenimiento',
            'verificar_imagenes',
            'imagenes',
            0,
            count($missing) . " faltantes, " . count($orphans) . " huérfanas"
        );

        $this->redirect('/admin/mantenimiento/herramientas', [
            'success' => 'Verificación completada: ' . count($missing) . ' faltantes, ' . count($orphans) . ' huérfanas',
        ]);
    }

    /**
     * POST /admin/mantenimiento/stats/recalcular
     * Recalcular estadísticas de visitas y clics
     */
    public function recalcStats(): void
    {
        // Recalcular visitas de comercios desde visitas_log
        $this->db->execute(
            "UPDATE comercios c SET visitas = (
                SELECT COUNT(*) FROM visitas_log WHERE comercio_id = c.id AND tipo = 'page_view'
            )"
        );

        // Recalcular clics de WhatsApp
        $this->db->execute(
            "UPDATE comercios c SET whatsapp_clicks = (
                SELECT COUNT(*) FROM visitas_log WHERE comercio_id = c.id AND tipo = 'whatsapp_click'
            )"
        );

        // Recalcular visitas de comercios (general - todas las visitas con comercio_id)
        $this->db->execute(
            "UPDATE comercios c SET visitas = (
                SELECT COUNT(*) FROM visitas_log WHERE comercio_id = c.id
            ) WHERE EXISTS (SELECT 1 FROM visitas_log WHERE comercio_id = c.id)"
        );

        $this->log('mantenimiento', 'recalcular_stats', 'estadisticas', 0, 'Estadísticas de visitas y clics recalculadas');

        $this->redirect('/admin/mantenimiento/herramientas', [
            'success' => 'Estadísticas recalculadas correctamente',
        ]);
    }
}
