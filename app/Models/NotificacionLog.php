<?php
namespace App\Models;

use App\Core\Database;

class NotificacionLog
{
    public static function getFiltered(string $where, array $params, int $limit, int $offset): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM notificaciones_log WHERE {$where} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public static function countFiltered(string $where, array $params): int
    {
        $r = Database::getInstance()->fetch("SELECT COUNT(*) as total FROM notificaciones_log WHERE {$where}", $params);
        return (int) ($r['total'] ?? 0);
    }

    public static function countAll(): int
    {
        return Database::getInstance()->count('notificaciones_log');
    }

    public static function countByEstado(string $estado): int
    {
        return Database::getInstance()->count('notificaciones_log', 'estado = ?', [$estado]);
    }

    public static function deleteOlderThan(int $days): int
    {
        return Database::getInstance()->execute(
            "DELETE FROM notificaciones_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)", [$days]
        );
    }
}
