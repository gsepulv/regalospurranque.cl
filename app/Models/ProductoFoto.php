<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Fotos de Productos
 * Galería múltiple de imágenes por producto
 */
class ProductoFoto
{
    /**
     * Obtener fotos de un producto ordenadas
     */
    public static function findByProductoId(int $productoId): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM producto_fotos WHERE producto_id = ? ORDER BY es_principal DESC, orden ASC",
            [$productoId]
        );
    }

    /**
     * Obtener la foto principal de un producto
     */
    public static function getPrincipal(int $productoId): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM producto_fotos WHERE producto_id = ? ORDER BY es_principal DESC, orden ASC LIMIT 1",
            [$productoId]
        );
    }

    /**
     * Crear nueva foto
     */
    public static function create(array $data): int
    {
        return Database::getInstance()->insert('producto_fotos', $data);
    }

    /**
     * Obtener foto por ID
     */
    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM producto_fotos WHERE id = ?", [$id]);
    }

    /**
     * Eliminar foto por ID
     */
    public static function delete(int $id): int
    {
        return Database::getInstance()->delete('producto_fotos', 'id = ?', [$id]);
    }

    /**
     * Contar fotos de un producto
     */
    public static function countByProductoId(int $productoId): int
    {
        return Database::getInstance()->count('producto_fotos', 'producto_id = ?', [$productoId]);
    }

    /**
     * Marcar una foto como principal (desmarca las demás)
     */
    public static function setPrincipal(int $productoId, int $fotoId): void
    {
        $db = Database::getInstance();
        $db->execute("UPDATE producto_fotos SET es_principal = 0 WHERE producto_id = ?", [$productoId]);
        $db->execute("UPDATE producto_fotos SET es_principal = 1 WHERE id = ? AND producto_id = ?", [$fotoId, $productoId]);
    }

    /**
     * Obtener el siguiente orden disponible
     */
    public static function getNextOrden(int $productoId): int
    {
        $result = Database::getInstance()->fetch(
            "SELECT COALESCE(MAX(orden), -1) + 1 AS next_orden FROM producto_fotos WHERE producto_id = ?",
            [$productoId]
        );
        return (int) ($result['next_orden'] ?? 0);
    }
}
