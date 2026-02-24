<?php
namespace App\Models;

use App\Core\Database;

class MensajeContacto
{
    public static function create(array $data): int
    {
        return Database::getInstance()->insert('mensajes_contacto', $data);
    }

    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM mensajes_contacto WHERE id = ?", [$id]
        );
    }

    public static function getAll(int $limit, int $offset, string $where = '1=1', array $params = []): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM mensajes_contacto WHERE {$where} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public static function countAll(string $where = '1=1', array $params = []): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as total FROM mensajes_contacto WHERE {$where}", $params
        );
        return (int) ($r['total'] ?? 0);
    }

    public static function countNoLeidos(): int
    {
        return Database::getInstance()->count('mensajes_contacto', 'leido = 0');
    }

    public static function marcarLeido(int $id): void
    {
        Database::getInstance()->update('mensajes_contacto', ['leido' => 1], 'id = ?', [$id]);
    }

    public static function marcarRespondido(int $id): void
    {
        Database::getInstance()->update('mensajes_contacto', ['respondido' => 1, 'leido' => 1], 'id = ?', [$id]);
    }
}
