<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de estadísticas de compartidos
 */
class Share
{
    /**
     * Registrar un evento de compartir
     */
    public static function registrar(?int $comercioId, string $pagina, string $redSocial): void
    {
        try {
            $db = Database::getInstance();
            $db->insert('share_log', [
                'comercio_id' => $comercioId,
                'pagina'      => mb_substr($pagina, 0, 500),
                'red_social'  => mb_substr($redSocial, 0, 50),
                'ip'          => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent'  => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            error_log('Share log error: ' . $e->getMessage());
        }
    }

    /**
     * Total de compartidos en período
     */
    public static function getTotal(string $desde, string $hasta): int
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(*) as total FROM share_log
             WHERE created_at BETWEEN ? AND ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Compartidos por red social
     */
    public static function getPorRed(string $desde, string $hasta): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT red_social, COUNT(*) as total
             FROM share_log
             WHERE created_at BETWEEN ? AND ?
             GROUP BY red_social
             ORDER BY total DESC",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );
    }

    /**
     * Contenido más compartido
     */
    public static function getTopCompartido(string $desde, string $hasta, int $limit = 20): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT pagina, COUNT(*) as total
             FROM share_log
             WHERE created_at BETWEEN ? AND ?
             GROUP BY pagina
             ORDER BY total DESC
             LIMIT ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59', $limit]
        );
    }

    /**
     * Comercios más compartidos
     */
    public static function getTopComercios(string $desde, string $hasta, int $limit = 20): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT c.nombre, c.slug, COUNT(s.id) as total
             FROM share_log s
             INNER JOIN comercios c ON s.comercio_id = c.id
             WHERE s.comercio_id IS NOT NULL
               AND s.created_at BETWEEN ? AND ?
             GROUP BY c.id, c.nombre, c.slug
             ORDER BY total DESC
             LIMIT ?",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59', $limit]
        );
    }

    /**
     * Compartidos por día
     */
    public static function getPorDia(string $desde, string $hasta): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT DATE(created_at) as fecha, COUNT(*) as total
             FROM share_log
             WHERE created_at BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY fecha ASC",
            [$desde . ' 00:00:00', $hasta . ' 23:59:59']
        );
    }
}
