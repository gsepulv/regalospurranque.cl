<?php
namespace App\Models;

use App\Core\Database;

class NurturingLog
{
    public static function registrar(array $datos): int
    {
        return Database::getInstance()->insert('nurturing_log', $datos);
    }

    public static function getPorMensaje(int $mensajeId): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT nl.*, np.nombre AS plantilla_nombre
             FROM nurturing_log nl
             LEFT JOIN nurturing_plantillas np ON nl.plantilla_id = np.id
             WHERE nl.mensaje_id = ?
             ORDER BY nl.created_at DESC",
            [$mensajeId]
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

        $porRecordatorio = $db->fetchAll(
            "SELECT numero_recordatorio, estado, COUNT(*) as total
             FROM nurturing_log WHERE {$where}
             GROUP BY numero_recordatorio, estado
             ORDER BY numero_recordatorio",
            $params
        );

        $totalEnviados = 0;
        $totalFallidos = 0;
        $totalCancelados = 0;
        $porNumero = [];

        foreach ($porRecordatorio as $r) {
            $num = $r['numero_recordatorio'];
            if (!isset($porNumero[$num])) {
                $porNumero[$num] = ['enviado' => 0, 'fallido' => 0, 'cancelado' => 0];
            }
            $porNumero[$num][$r['estado']] = (int) $r['total'];
            if ($r['estado'] === 'enviado') $totalEnviados += (int) $r['total'];
            if ($r['estado'] === 'fallido') $totalFallidos += (int) $r['total'];
            if ($r['estado'] === 'cancelado') $totalCancelados += (int) $r['total'];
        }

        $porDia = $db->fetchAll(
            "SELECT DATE(created_at) as fecha, COUNT(*) as total
             FROM nurturing_log
             WHERE estado = 'enviado' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at) ORDER BY fecha"
        );

        $totalIntento = $totalEnviados + $totalFallidos;
        $tasaExito = $totalIntento > 0 ? round(($totalEnviados / $totalIntento) * 100, 1) : 100;

        return [
            'total_enviados'   => $totalEnviados,
            'total_fallidos'   => $totalFallidos,
            'total_cancelados' => $totalCancelados,
            'por_numero'       => $porNumero,
            'por_dia'          => $porDia,
            'tasa_exito'       => $tasaExito,
        ];
    }

    public static function getUltimos(int $limit = 20): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT nl.*, mc.nombre, mc.email, np.nombre AS plantilla_nombre
             FROM nurturing_log nl
             LEFT JOIN mensajes_contacto mc ON nl.mensaje_id = mc.id
             LEFT JOIN nurturing_plantillas np ON nl.plantilla_id = np.id
             ORDER BY nl.created_at DESC
             LIMIT {$limit}"
        );
    }

    public static function countHoy(): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as c FROM nurturing_log
             WHERE estado = 'enviado' AND DATE(created_at) = CURDATE()"
        );
        return (int) ($r['c'] ?? 0);
    }

    public static function countSemana(): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as c FROM nurturing_log
             WHERE estado = 'enviado' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"
        );
        return (int) ($r['c'] ?? 0);
    }

    public static function countMes(): int
    {
        $r = Database::getInstance()->fetch(
            "SELECT COUNT(*) as c FROM nurturing_log
             WHERE estado = 'enviado' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"
        );
        return (int) ($r['c'] ?? 0);
    }
}
