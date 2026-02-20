<?php
namespace App\Models;

use App\Core\Database;

class Sitio
{
    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM sitios WHERE id = ?", [$id]);
    }

    public static function getAll(): array
    {
        return Database::getInstance()->fetchAll("SELECT * FROM sitios ORDER BY id ASC");
    }

    public static function create(array $data): int
    {
        return Database::getInstance()->insert('sitios', $data);
    }

    public static function updateById(int $id, array $data): int
    {
        return Database::getInstance()->update('sitios', $data, 'id = ?', [$id]);
    }

    public static function countComerciosBySite(int $siteId): int
    {
        return Database::getInstance()->count('comercios', 'site_id = ?', [$siteId]);
    }

    public static function countCategoriasBySite(int $siteId): int
    {
        return Database::getInstance()->count('categorias', 'site_id = ?', [$siteId]);
    }
}
