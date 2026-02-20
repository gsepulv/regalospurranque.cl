<?php
namespace App\Models;

use App\Core\Database;

class Configuracion
{
    public static function getAll(): array
    {
        return Database::getInstance()->fetchAll("SELECT clave, valor, grupo FROM configuracion");
    }

    public static function getByGroup(string $grupo): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT clave, valor FROM configuracion WHERE grupo = ?", [$grupo]
        );
    }

    public static function getByKey(string $clave): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM configuracion WHERE clave = ?", [$clave]);
    }

    public static function upsert(string $clave, string $valor, string $grupo = ''): void
    {
        Database::getInstance()->execute(
            "INSERT INTO configuracion (clave, valor, grupo) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
            [$clave, $valor, $grupo]
        );
    }
}
