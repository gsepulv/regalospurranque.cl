<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Resenas / Calificaciones
 */
class Resena
{
    /**
     * Resenas de un comercio paginadas (público)
     */
    public static function getByComercio(int $comercioId, string $estado = 'aprobada', int $limit = 10, int $offset = 0): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM resenas
             WHERE comercio_id = ? AND estado = ?
             ORDER BY created_at DESC
             LIMIT ? OFFSET ?",
            [$comercioId, $estado, $limit, $offset]
        );
    }

    /**
     * Calificacion promedio de un comercio
     */
    public static function getPromedio(int $comercioId): ?float
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT ROUND(AVG(calificacion), 1) as promedio
             FROM resenas
             WHERE comercio_id = ? AND estado = 'aprobada'",
            [$comercioId]
        );
        return $result['promedio'] ? (float) $result['promedio'] : null;
    }

    /**
     * Distribucion de calificaciones (5 a 1 estrellas)
     */
    public static function getDistribucion(int $comercioId): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT calificacion, COUNT(*) as total
             FROM resenas
             WHERE comercio_id = ? AND estado = 'aprobada'
             GROUP BY calificacion
             ORDER BY calificacion DESC",
            [$comercioId]
        );

        $distribucion = [];
        for ($i = 5; $i >= 1; $i--) {
            $distribucion[$i] = 0;
        }
        foreach ($rows as $row) {
            $distribucion[(int) $row['calificacion']] = (int) $row['total'];
        }

        return $distribucion;
    }

    /**
     * Total de resenas aprobadas de un comercio
     */
    public static function countByComercio(int $comercioId): int
    {
        $db = Database::getInstance();
        return $db->count('resenas', 'comercio_id = ? AND estado = ?', [$comercioId, 'aprobada']);
    }

    /**
     * Crear nueva resena (estado: pendiente)
     */
    public static function crear(array $data): int
    {
        $db = Database::getInstance();
        return $db->insert('resenas', [
            'comercio_id'   => (int) $data['comercio_id'],
            'nombre_autor'  => mb_substr($data['nombre'], 0, 100),
            'email_autor'   => mb_substr($data['email'] ?? '', 0, 150),
            'calificacion'  => max(1, min(5, (int) $data['calificacion'])),
            'comentario'    => mb_substr($data['comentario'] ?? '', 0, 2000),
            'estado'        => 'pendiente',
            'ip'            => mb_substr($data['ip'] ?? '', 0, 45),
        ]);
    }

    /**
     * Reportar una resena
     */
    public static function reportar(int $resenaId, array $data): int
    {
        $db = Database::getInstance();
        return $db->insert('resenas_reportes', [
            'resena_id'   => $resenaId,
            'motivo'      => mb_substr($data['motivo'], 0, 100),
            'descripcion' => mb_substr($data['descripcion'] ?? '', 0, 2000),
            'ip'          => mb_substr($data['ip'] ?? '', 0, 45),
        ]);
    }

    /**
     * Resenas de un usuario por email
     */
    public static function getByEmail(string $email, int $limit = 20, int $offset = 0): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT r.*, c.nombre as comercio_nombre, c.slug as comercio_slug
             FROM resenas r
             INNER JOIN comercios c ON r.comercio_id = c.id
             WHERE r.email_autor = ?
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?",
            [$email, $limit, $offset]
        );
    }

    // ══════════════════════════════════════════════════════════════
    // Métodos Admin
    // ══════════════════════════════════════════════════════════════

    /**
     * Obtener reseña por ID con datos del comercio
     */
    public static function getById(int $id): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT r.*, c.nombre as comercio_nombre, c.slug as comercio_slug
             FROM resenas r
             INNER JOIN comercios c ON r.comercio_id = c.id
             WHERE r.id = ?",
            [$id]
        );
    }

    /**
     * Listado admin con filtros y paginación
     */
    public static function getAdmin(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $db = Database::getInstance();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['estado'])) {
            $where[] = 'r.estado = ?';
            $params[] = $filters['estado'];
        }

        if (!empty($filters['calificacion'])) {
            $where[] = 'r.calificacion = ?';
            $params[] = (int) $filters['calificacion'];
        }

        if (!empty($filters['comercio_id'])) {
            $where[] = 'r.comercio_id = ?';
            $params[] = (int) $filters['comercio_id'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(r.nombre_autor LIKE ? OR r.comentario LIKE ? OR r.email_autor LIKE ?)';
            $term = '%' . $filters['q'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereStr = implode(' AND ', $where);

        $rows = $db->fetchAll(
            "SELECT r.*, c.nombre as comercio_nombre, c.slug as comercio_slug,
                    (SELECT COUNT(*) FROM resenas_reportes rr WHERE rr.resena_id = r.id) as num_reportes
             FROM resenas r
             INNER JOIN comercios c ON r.comercio_id = c.id
             WHERE {$whereStr}
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?",
            [...$params, $limit, $offset]
        );

        return $rows;
    }

    /**
     * Contar reseñas admin con filtros
     */
    public static function countAdmin(array $filters = []): int
    {
        $db = Database::getInstance();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['estado'])) {
            $where[] = 'r.estado = ?';
            $params[] = $filters['estado'];
        }

        if (!empty($filters['calificacion'])) {
            $where[] = 'r.calificacion = ?';
            $params[] = (int) $filters['calificacion'];
        }

        if (!empty($filters['comercio_id'])) {
            $where[] = 'r.comercio_id = ?';
            $params[] = (int) $filters['comercio_id'];
        }

        if (!empty($filters['q'])) {
            $where[] = '(r.nombre_autor LIKE ? OR r.comentario LIKE ? OR r.email_autor LIKE ?)';
            $term = '%' . $filters['q'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereStr = implode(' AND ', $where);

        $result = $db->fetch(
            "SELECT COUNT(*) as total FROM resenas r WHERE {$whereStr}",
            $params
        );

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Contar por estado (para tabs)
     */
    public static function countByEstado(): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT estado, COUNT(*) as total FROM resenas GROUP BY estado"
        );

        $counts = ['pendiente' => 0, 'aprobada' => 0, 'rechazada' => 0, 'total' => 0];
        foreach ($rows as $row) {
            $counts[$row['estado']] = (int) $row['total'];
            $counts['total'] += (int) $row['total'];
        }

        return $counts;
    }

    /**
     * Aprobar reseña
     */
    public static function aprobar(int $id): bool
    {
        $db = Database::getInstance();
        return $db->update('resenas', ['estado' => 'aprobada'], 'id = ?', [$id]) > 0;
    }

    /**
     * Rechazar reseña
     */
    public static function rechazar(int $id): bool
    {
        $db = Database::getInstance();
        return $db->update('resenas', ['estado' => 'rechazada'], 'id = ?', [$id]) > 0;
    }

    /**
     * Guardar respuesta del comercio
     */
    public static function responder(int $id, string $respuesta): bool
    {
        $db = Database::getInstance();
        return $db->update('resenas', [
            'respuesta_comercio' => $respuesta,
            'fecha_respuesta'    => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]) > 0;
    }

    /**
     * Eliminar reseña
     */
    public static function eliminar(int $id): bool
    {
        $db = Database::getInstance();
        return $db->delete('resenas', 'id = ?', [$id]) > 0;
    }

    /**
     * Acción masiva sobre reseñas
     */
    public static function bulkAction(array $ids, string $action): int
    {
        if (empty($ids)) return 0;

        $db = Database::getInstance();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        switch ($action) {
            case 'aprobar':
                return $db->execute(
                    "UPDATE resenas SET estado = 'aprobada' WHERE id IN ({$placeholders})",
                    $ids
                );
            case 'rechazar':
                return $db->execute(
                    "UPDATE resenas SET estado = 'rechazada' WHERE id IN ({$placeholders})",
                    $ids
                );
            case 'eliminar':
                return $db->execute(
                    "DELETE FROM resenas WHERE id IN ({$placeholders})",
                    $ids
                );
            default:
                return 0;
        }
    }

    /**
     * Obtener reportes de reseñas con datos relacionados
     */
    public static function getReportes(int $limit = 20, int $offset = 0): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT rr.*, r.nombre_autor, r.comentario, r.calificacion, r.estado as resena_estado,
                    c.nombre as comercio_nombre
             FROM resenas_reportes rr
             INNER JOIN resenas r ON rr.resena_id = r.id
             INNER JOIN comercios c ON r.comercio_id = c.id
             ORDER BY rr.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    /**
     * Contar reportes
     */
    public static function countReportes(): int
    {
        $db = Database::getInstance();
        return $db->count('resenas_reportes');
    }

    /**
     * Eliminar reporte
     */
    public static function eliminarReporte(int $id): bool
    {
        $db = Database::getInstance();
        return $db->delete('resenas_reportes', 'id = ?', [$id]) > 0;
    }

    /**
     * Obtener reportes de una reseña específica
     */
    public static function getReportesByResena(int $resenaId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM resenas_reportes WHERE resena_id = ? ORDER BY created_at DESC",
            [$resenaId]
        );
    }

    /**
     * Estadísticas generales de reseñas
     */
    public static function getEstadisticas(): array
    {
        $db = Database::getInstance();

        $counts = self::countByEstado();

        $promedio = $db->fetch(
            "SELECT ROUND(AVG(calificacion), 1) as promedio FROM resenas WHERE estado = 'aprobada'"
        );

        $hoy = $db->count('resenas', 'DATE(created_at) = CURDATE()');

        $semana = $db->count('resenas', 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');

        $reportesPendientes = $db->fetch(
            "SELECT COUNT(*) as total FROM resenas_reportes rr
             INNER JOIN resenas r ON rr.resena_id = r.id
             WHERE r.estado != 'rechazada'"
        );

        return [
            'total'              => $counts['total'],
            'pendientes'         => $counts['pendiente'],
            'aprobadas'          => $counts['aprobada'],
            'rechazadas'         => $counts['rechazada'],
            'promedio'           => $promedio['promedio'] ? (float) $promedio['promedio'] : 0,
            'hoy'                => $hoy,
            'semana'             => $semana,
            'reportes_pendientes'=> (int) ($reportesPendientes['total'] ?? 0),
        ];
    }
}
