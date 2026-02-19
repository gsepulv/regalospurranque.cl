<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Banners publicitarios
 */
class Banner
{
    /**
     * Banners activos por tipo
     */
    public static function getByTipo(string $tipo, ?int $limit = null): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM banners
                WHERE activo = 1 AND tipo = ?
                AND (fecha_inicio IS NULL OR fecha_inicio <= CURDATE())
                AND (fecha_fin IS NULL OR fecha_fin >= CURDATE())
                ORDER BY orden ASC";
        $params = [$tipo];
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        return $db->fetchAll($sql, $params);
    }

    /**
     * Incrementar impresiones
     */
    public static function incrementImpresiones(int $bannerId): void
    {
        $db = Database::getInstance();
        $db->execute("UPDATE banners SET impresiones = impresiones + 1 WHERE id = ?", [$bannerId]);
    }

    /**
     * Incrementar clics
     */
    public static function incrementClicks(int $bannerId): void
    {
        $db = Database::getInstance();
        $db->execute("UPDATE banners SET clicks = clicks + 1 WHERE id = ?", [$bannerId]);
    }
}