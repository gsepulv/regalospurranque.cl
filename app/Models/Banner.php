<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Banners publicitarios
 */
class Banner
{
    /**
     * Banners activos por tipo
     */
    public static function getByTipo(string $tipo, ?int $limit = null): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM banners
                WHERE activo = 1 AND tipo = ?
                AND (fecha_inicio IS NULL OR fecha_inicio <= CURDATE())
                AND (fecha_fin IS NULL OR fecha_fin >= CURDATE())
                ORDER BY orden ASC";
        $params = [$tipo];
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        return $db->fetchAll($sql, $params);
    }

    // ══════════════════════════════════════════════════════════════
    // CRUD y helpers admin
    // ══════════════════════════════════════════════════════════════

    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM banners WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        return Database::getInstance()->insert('banners', $data);
    }

    public static function updateById(int $id, array $data): int
    {
        return Database::getInstance()->update('banners', $data, 'id = ?', [$id]);
    }

    public static function deleteById(int $id): int
    {
        return Database::getInstance()->delete('banners', 'id = ?', [$id]);
    }

    public static function countActive(): int
    {
        return Database::getInstance()->count('banners', 'activo = 1');
    }

    public static function getAdminFiltered(string $where, array $params): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT b.*, c.nombre as comercio_nombre
             FROM banners b
             LEFT JOIN comercios c ON b.comercio_id = c.id
             WHERE {$where}
             ORDER BY b.tipo ASC, b.orden ASC",
            $params
        );
    }

    public static function resetStats(int $id): void
    {
        Database::getInstance()->update('banners', ['clicks' => 0, 'impresiones' => 0], 'id = ?', [$id]);
    }

    /**
     * Incrementar impresiones
     */
    public static function incrementImpresiones(int $bannerId): void
    {
        $db = Database::getInstance();
        $db->execute("UPDATE banners SET impresiones = impresiones + 1 WHERE id = ?", [$bannerId]);
    }

    /**
     * Incrementar clics
     */
    public static function incrementClicks(int $bannerId): void
    {
        $db = Database::getInstance();
        $db->execute("UPDATE banners SET clicks = clicks + 1 WHERE id = ?", [$bannerId]);
    }
}