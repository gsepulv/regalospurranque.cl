<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Categorias
 */
class Categoria
{
    /**
     * Todas las categorias activas ordenadas
     */
    public static function getAll(bool $activeOnly = true): array
    {
        $db = Database::getInstance();
        $where = $activeOnly ? 'WHERE activo = 1' : '';
        return $db->fetchAll(
            "SELECT * FROM categorias {$where} ORDER BY orden ASC, nombre ASC"
        );
    }

    /**
     * Categoria por slug con conteo de comercios
     */
    public static function getBySlug(string $slug): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT cat.*,
                    (SELECT COUNT(DISTINCT cc.comercio_id)
                     FROM comercio_categoria cc
                     INNER JOIN comercios c ON cc.comercio_id = c.id AND c.activo = 1
                     WHERE cc.categoria_id = cat.id) as comercios_count
             FROM categorias cat
             WHERE cat.slug = ? AND cat.activo = 1",
            [$slug]
        );
    }

    /**
     * Categorias con conteo de comercios activos
     */
    public static function getWithComerciosCount(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT cat.*,
                    COUNT(DISTINCT c.id) as comercios_count
             FROM categorias cat
             LEFT JOIN comercio_categoria cc ON cat.id = cc.categoria_id
             LEFT JOIN comercios c ON cc.comercio_id = c.id AND c.activo = 1
             WHERE cat.activo = 1
             GROUP BY cat.id
             ORDER BY cat.orden ASC, cat.nombre ASC"
        );
    }
}
