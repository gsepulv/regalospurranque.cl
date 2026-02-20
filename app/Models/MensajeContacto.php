<?php
namespace App\Models;

use App\Core\Database;

class MensajeContacto
{
    public static function create(array $data): int
    {
        return Database::getInstance()->insert('mensajes_contacto', $data);
    }
}
