<?php
namespace App\Models;

use App\Core\Database;

class MensajeContacto
{
    public static function create(array $data): int
    {
        return Database::getInstance()->insert('mensajes_contacto', $data);
    }

    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM mensajes_contacto WHERE id = ?", [$id]
        );
    }

    public static function getAll(int $limit, int $offset, string $where = '1=1', array $params = []): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT * FROM mensajes_contacto WHERE {$where} ORDER BY created_at DESC LIMIT {$limit} OFFSET {$offset}",
            $params
        );
    }

    public static function countAll(string $where = '1=1', array $params = []): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as total FROM mensajes_contacto WHERE {$where}", $params
        );
        return (int) ($r['total'] ?? 0);
    }

    public static function countNoLeidos(): int
    {
        return Database::getInstance()->count('mensajes_contacto', 'leido = 0');
    }

    public static function marcarLeido(int $id): void
    {
        Database::getInstance()->update('mensajes_contacto', ['leido' => 1], 'id = ?', [$id]);
    }

    public static function marcarRespondido(int $id): void
    {
        Database::getInstance()->update('mensajes_contacto', ['respondido' => 1, 'leido' => 1], 'id = ?', [$id]);
    }

    // ── Nuevos métodos para seguimiento de conversiones ──────────

    public static function getConFiltros(array $filtros = []): array
    {
        $db = Database::getInstance();
        $where = '1=1';
        $params = [];

        if (!empty($filtros['estado'])) {
            $where .= ' AND mc.estado = ?';
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $where .= ' AND mc.created_at >= ?';
            $params[] = $filtros['fecha_desde'] . ' 00:00:00';
        }

        if (!empty($filtros['fecha_hasta'])) {
            $where .= ' AND mc.created_at <= ?';
            $params[] = $filtros['fecha_hasta'] . ' 23:59:59';
        }

        if (!empty($filtros['busqueda'])) {
            $where .= ' AND (mc.nombre LIKE ? OR mc.email LIKE ? OR mc.asunto LIKE ?)';
            $term = '%' . $filtros['busqueda'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $limit  = (int) ($filtros['limit'] ?? ADMIN_PER_PAGE);
        $offset = (int) ($filtros['offset'] ?? 0);

        $rows = $db->fetchAll(
            "SELECT mc.*, c.nombre AS comercio_nombre
             FROM mensajes_contacto mc
             LEFT JOIN comercios c ON mc.comercio_id = c.id
             WHERE {$where}
             ORDER BY mc.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        $countRow = $db->fetch(
            "SELECT COUNT(*) as total FROM mensajes_contacto mc WHERE {$where}",
            $params
        );

        return [
            'data'  => $rows,
            'total' => (int) ($countRow['total'] ?? 0),
        ];
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance();

        $msg = $db->fetch(
            "SELECT mc.*, c.nombre AS comercio_nombre, c.slug AS comercio_slug
             FROM mensajes_contacto mc
             LEFT JOIN comercios c ON mc.comercio_id = c.id
             WHERE mc.id = ?",
            [$id]
        );

        if ($msg) {
            $r = $db->fetch(
                "SELECT COUNT(*) as total FROM mensajes_respuestas WHERE mensaje_id = ?",
                [$id]
            );
            $msg['total_respuestas'] = (int) ($r['total'] ?? 0);
        }

        return $msg;
    }

    public static function actualizarEstado(int $id, string $estado, array $datos = []): void
    {
        $db = Database::getInstance();
        $update = ['estado' => $estado];

        if ($estado === 'respondido' && empty($datos['respondido_at'])) {
            $update['respondido_at'] = date('Y-m-d H:i:s');
            $update['respondido'] = 1;
            $update['leido'] = 1;
        }

        if ($estado === 'convertido') {
            if (empty($datos['convertido_at'])) {
                $update['convertido_at'] = date('Y-m-d H:i:s');
            }
            if (!empty($datos['comercio_id'])) {
                $update['comercio_id'] = (int) $datos['comercio_id'];
            }
        }

        if ($estado === 'leido') {
            $update['leido'] = 1;
        }

        $db->update('mensajes_contacto', $update, 'id = ?', [$id]);
    }

    public static function guardarNota(int $id, string $nota): void
    {
        Database::getInstance()->update(
            'mensajes_contacto',
            ['notas_admin' => $nota],
            'id = ?',
            [$id]
        );
    }

    public static function getEstadisticas(?string $desde = null, ?string $hasta = null): array
    {
        $db = Database::getInstance();
        $where = '1=1';
        $params = [];

        if ($desde) {
            $where .= ' AND created_at >= ?';
            $params[] = $desde . ' 00:00:00';
        }
        if ($hasta) {
            $where .= ' AND created_at <= ?';
            $params[] = $hasta . ' 23:59:59';
        }

        // Conteo por estado
        $estados = $db->fetchAll(
            "SELECT estado, COUNT(*) as total FROM mensajes_contacto WHERE {$where} GROUP BY estado",
            $params
        );
        $porEstado = [];
        $totalGeneral = 0;
        foreach ($estados as $e) {
            $porEstado[$e['estado']] = (int) $e['total'];
            $totalGeneral += (int) $e['total'];
        }

        // Tasa de conversion
        $convertidos = $porEstado['convertido'] ?? 0;
        $tasaConversion = $totalGeneral > 0 ? round(($convertidos / $totalGeneral) * 100, 1) : 0;

        // Tiempo promedio de respuesta (en minutos)
        $avgResp = $db->fetch(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, respondido_at)) as avg_min
             FROM mensajes_contacto
             WHERE respondido_at IS NOT NULL AND {$where}",
            $params
        );
        $tiempoRespuesta = $avgResp['avg_min'] ? round((float) $avgResp['avg_min']) : null;

        // Tiempo promedio de conversion (en minutos)
        $avgConv = $db->fetch(
            "SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, convertido_at)) as avg_min
             FROM mensajes_contacto
             WHERE convertido_at IS NOT NULL AND {$where}",
            $params
        );
        $tiempoConversion = $avgConv['avg_min'] ? round((float) $avgConv['avg_min']) : null;

        // Mensajes por dia (ultimos 30 dias)
        $porDia = $db->fetchAll(
            "SELECT DATE(created_at) as fecha, COUNT(*) as total
             FROM mensajes_contacto
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)
             ORDER BY fecha ASC"
        );

        // Top 5 asuntos
        $topAsuntos = $db->fetchAll(
            "SELECT asunto, COUNT(*) as total
             FROM mensajes_contacto
             WHERE {$where}
             GROUP BY asunto
             ORDER BY total DESC
             LIMIT 5",
            $params
        );

        return [
            'total'             => $totalGeneral,
            'por_estado'        => $porEstado,
            'tasa_conversion'   => $tasaConversion,
            'tiempo_respuesta'  => $tiempoRespuesta,
            'tiempo_conversion' => $tiempoConversion,
            'por_dia'           => $porDia,
            'top_asuntos'       => $topAsuntos,
        ];
    }

    public static function detectarConversiones(): array
    {
        $db = Database::getInstance();

        // Cruzar emails de mensajes no convertidos con comercios existentes
        $matches = $db->fetchAll(
            "SELECT mc.id AS mensaje_id, mc.email, mc.nombre AS msg_nombre,
                    c.id AS comercio_id, c.nombre AS comercio_nombre
             FROM mensajes_contacto mc
             INNER JOIN comercios c ON LOWER(mc.email) = LOWER(c.email)
             WHERE mc.estado != 'convertido'
               AND mc.comercio_id IS NULL"
        );

        $conversiones = [];
        foreach ($matches as $m) {
            $db->update('mensajes_contacto', [
                'estado'       => 'convertido',
                'comercio_id'  => (int) $m['comercio_id'],
                'convertido_at'=> date('Y-m-d H:i:s'),
            ], 'id = ?', [$m['mensaje_id']]);

            $conversiones[] = $m;
        }

        return $conversiones;
    }

    public static function countPorEstado(): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT estado, COUNT(*) as total FROM mensajes_contacto GROUP BY estado"
        );
        $result = ['nuevo' => 0, 'leido' => 0, 'respondido' => 0, 'convertido' => 0, 'descartado' => 0];
        foreach ($rows as $r) {
            $result[$r['estado']] = (int) $r['total'];
        }
        $result['todos'] = array_sum($result);
        return $result;
    }

    public static function countNuevos(): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as total FROM mensajes_contacto WHERE estado = 'nuevo'"
        );
        return (int) ($r['total'] ?? 0);
    }
}
