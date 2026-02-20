<?php
namespace App\Models;

use App\Core\Database;

class SeoRedirect
{
    public static function getAll(): array
    {
        return Database::getInstance()->fetchAll("SELECT * FROM seo_redirects ORDER BY created_at DESC");
    }

    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM seo_redirects WHERE id = ?", [$id]);
    }

    public static function findByUrlOrigen(string $url): ?array
    {
        return Database::getInstance()->fetch("SELECT id FROM seo_redirects WHERE url_origen = ?", [$url]);
    }

    public static function create(array $data): int
    {
        return Database::getInstance()->insert('seo_redirects', $data);
    }

    public static function deleteById(int $id): int
    {
        return Database::getInstance()->delete('seo_redirects', 'id = ?', [$id]);
    }

    public static function toggleActive(int $id): void
    {
        $row = Database::getInstance()->fetch("SELECT activo FROM seo_redirects WHERE id = ?", [$id]);
        if ($row) {
            $new = $row['activo'] ? 0 : 1;
            Database::getInstance()->update('seo_redirects', ['activo' => $new], 'id = ?', [$id]);
        }
    }

    public static function getConfig(): array
    {
        return Database::getInstance()->fetchAll("SELECT clave, valor FROM seo_config");
    }

    public static function getConfigPagesMeta(): array
    {
        return Database::getInstance()->fetchAll("SELECT clave, valor FROM seo_config WHERE clave LIKE 'page_%'");
    }

    public static function getConfigSchema(): array
    {
        return Database::getInstance()->fetchAll("SELECT clave, valor FROM seo_config WHERE clave LIKE 'schema_%'");
    }

    public static function upsertConfig(string $clave, string $valor): void
    {
        Database::getInstance()->execute(
            "INSERT INTO seo_config (clave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
            [$clave, $valor]
        );
    }
}
