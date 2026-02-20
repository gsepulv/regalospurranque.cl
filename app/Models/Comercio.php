<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Comercios
 * Consultas principales para el directorio de negocios
 */
class Comercio
{
    /**
     * Orden de prioridad de planes para queries SQL
     */
    private static string $planOrder = "FIELD(c.plan, 'sponsor','premium','basico','freemium','banner')";

    /**
     * Obtener comercio por slug con categorias y rating
     */
    public static function getBySlug(string $slug): ?array
    {
        $db = Database::getInstance();

        $comercio = $db->fetch(
            "SELECT c.*,
                    ROUND(AVG(r.calificacion), 1) as calificacion_promedio,
                    COUNT(DISTINCT r.id) as total_resenas
             FROM comercios c
             LEFT JOIN resenas r ON c.id = r.comercio_id AND r.estado = 'aprobada'
             WHERE c.slug = ? AND c.activo = 1
             GROUP BY c.id",
            [$slug]
        );

        if (!$comercio) {
            return null;
        }

        // Obtener categorias asociadas
        $comercio['categorias'] = $db->fetchAll(
            "SELECT cat.id, cat.nombre, cat.slug, cat.icono, cat.color
             FROM categorias cat
             INNER JOIN comercio_categoria cc ON cat.id = cc.categoria_id
             WHERE cc.comercio_id = ? AND cat.activo = 1
             ORDER BY cc.es_principal DESC, cat.nombre ASC",
            [$comercio['id']]
        );

        // Obtener fechas especiales asociadas
        $comercio['fechas'] = $db->fetchAll(
            "SELECT fe.id, fe.nombre, fe.slug, fe.icono,
                    cf.oferta_especial, cf.precio_desde, cf.precio_hasta
             FROM fechas_especiales fe
             INNER JOIN comercio_fecha cf ON fe.id = cf.fecha_id
             WHERE cf.comercio_id = ? AND fe.activo = 1 AND cf.activo = 1",
            [$comercio['id']]
        );

        return $comercio;
    }

    /**
     * Verificar completitud de una ficha de comercio
     * Retorna array con porcentaje y items faltantes
     */
    public static function checkCompletitud(array $comercio): array
    {
        $items = [];
        $total = 4;
        $completos = 0;

        // Descripción >= 100 caracteres
        $descOk = mb_strlen($comercio['descripcion'] ?? '') >= 100;
        $items['descripcion'] = $descOk;
        if ($descOk) $completos++;

        // Al menos 1 imagen (portada)
        $imgOk = !empty($comercio['portada']);
        $items['imagen'] = $imgOk;
        if ($imgOk) $completos++;

        // Al menos 1 dato de contacto
        $contactoOk = !empty($comercio['telefono']) || !empty($comercio['whatsapp']) || !empty($comercio['email']);
        $items['contacto'] = $contactoOk;
        if ($contactoOk) $completos++;

        // Al menos 1 categoría
        $db = Database::getInstance();
        $catCount = $db->fetch(
            "SELECT COUNT(*) as total FROM comercio_categoria WHERE comercio_id = ?",
            [(int)$comercio['id']]
        );
        $catOk = ($catCount['total'] ?? 0) > 0;
        $items['categoria'] = $catOk;
        if ($catOk) $completos++;

        return [
            'porcentaje' => (int) round(($completos / $total) * 100),
            'completa'   => $completos === $total,
            'items'      => $items,
            'faltantes'  => array_keys(array_filter($items, fn($v) => !$v)),
        ];
    }

    /**
     * Recalcular y guardar el campo calidad_ok de un comercio
     */
    public static function recalcularCalidad(int $comercioId): void
    {
        $db = Database::getInstance();
        $comercio = $db->fetch("SELECT * FROM comercios WHERE id = ?", [$comercioId]);
        if (!$comercio) return;

        $completitud = self::checkCompletitud($comercio);
        $calidad = $completitud['completa'] ? 1 : 0;

        $db->update('comercios', ['calidad_ok' => $calidad], 'id = ?', [$comercioId]);
    }

    /**
     * Comercios destacados para la home
     */
    public static function getDestacados(int $limit = 8): array
    {
        $db = Database::getInstance();

        $comercios = $db->fetchAll(
            "SELECT c.*,
                    GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') as categorias_nombres,
                    ROUND(AVG(r.calificacion), 1) as calificacion_promedio,
                    COUNT(DISTINCT r.id) as total_resenas
             FROM comercios c
             LEFT JOIN comercio_categoria cc ON c.id = cc.comercio_id
             LEFT JOIN categorias cat ON cc.categoria_id = cat.id AND cat.activo = 1
             LEFT JOIN resenas r ON c.id = r.comercio_id AND r.estado = 'aprobada'
             WHERE c.activo = 1 AND c.calidad_ok = 1 AND c.destacado = 1
             GROUP BY c.id
             ORDER BY " . self::$planOrder . ", c.nombre ASC
             LIMIT ?",
            [$limit]
        );

        return $comercios;
    }

    /**
     * Comercios por categoria con paginacion
     */
    public static function getByCategoria(int $categoriaId, int $limit, int $offset): array
    {
        $db = Database::getInstance();

        return $db->fetchAll(
            "SELECT c.*,
                    GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') as categorias_nombres,
                    ROUND(AVG(r.calificacion), 1) as calificacion_promedio,
                    COUNT(DISTINCT r.id) as total_resenas
             FROM comercios c
             INNER JOIN comercio_categoria cc ON c.id = cc.comercio_id AND cc.categoria_id = ?
             LEFT JOIN comercio_categoria cc2 ON c.id = cc2.comercio_id
             LEFT JOIN categorias cat ON cc2.categoria_id = cat.id AND cat.activo = 1
             LEFT JOIN resenas r ON c.id = r.comercio_id AND r.estado = 'aprobada'
             WHERE c.activo = 1 AND c.calidad_ok = 1
             GROUP BY c.id
             ORDER BY c.destacado DESC, " . self::$planOrder . ", c.nombre ASC
             LIMIT ? OFFSET ?",
            [$categoriaId, $limit, $offset]
        );
    }

    /**
     * Contar comercios por categoria
     */
    public static function countByCategoria(int $categoriaId): int
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(DISTINCT c.id) as total
             FROM comercios c
             INNER JOIN comercio_categoria cc ON c.id = cc.comercio_id AND cc.categoria_id = ?
             WHERE c.activo = 1 AND c.calidad_ok = 1",
            [$categoriaId]
        );
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Comercios por fecha especial con paginacion
     */
    public static function getByFecha(int $fechaId, int $limit, int $offset): array
    {
        $db = Database::getInstance();

        return $db->fetchAll(
            "SELECT c.*,
                    MAX(cf.oferta_especial) as oferta_especial,
                    MAX(cf.precio_desde) as precio_desde,
                    MAX(cf.precio_hasta) as precio_hasta,
                    GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') as categorias_nombres,
                    ROUND(AVG(r.calificacion), 1) as calificacion_promedio,
                    COUNT(DISTINCT r.id) as total_resenas
             FROM comercios c
             INNER JOIN comercio_fecha cf ON c.id = cf.comercio_id AND cf.fecha_id = ? AND cf.activo = 1
             LEFT JOIN comercio_categoria cc ON c.id = cc.comercio_id
             LEFT JOIN categorias cat ON cc.categoria_id = cat.id AND cat.activo = 1
             LEFT JOIN resenas r ON c.id = r.comercio_id AND r.estado = 'aprobada'
             WHERE c.activo = 1 AND c.calidad_ok = 1
             GROUP BY c.id
             ORDER BY c.destacado DESC, " . self::$planOrder . ", c.nombre ASC
             LIMIT ? OFFSET ?",
            [$fechaId, $limit, $offset]
        );
    }

    /**
     * Contar comercios por fecha especial
     */
    public static function countByFecha(int $fechaId): int
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT COUNT(DISTINCT c.id) as total
             FROM comercios c
             INNER JOIN comercio_fecha cf ON c.id = cf.comercio_id AND cf.fecha_id = ? AND cf.activo = 1
             WHERE c.activo = 1 AND c.calidad_ok = 1",
            [$fechaId]
        );
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Busqueda con filtros y paginacion
     */
    public static function search(array $filters, int $limit, int $offset): array
    {
        $db = Database::getInstance();
        $where = ['c.activo = 1', 'c.calidad_ok = 1'];
        $params = [];

        if (!empty($filters['query'])) {
            $where[] = '(c.nombre LIKE ? OR c.descripcion LIKE ? OR c.direccion LIKE ?)';
            $q = '%' . $filters['query'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        if (!empty($filters['categoria_id'])) {
            $where[] = 'c.id IN (SELECT comercio_id FROM comercio_categoria WHERE categoria_id = ?)';
            $params[] = (int) $filters['categoria_id'];
        }

        if (!empty($filters['fecha_id'])) {
            $where[] = 'c.id IN (SELECT comercio_id FROM comercio_fecha WHERE fecha_id = ? AND activo = 1)';
            $params[] = (int) $filters['fecha_id'];
        }

        if (!empty($filters['plan'])) {
            $where[] = 'c.plan = ?';
            $params[] = $filters['plan'];
        }

        if (!empty($filters['destacado'])) {
            $where[] = 'c.destacado = 1';
        }

        $whereSql = implode(' AND ', $where);
        $params[] = $limit;
        $params[] = $offset;

        return $db->fetchAll(
            "SELECT c.*,
                    GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') as categorias_nombres,
                    ROUND(AVG(r.calificacion), 1) as calificacion_promedio,
                    COUNT(DISTINCT r.id) as total_resenas
             FROM comercios c
             LEFT JOIN comercio_categoria cc ON c.id = cc.comercio_id
             LEFT JOIN categorias cat ON cc.categoria_id = cat.id AND cat.activo = 1
             LEFT JOIN resenas r ON c.id = r.comercio_id AND r.estado = 'aprobada'
             WHERE {$whereSql}
             GROUP BY c.id
             ORDER BY c.destacado DESC, " . self::$planOrder . ", c.nombre ASC
             LIMIT ? OFFSET ?",
            $params
        );
    }

    /**
     * Contar resultados de busqueda
     */
    public static function countSearch(array $filters): int
    {
        $db = Database::getInstance();
        $where = ['c.activo = 1', 'c.calidad_ok = 1'];
        $params = [];

        if (!empty($filters['query'])) {
            $where[] = '(c.nombre LIKE ? OR c.descripcion LIKE ? OR c.direccion LIKE ?)';
            $q = '%' . $filters['query'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        if (!empty($filters['categoria_id'])) {
            $where[] = 'c.id IN (SELECT comercio_id FROM comercio_categoria WHERE categoria_id = ?)';
            $params[] = (int) $filters['categoria_id'];
        }

        if (!empty($filters['fecha_id'])) {
            $where[] = 'c.id IN (SELECT comercio_id FROM comercio_fecha WHERE fecha_id = ? AND activo = 1)';
            $params[] = (int) $filters['fecha_id'];
        }

        if (!empty($filters['plan'])) {
            $where[] = 'c.plan = ?';
            $params[] = $filters['plan'];
        }

        if (!empty($filters['destacado'])) {
            $where[] = 'c.destacado = 1';
        }

        $whereSql = implode(' AND ', $where);
        $result = $db->fetch(
            "SELECT COUNT(DISTINCT c.id) as total FROM comercios c WHERE {$whereSql}",
            $params
        );
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Fotos del comercio ordenadas
     */
    public static function getFotos(int $comercioId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM comercio_fotos WHERE comercio_id = ? ORDER BY orden ASC",
            [$comercioId]
        );
    }

    /**
     * Horarios del comercio indexados por dia
     */
    public static function getHorarios(int $comercioId): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT dia, hora_apertura, hora_cierre, cerrado
             FROM comercio_horarios
             WHERE comercio_id = ?
             ORDER BY dia ASC",
            [$comercioId]
        );

        $horarios = [];
        foreach ($rows as $row) {
            $horarios[(int) $row['dia']] = $row;
        }
        return $horarios;
    }

    /**
     * Comercios relacionados por categorias compartidas
     */
    public static function getRelacionados(int $comercioId, int $limit = 4): array
    {
        $db = Database::getInstance();

        return $db->fetchAll(
            "SELECT c.*, GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') as categorias_nombres
             FROM comercios c
             INNER JOIN comercio_categoria cc ON c.id = cc.comercio_id
             INNER JOIN comercio_categoria cc2 ON cc.categoria_id = cc2.categoria_id AND cc2.comercio_id = ?
             LEFT JOIN comercio_categoria cc3 ON c.id = cc3.comercio_id
             LEFT JOIN categorias cat ON cc3.categoria_id = cat.id AND cat.activo = 1
             WHERE c.activo = 1 AND c.calidad_ok = 1 AND c.id != ?
             GROUP BY c.id
             ORDER BY c.destacado DESC, RAND()
             LIMIT ?",
            [$comercioId, $comercioId, $limit]
        );
    }

    /**
     * Incrementar contador de visitas
     */
    public static function incrementVisitas(int $comercioId): void
    {
        $db = Database::getInstance();
        $db->execute("UPDATE comercios SET visitas = visitas + 1 WHERE id = ?", [$comercioId]);
    }

    /**
     * Incrementar contador de clics WhatsApp
     */
    public static function incrementWhatsappClicks(int $comercioId): void
    {
        $db = Database::getInstance();
        $db->execute("UPDATE comercios SET whatsapp_clicks = whatsapp_clicks + 1 WHERE id = ?", [$comercioId]);
    }

    /**
     * Todos los comercios con coordenadas para el mapa
     */
    public static function getParaMapa(): array
    {
        $db = Database::getInstance();

        return $db->fetchAll(
            "SELECT c.id, c.nombre, c.slug, c.direccion, c.lat, c.lng, c.logo, c.telefono,
                    GROUP_CONCAT(DISTINCT cc.categoria_id) as categorias_ids
             FROM comercios c
             LEFT JOIN comercio_categoria cc ON c.id = cc.comercio_id
             WHERE c.activo = 1 AND c.calidad_ok = 1 AND c.lat IS NOT NULL AND c.lng IS NOT NULL
             GROUP BY c.id
             ORDER BY c.nombre ASC"
        );
    }
}
