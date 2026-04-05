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
        $sql .= " ORDER BY FIELD(estado,'disponible','reservado','vendido','agotado'), orden ASC, created_at DESC";

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
            "SELECT p.*, c.nombre AS comercio_nombre, c.slug AS comercio_slug, c.whatsapp AS comercio_whatsapp, c.logo AS comercio_logo
             FROM productos p
             INNER JOIN comercios c ON p.comercio_id = c.id
             WHERE p.activo = 1 AND p.estado = 'disponible' AND c.activo = 1 AND p.imagen IS NOT NULL
             ORDER BY p.created_at DESC
             LIMIT ?",
            [$limite]
        );
    }

    /**
     * Toggle activo/inactivo de un producto
     */
    public static function toggleActivo(int $id): int
    {
        $db = Database::getInstance();
        $db->execute("UPDATE productos SET activo = NOT activo WHERE id = ?", [$id]);
        $row = $db->fetch("SELECT activo FROM productos WHERE id = ?", [$id]);
        return $row ? (int) $row['activo'] : 0;
    }

    /**
     * Label con icono para el estado
     */
    public static function getEstadoLabel(string $estado): string
    {
        $labels = [
            'disponible' => "\u{2705} Disponible",
            'vendido'    => "\u{1F534} Vendido",
            'reservado'  => "\u{1F7E1} Reservado",
            'agotado'    => "\u{26AB} Agotado",
        ];
        return $labels[$estado] ?? $estado;
    }

    /**
     * Label con icono para el tipo
     */
    public static function getTipoLabel(string $tipo): string
    {
        $labels = [
            'producto'  => "\u{1F4E6} Producto",
            'servicio'  => "\u{1F527} Servicio",
            'arriendo'  => "\u{1F3E0} Arriendo",
            'propiedad' => "\u{1F3E1} Propiedad",
        ];
        return $labels[$tipo] ?? $tipo;
    }

    /**
     * Solo productos disponibles y activos de un comercio
     */
    public static function findDisponiblesByComercioId(int $comercioId): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM productos WHERE comercio_id = ? AND activo = 1 AND estado = 'disponible' ORDER BY orden ASC, created_at DESC",
            [$comercioId]
        );
    }

    /**
     * Actualizar solo el estado de un producto
     */
    public static function updateEstado(int $id, string $estado): int
    {
        return Database::getInstance()->update('productos', ['estado' => $estado], 'id = ?', [$id]);
    }

    /**
     * Incrementar vistas de todos los productos activos de un comercio
     */
    public static function incrementVistas(int $comercioId): void
    {
        Database::getInstance()->execute(
            "UPDATE productos SET vistas = vistas + 1 WHERE comercio_id = ? AND activo = 1",
            [$comercioId]
        );
    }
}
