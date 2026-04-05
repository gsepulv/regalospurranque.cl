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
            "SELECT p.*, c.nombre AS comercio_nombre, c.slug AS comercio_slug, c.whatsapp AS comercio_whatsapp, c.logo AS comercio_logo,
                    COALESCE(pf.imagen, p.imagen) AS foto_principal
             FROM productos p
             INNER JOIN comercios c ON p.comercio_id = c.id
             LEFT JOIN producto_fotos pf ON pf.producto_id = p.id AND pf.es_principal = 1
             WHERE p.activo = 1 AND p.estado = 'disponible' AND c.activo = 1 AND (p.imagen IS NOT NULL OR pf.imagen IS NOT NULL)
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
            'inmueble'  => "\u{1F3E0} Inmueble",
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

    /**
     * Producto con datos del comercio (para share OG)
     */
    public static function findByIdWithComercio(int $id): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT p.*, c.nombre AS comercio_nombre, c.slug AS comercio_slug, c.logo AS comercio_logo, c.whatsapp AS comercio_whatsapp
             FROM productos p
             INNER JOIN comercios c ON p.comercio_id = c.id
             WHERE p.id = ?",
            [$id]
        );
    }

    /**
     * Label con icono para la operacion inmobiliaria
     */
    public static function getOperacionLabel(?string $op): string
    {
        $labels = [
            'arriendo' => "\u{1F3E0} Arriendo",
            'venta' => "\u{1F3E1} Venta",
            'permuta' => "\u{1F504} Permuta",
            'arriendo_con_opcion_compra' => "\u{1F3E0}\u{1F3E1} Arriendo c/ opci\u{00F3}n compra",
            'cesion_derechos' => "\u{1F4DD} Cesi\u{00F3}n de derechos",
        ];
        return $labels[$op] ?? ($op ?: '');
    }

    /**
     * Array de amenidades activas con iconos
     */
    public static function getAmenidades(array $prod): array
    {
        $amenidades = [];
        if (!empty($prod['amoblado'])) $amenidades[] = "\u{1FA91} Amoblado";
        if (!empty($prod['acepta_mascotas'])) $amenidades[] = "\u{1F43E} Mascotas OK";
        if (!empty($prod['tiene_lenera'])) $amenidades[] = "\u{1FAB5} Le\u{00F1}era";
        if (!empty($prod['tiene_areas_verdes'])) $amenidades[] = "\u{1F33F} \u{00C1}reas verdes";
        if (!empty($prod['tiene_calefaccion'])) {
            $tc = !empty($prod['tipo_calefaccion']) ? ' (' . $prod['tipo_calefaccion'] . ')' : '';
            $amenidades[] = "\u{1F525} Calefacci\u{00F3}n" . $tc;
        }
        if (!empty($prod['agua_potable'])) $amenidades[] = "\u{1F4A7} Agua potable";
        if (!empty($prod['alcantarillado'])) $amenidades[] = "\u{1F6B0} Alcantarillado";
        if (!empty($prod['luz_electrica'])) $amenidades[] = "\u{1F4A1} Luz";
        if (isset($prod['es_rural'])) $amenidades[] = $prod['es_rural'] ? "\u{1F33E} Rural" : "\u{1F3D9} Urbano";
        return $amenidades;
    }

    /**
     * Obtener max fotos permitidas segun plan del comercio
     */
    public static function getMaxFotos(int $comercioId): int
    {
        $row = Database::getInstance()->fetch(
            "SELECT pc.max_fotos_producto FROM comercios c
             INNER JOIN planes_config pc ON c.plan = pc.slug
             WHERE c.id = ?",
            [$comercioId]
        );
        return (int) ($row['max_fotos_producto'] ?? 2);
    }
}
