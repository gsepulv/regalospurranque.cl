<?php
namespace App\Models;

use App\Core\Database;

class CambioPendiente
{
    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT cp.*, c.nombre as comercio_nombre, c.slug as comercio_slug,
                    u.nombre as usuario_nombre
             FROM comercio_cambios_pendientes cp
             INNER JOIN comercios c ON cp.comercio_id = c.id
             LEFT JOIN admin_usuarios u ON cp.usuario_id = u.id
             WHERE cp.id = ?",
            [$id]
        );
    }

    public static function getPendiente(int $id): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM comercio_cambios_pendientes WHERE id = ? AND estado = 'pendiente'", [$id]
        );
    }

    public static function getFiltered(?string $estado = null): array
    {
        $db = Database::getInstance();
        $where = '1=1';
        $params = [];
        if ($estado) {
            $where = 'cp.estado = ?';
            $params[] = $estado;
        }
        return $db->fetchAll(
            "SELECT cp.*, c.nombre as comercio_nombre, c.slug as comercio_slug,
                    u.nombre as usuario_nombre
             FROM comercio_cambios_pendientes cp
             INNER JOIN comercios c ON cp.comercio_id = c.id
             LEFT JOIN admin_usuarios u ON cp.usuario_id = u.id
             WHERE {$where}
             ORDER BY cp.created_at DESC",
            $params
        );
    }

    public static function countByEstado(string $estado): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as n FROM comercio_cambios_pendientes WHERE estado = ?", [$estado]
        );
        return (int) ($r['n'] ?? 0);
    }

    public static function getLatestPendiente(int $comercioId): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT id, created_at FROM comercio_cambios_pendientes
             WHERE comercio_id = ? AND estado = 'pendiente'
             ORDER BY created_at DESC LIMIT 1",
            [$comercioId]
        );
    }

    public static function create(array $data): int
    {
        return Database::getInstance()->insert('comercio_cambios_pendientes', $data);
    }

    public static function updateById(int $id, array $data): int
    {
        return Database::getInstance()->update('comercio_cambios_pendientes', $data, 'id = ?', [$id]);
    }
}
