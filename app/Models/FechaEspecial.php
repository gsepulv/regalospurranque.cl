<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Fechas Especiales
 */
class FechaEspecial
{
    /**
     * Todas las fechas especiales activas
     */
    public static function getAll(bool $activeOnly = true): array
    {
        $db = Database::getInstance();
        $where = $activeOnly ? 'WHERE activo = 1' : '';
        return $db->fetchAll(
            "SELECT * FROM fechas_especiales {$where} ORDER BY fecha_inicio ASC, nombre ASC"
        );
    }

    /**
     * Fechas especiales activas por tipo con conteo de comercios
     */
    public static function getAllByTipo(string $tipo): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT fe.*,
                    (SELECT COUNT(DISTINCT cf.comercio_id)
                     FROM comercio_fecha cf
                     INNER JOIN comercios c ON cf.comercio_id = c.id AND c.activo = 1
                     WHERE cf.fecha_id = fe.id AND cf.activo = 1) as comercios_count
             FROM fechas_especiales fe
             WHERE fe.activo = 1 AND fe.tipo = ?
             ORDER BY fe.nombre ASC",
            [$tipo]
        );
    }

    /**
     * Fecha especial por slug con conteo de comercios
     */
    public static function getBySlug(string $slug): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT fe.*,
                    (SELECT COUNT(DISTINCT cf.comercio_id)
                     FROM comercio_fecha cf
                     INNER JOIN comercios c ON cf.comercio_id = c.id AND c.activo = 1
                     WHERE cf.fecha_id = fe.id AND cf.activo = 1) as comercios_count
             FROM fechas_especiales fe
             WHERE fe.slug = ? AND fe.activo = 1",
            [$slug]
        );
    }

    /**
     * Fechas activas en el periodo actual (o recurrentes)
     */
    public static function getActivas(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM fechas_especiales
             WHERE activo = 1
             AND (
                 recurrente = 1
                 OR (fecha_inicio IS NULL AND fecha_fin IS NULL)
                 OR (fecha_inicio <= CURDATE() AND (fecha_fin IS NULL OR fecha_fin >= CURDATE()))
                 OR (fecha_inicio IS NULL AND fecha_fin >= CURDATE())
             )
             ORDER BY fecha_inicio ASC, nombre ASC"
        );
    }

    /**
     * La proxima fecha especial con fecha_inicio futura (para countdown del hero)
     */
    public static function getProximaConFecha(): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM fechas_especiales
             WHERE activo = 1
             AND fecha_inicio IS NOT NULL
             AND fecha_inicio >= CURDATE()
             ORDER BY fecha_inicio ASC
             LIMIT 1"
        );
    }

    /**
     * Proximas fechas de calendario para sidebar/home
     */
    public static function getProximas(int $limit = 4): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM fechas_especiales
             WHERE activo = 1 AND tipo = 'calendario'
             AND (
                 (fecha_inicio IS NOT NULL AND fecha_inicio >= CURDATE())
                 OR (fecha_inicio <= CURDATE() AND fecha_fin >= CURDATE())
                 OR recurrente = 1
             )
             ORDER BY
                 CASE WHEN fecha_inicio >= CURDATE() THEN fecha_inicio
                      ELSE '9999-12-31' END ASC,
                 nombre ASC
             LIMIT ?",
            [$limit]
        );
    }
}
