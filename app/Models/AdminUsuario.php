<?php
namespace App\Models;

use App\Core\Database;

class AdminUsuario
{
    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM admin_usuarios WHERE id = ?", [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM admin_usuarios WHERE email = ?", [$email]);
    }

    public static function findByEmailAndRol(string $email, string $rol): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM admin_usuarios WHERE email = ? AND rol = ?", [$email, $rol]);
    }

    public static function getAll(): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT id, nombre, email, telefono, rol, avatar, activo, last_login, created_at
             FROM admin_usuarios ORDER BY created_at ASC"
        );
    }

    public static function create(array $data): int
    {
        return Database::getInstance()->insert('admin_usuarios', $data);
    }

    public static function updateById(int $id, array $data): int
    {
        return Database::getInstance()->update('admin_usuarios', $data, 'id = ?', [$id]);
    }

    public static function deleteById(int $id): int
    {
        return Database::getInstance()->delete('admin_usuarios', 'id = ?', [$id]);
    }

    public static function getFirstAdminEmail(): ?string
    {
        $row = Database::getInstance()->fetch(
            "SELECT email FROM admin_usuarios WHERE rol IN ('admin','superadmin') AND activo = 1 LIMIT 1"
        );
        return $row['email'] ?? null;
    }

    public static function countBySite(int $siteId): int
    {
        return Database::getInstance()->count('admin_usuarios', 'site_id = ?', [$siteId]);
    }

    public static function getComerciantes(): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT id, nombre, email FROM admin_usuarios WHERE rol = 'comerciante' ORDER BY nombre ASC"
        );
    }
}
