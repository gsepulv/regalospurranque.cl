<?php
namespace App\Models;

use App\Core\Database;

class AdminLog
{
    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM admin_log WHERE id = ?", [$id]);
    }

    public static function getFiltered(string $where, array $params, int $limit, int $offset): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM admin_log WHERE {$where} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public static function countFiltered(string $where, array $params): int
    {
        $r = Database::getInstance()->fetch("SELECT COUNT(*) as total FROM admin_log WHERE {$where}", $params);
        return (int) ($r['total'] ?? 0);
    }

    public static function countAll(): int
    {
        $r = Database::getInstance()->fetch("SELECT COUNT(*) as total FROM admin_log");
        return (int) ($r['total'] ?? 0);
    }

    public static function getRecentLimit(int $limit): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM admin_log ORDER BY created_at DESC LIMIT ?", [$limit]
        );
    }

    public static function getTopAcciones(int $limit = 5): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT accion, COUNT(*) as total FROM admin_log GROUP BY accion ORDER BY total DESC LIMIT ?", [$limit]
        );
    }

    public static function getTopUsuarios(int $limit = 5): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT usuario_nombre, COUNT(*) as total FROM admin_log GROUP BY usuario_nombre ORDER BY total DESC LIMIT ?", [$limit]
        );
    }

    public static function getTopModulos(int $limit = 5): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT modulo, COUNT(*) as total FROM admin_log GROUP BY modulo ORDER BY total DESC LIMIT ?", [$limit]
        );
    }

    public static function getActividad30Dias(): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT DATE(created_at) as fecha, COUNT(*) as total
             FROM admin_log
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at) ORDER BY fecha ASC"
        );
    }

    public static function getDistinctUsuarios(): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT DISTINCT usuario_nombre FROM admin_log ORDER BY usuario_nombre"
        );
    }

    public static function getDistinctModulos(): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT DISTINCT modulo FROM admin_log ORDER BY modulo"
        );
    }

    public static function getForExport(string $where, array $params): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT created_at, usuario_nombre, modulo, accion, entidad_tipo, entidad_id, detalle, ip
             FROM admin_log WHERE {$where} ORDER BY created_at DESC",
            $params
        );
    }

    public static function countOlderThan(int $days): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as total FROM admin_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)", [$days]
        );
        return (int) ($r['total'] ?? 0);
    }

    public static function deleteOlderThan(int $days): int
    {
        return Database::getInstance()->execute(
            "DELETE FROM admin_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)", [$days]
        );
    }

    public static function getWeeklyStats(): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT DATE(created_at) as fecha, COUNT(*) as total
             FROM visitas_log
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(created_at) ORDER BY fecha ASC"
        );
    }
}
