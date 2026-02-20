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

    // ══════════════════════════════════════════════════════════════
    // CRUD y helpers admin
    // ══════════════════════════════════════════════════════════════

    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch("SELECT * FROM noticias WHERE id = ?", [$id]);
    }

    public static function create(array $data): int
    {
        return Database::getInstance()->insert('noticias', $data);
    }

    public static function updateById(int $id, array $data): int
    {
        return Database::getInstance()->update('noticias', $data, 'id = ?', [$id]);
    }

    public static function deleteById(int $id): int
    {
        return Database::getInstance()->delete('noticias', 'id = ?', [$id]);
    }

    public static function countActive(): int
    {
        return Database::getInstance()->count('noticias', 'activo = 1');
    }

    public static function getAdminFiltered(string $where, array $params, int $limit, int $offset): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT n.* FROM noticias n WHERE {$where} ORDER BY n.fecha_publicacion DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public static function countAdminFiltered(string $where, array $params): int
    {
        $r = Database::getInstance()->fetch("SELECT COUNT(*) as total FROM noticias n WHERE {$where}", $params);
        return (int) ($r['total'] ?? 0);
    }

    public static function syncCategorias(int $noticiaId, array $categoriaIds): void
    {
        $db = Database::getInstance();
        $db->delete('noticia_categoria', 'noticia_id = ?', [$noticiaId]);
        foreach ($categoriaIds as $catId) {
            $db->insert('noticia_categoria', [
                'noticia_id'   => $noticiaId,
                'categoria_id' => (int) $catId,
            ]);
        }
    }

    public static function syncFechas(int $noticiaId, array $fechaIds): void
    {
        $db = Database::getInstance();
        $db->delete('noticia_fecha', 'noticia_id = ?', [$noticiaId]);
        foreach ($fechaIds as $fechaId) {
            $db->insert('noticia_fecha', [
                'noticia_id' => $noticiaId,
                'fecha_id'   => (int) $fechaId,
            ]);
        }
    }

    public static function getCategoriaIds(int $noticiaId): array
    {
        $rows = Database::getInstance()->fetchAll(
            "SELECT categoria_id FROM noticia_categoria WHERE noticia_id = ?", [$noticiaId]
        );
        return array_column($rows, 'categoria_id');
    }

    public static function getFechaIds(int $noticiaId): array
    {
        $rows = Database::getInstance()->fetchAll(
            "SELECT fecha_id FROM noticia_fecha WHERE noticia_id = ?", [$noticiaId]
        );
        return array_column($rows, 'fecha_id');
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
