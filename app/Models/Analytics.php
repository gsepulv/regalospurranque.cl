<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Analytics
 * Registro y consulta de visitas y eventos
 */
class Analytics
{
    /**
     * Registrar visita/evento
     */
    public static function registrarVisita(?int $comercioId, string $pagina, string $tipo): void
    {
        try {
            $db = Database::getInstance();
            $db->insert('visitas_log', [
                'comercio_id' => $comercioId,
                'pagina'      => mb_substr($pagina, 0, 500),
                'tipo'        => mb_substr($tipo, 0, 50),
                'ip'          => self::getClientIp(),
                'user_agent'  => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                'referrer'    => mb_substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            error_log('Analytics error: ' . $e->getMessage());
        }
    }

    /**
     * Resumen diario para cron: agrupa visitas_log por pagina y fecha
     */
    public static function resumenDiario(?string $fecha = null): void
    {
        $db = Database::getInstance();
        $fecha = $fecha ?: date('Y-m-d', strtotime('-1 day'));

        $resumen = $db->fetchAll(
            "SELECT pagina,
                    COUNT(*) as visitas,
                    COUNT(DISTINCT ip) as visitantes_unicos
             FROM visitas_log
             WHERE DATE(created_at) = ?
             GROUP BY pagina",
            [$fecha]
        );

        foreach ($resumen as $row) {
            $existing = $db->fetch(
                "SELECT id FROM analytics_diario WHERE fecha = ? AND pagina = ?",
                [$fecha, $row['pagina']]
            );

            if ($existing) {
                $db->update('analytics_diario', [
                    'visitas'            => $row['visitas'],
                    'visitantes_unicos'  => $row['visitantes_unicos'],
                ], 'id = ?', [$existing['id']]);
            } else {
                $db->insert('analytics_diario', [
                    'fecha'              => $fecha,
                    'pagina'             => $row['pagina'],
                    'visitas'            => $row['visitas'],
                    'visitantes_unicos'  => $row['visitantes_unicos'],
                ]);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════
    // Métodos de consulta para panel admin
    // ══════════════════════════════════════════════════════════════

    /**
     * Resumen general del dashboard
     */
    public static function getDashboard(string $desde, string $hasta): array
    {
        $db = Database::getInstance();

        $totalVisitas = $db->fetch(
            "SELECT COUNT(*) as total FROM visitas_log WHERE created_at BETWEEN ? AND ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );

        $visitantesUnicos = $db->fetch(
            "SELECT COUNT(DISTINCT ip) as total FROM visitas_log WHERE created_at BETWEEN ? AND ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );

        $comerciosVisitados = $db->fetch(
            "SELECT COUNT(DISTINCT comercio_id) as total FROM visitas_log
             WHERE comercio_id IS NOT NULL AND created_at BETWEEN ? AND ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );

        $whatsappClicks = $db->fetch(
            "SELECT COUNT(*) as total FROM visitas_log
             WHERE tipo = 'whatsapp_click' AND created_at BETWEEN ? AND ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );

        return [
            'visitas'             => (int) ($totalVisitas['total'] ?? 0),
            'visitantes_unicos'   => (int) ($visitantesUnicos['total'] ?? 0),
            'comercios_visitados' => (int) ($comerciosVisitados['total'] ?? 0),
            'whatsapp_clicks'     => (int) ($whatsappClicks['total'] ?? 0),
        ];
    }

    /**
     * Visitas por día para gráfico
     */
    public static function getVisitasPorDia(string $desde, string $hasta): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT DATE(created_at) as fecha,
                    COUNT(*) as visitas,
                    COUNT(DISTINCT ip) as unicos
             FROM visitas_log
             WHERE created_at BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY fecha ASC",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );
    }

    /**
     * Páginas más visitadas
     */
    public static function getPaginasTop(string $desde, string $hasta, int $limit = 20): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT pagina, COUNT(*) as visitas, COUNT(DISTINCT ip) as unicos
             FROM visitas_log
             WHERE created_at BETWEEN ? AND ?
             GROUP BY pagina
             ORDER BY visitas DESC
             LIMIT ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59', $limit]
        );
    }

    /**
     * Tipos de visita (desglose por tipo)
     */
    public static function getVisitasPorTipo(string $desde, string $hasta): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT tipo, COUNT(*) as total
             FROM visitas_log
             WHERE created_at BETWEEN ? AND ?
             GROUP BY tipo
             ORDER BY total DESC",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );
    }

    /**
     * Comercios más visitados
     */
    public static function getComerciosTop(string $desde, string $hasta, int $limit = 20): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.id, c.nombre, c.slug, c.plan,
                    COUNT(v.id) as visitas,
                    COUNT(DISTINCT v.ip) as unicos
             FROM visitas_log v
             INNER JOIN comercios c ON v.comercio_id = c.id
             WHERE v.comercio_id IS NOT NULL
               AND v.created_at BETWEEN ? AND ?
             GROUP BY c.id, c.nombre, c.slug, c.plan
             ORDER BY visitas DESC
             LIMIT ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59', $limit]
        );
    }

    /**
     * Clicks de WhatsApp por comercio
     */
    public static function getWhatsAppTop(string $desde, string $hasta, int $limit = 20): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.id, c.nombre, c.slug,
                    COUNT(v.id) as clicks
             FROM visitas_log v
             INNER JOIN comercios c ON v.comercio_id = c.id
             WHERE v.tipo = 'whatsapp_click'
               AND v.created_at BETWEEN ? AND ?
             GROUP BY c.id, c.nombre, c.slug
             ORDER BY clicks DESC
             LIMIT ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59', $limit]
        );
    }

    /**
     * Visitas por categoría
     */
    public static function getVisitasPorCategoria(string $desde, string $hasta): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT cat.id, cat.nombre, cat.slug, cat.color,
                    COUNT(v.id) as visitas
             FROM visitas_log v
             INNER JOIN comercios c ON v.comercio_id = c.id
             INNER JOIN comercio_categoria cc ON c.id = cc.comercio_id
             INNER JOIN categorias cat ON cc.categoria_id = cat.id
             WHERE v.comercio_id IS NOT NULL
               AND v.created_at BETWEEN ? AND ?
             GROUP BY cat.id, cat.nombre, cat.slug, cat.color
             ORDER BY visitas DESC",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );
    }

    /**
     * Visitas por fecha especial
     */
    public static function getVisitasPorFecha(string $desde, string $hasta): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT f.id, f.nombre, f.slug, f.tipo,
                    COUNT(v.id) as visitas
             FROM visitas_log v
             INNER JOIN comercios c ON v.comercio_id = c.id
             INNER JOIN comercio_fecha cf ON c.id = cf.comercio_id
             INNER JOIN fechas_especiales f ON cf.fecha_id = f.id
             WHERE v.comercio_id IS NOT NULL
               AND v.created_at BETWEEN ? AND ?
             GROUP BY f.id, f.nombre, f.slug, f.tipo
             ORDER BY visitas DESC",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );
    }

    /**
     * Estadísticas de banners
     */
    public static function getBannersStats(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT b.id, b.titulo, b.tipo, b.imagen, b.url,
                    b.impresiones, b.clicks,
                    CASE WHEN b.impresiones > 0
                         THEN ROUND((b.clicks / b.impresiones) * 100, 2)
                         ELSE 0 END as ctr
             FROM banners b
             WHERE b.activo = 1
             ORDER BY b.impresiones DESC"
        );
    }

    /**
     * Referrers más comunes
     */
    public static function getReferrersTop(string $desde, string $hasta, int $limit = 15): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT
                CASE
                    WHEN referrer = '' OR referrer IS NULL THEN '(directo)'
                    ELSE SUBSTRING_INDEX(SUBSTRING_INDEX(referrer, '/', 3), '//', -1)
                END as fuente,
                COUNT(*) as visitas
             FROM visitas_log
             WHERE created_at BETWEEN ? AND ?
             GROUP BY fuente
             ORDER BY visitas DESC
             LIMIT ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59', $limit]
        );
    }

    /**
     * Datos para exportar CSV
     */
    public static function getExportData(string $tipo, string $desde, string $hasta): array
    {
        switch ($tipo) {
            case 'visitas':
                return self::getVisitasPorDia($desde, $hasta);
            case 'comercios':
                return self::getComerciosTop($desde, $hasta, 1000);
            case 'categorias':
                return self::getVisitasPorCategoria($desde, $hasta);
            case 'fechas':
                return self::getVisitasPorFecha($desde, $hasta);
            case 'banners':
                return self::getBannersStats();
            case 'paginas':
                return self::getPaginasTop($desde, $hasta, 1000);
            default:
                return [];
        }
    }

    /**
     * Obtener IP real del cliente
     */
    private static function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        return mb_substr($ip, 0, 45);
    }
}
