<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Productos
 * Catálogo de productos por comerciante
 */
class Producto
{
    /**
     * Obtener productos de un comercio
     */
    public static function findByComercioId(int $comercioId, bool $soloActivos = true): array
    {
        $sql = "SELECT * FROM productos WHERE comercio_id = ?";
        if ($soloActivos) {
            $sql .= " AND activo = 1";
        }
        $sql .= " ORDER BY orden ASC, created_at DESC";

        return Database::getInstance()->fetchAll($sql, [$comercioId]);
    }

    /**
     * Obtener un producto por ID
     */
    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM productos WHERE id = ?", [$id]);
    }

    /**
     * Crear producto
     */
    public static function create(array $data): int
    {
        return Database::getInstance()->insert('productos', $data);
    }

    /**
     * Actualizar producto
     */
    public static function update(int $id, array $data): int
    {
        return Database::getInstance()->update('productos', $data, 'id = ?', [$id]);
    }

    /**
     * Eliminar producto
     */
    public static function delete(int $id): int
    {
        return Database::getInstance()->delete('productos', 'id = ?', [$id]);
    }

    /**
     * Contar productos de un comercio
     */
    public static function countByComercioId(int $comercioId): int
    {
        return Database::getInstance()->count('productos', 'comercio_id = ?', [$comercioId]);
    }

    /**
     * Productos destacados para home (con info del comercio)
     */
    public static function getDestacadosParaHome(int $limite = 8): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT p.*, c.nombre AS comercio_nombre, c.slug AS comercio_slug, c.whatsapp AS comercio_whatsapp
             FROM productos p
             INNER JOIN comercios c ON p.comercio_id = c.id
             WHERE p.activo = 1 AND c.activo = 1 AND p.imagen IS NOT NULL
             ORDER BY p.created_at DESC
             LIMIT ?",
            [$limite]
        );
    }
}
