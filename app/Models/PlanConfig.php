<?php
namespace App\Models;

use App\Core\Database;

class PlanConfig
{
    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM planes_config WHERE id = ?", [$id]);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM planes_config WHERE slug = ?", [$slug]);
    }

    public static function getAll(): array
    {
        return Database::getInstance()->fetchAll("SELECT * FROM planes_config ORDER BY orden ASC");
    }

    public static function create(array $data): int
    {
        return Database::getInstance()->insert('planes_config', $data);
    }

    public static function updateById(int $id, array $data): int
    {
        return Database::getInstance()->update('planes_config', $data, 'id = ?', [$id]);
    }

    public static function deleteById(int $id): int
    {
        return Database::getInstance()->delete('planes_config', 'id = ?', [$id]);
    }

    /**
     * Planes activos disponibles para renovación (excluye banner)
     */
    public static function getActiveForRenewal(): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM planes_config WHERE activo = 1 AND slug != 'banner' ORDER BY orden ASC"
        );
    }
}
