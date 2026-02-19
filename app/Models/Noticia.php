<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Noticias
 */
class Noticia
{
    /**
     * Noticias paginadas
     */
    public static function getAll(int $limit, int $offset): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM noticias
             WHERE activo = 1
             ORDER BY fecha_publicacion DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Total de noticias activas
     */
    public static function countAll(): int
    {
        $db = Database::getInstance();
        return $db->count('noticias', 'activo = 1');
    }

    /**
     * Noticia por slug con categorias y fechas
     */
    public static function getBySlug(string $slug): ?array
    {
        $db = Database::getInstance();

        $noticia = $db->fetch(
            "SELECT * FROM noticias WHERE slug = ? AND activo = 1",
            [$slug]
        );

        if (!$noticia) {
            return null;
        }

        // Categorias relacionadas
        $noticia['categorias'] = $db->fetchAll(
            "SELECT cat.id, cat.nombre, cat.slug
             FROM categorias cat
             INNER JOIN noticia_categoria nc ON cat.id = nc.categoria_id
             WHERE nc.noticia_id = ? AND cat.activo = 1",
            [$noticia['id']]
        );

        // Fechas especiales relacionadas
        $noticia['fechas'] = $db->fetchAll(
            "SELECT fe.id, fe.nombre, fe.slug
             FROM fechas_especiales fe
             INNER JOIN noticia_fecha nf ON fe.id = nf.fecha_id
             WHERE nf.noticia_id = ? AND fe.activo = 1",
            [$noticia['id']]
        );

        return $noticia;
    }

    /**
     * Noticias destacadas para home o sidebar
     */
    public static function getDestacadas(int $limit = 3): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM noticias
             WHERE activo = 1 AND destacada = 1
             ORDER BY fecha_publicacion DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Noticias relacionadas por categorias compartidas
     */
    public static function getRelacionadas(int $noticiaId, int $limit = 3): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT DISTINCT n.*
             FROM noticias n
             INNER JOIN noticia_categoria nc ON n.id = nc.noticia_id
             INNER JOIN noticia_categoria nc2 ON nc.categoria_id = nc2.categoria_id AND nc2.noticia_id = ?
             WHERE n.activo = 1 AND n.id != ?
             ORDER BY n.fecha_publicacion DESC
             LIMIT ?",
            [$noticiaId, $noticiaId, $limit]
        );
    }
}
