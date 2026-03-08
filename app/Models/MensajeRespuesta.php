<?php
namespace App\Models;

use App\Core\Database;

class MensajeRespuesta
{
    public static function crear(array $datos): int
    {
        return Database::getInstance()->insert('mensajes_respuestas', $datos);
    }

    public static function getPorMensaje(int $mensajeId): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM mensajes_respuestas WHERE mensaje_id = ? ORDER BY created_at ASC",
            [$mensajeId]
        );
    }

    public static function getUltimaRespuesta(int $mensajeId): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM mensajes_respuestas WHERE mensaje_id = ? ORDER BY created_at DESC LIMIT 1",
            [$mensajeId]
        );
    }

    public static function countPorMensaje(int $mensajeId): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as total FROM mensajes_respuestas WHERE mensaje_id = ?",
            [$mensajeId]
        );
        return (int) ($r['total'] ?? 0);
    }
}
