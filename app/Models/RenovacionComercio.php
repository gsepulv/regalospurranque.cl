<?php
namespace App\Models;

use App\Core\Database;

/**
 * Modelo de Renovaciones de Comercios
 * Gestiona solicitudes de renovación/upgrade de plan
 */
class RenovacionComercio
{
    /**
     * Buscar renovación por ID con datos del comercio y usuario
     */
    public static function find(int $id): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT r.*,
                    c.nombre as comercio_nombre, c.slug as comercio_slug,
                    c.plan as comercio_plan_actual, c.plan_fin as comercio_plan_fin,
                    c.activo as comercio_activo,
                    u.nombre as usuario_nombre, u.email as usuario_email
             FROM comercio_renovaciones r
             INNER JOIN comercios c ON r.comercio_id = c.id
             LEFT JOIN admin_usuarios u ON r.usuario_id = u.id
             WHERE r.id = ?",
            [$id]
        );
    }

    /**
     * Buscar renovación pendiente por ID (guard para aprobar/rechazar)
     */
    public static function findPendiente(int $id): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT r.*,
                    c.nombre as comercio_nombre, c.slug as comercio_slug,
                    c.plan as comercio_plan_actual, c.plan_fin as comercio_plan_fin,
                    c.activo as comercio_activo,
                    u.nombre as usuario_nombre, u.email as usuario_email
             FROM comercio_renovaciones r
             INNER JOIN comercios c ON r.comercio_id = c.id
             LEFT JOIN admin_usuarios u ON r.usuario_id = u.id
             WHERE r.id = ? AND r.estado = 'pendiente'",
            [$id]
        );
    }

    /**
     * ¿Existe una solicitud pendiente para este comercio?
     */
    public static function hasPendiente(int $comercioId): bool
    {
        $count = Database::getInstance()->count(
            'comercio_renovaciones',
            "comercio_id = ? AND estado = 'pendiente'",
            [$comercioId]
        );
        return $count > 0;
    }

    /**
     * Lista paginada con filtro por estado
     */
    public static function getFiltered(?string $estado, int $limit, int $offset): array
    {
        $where = '1=1';
        $params = [];

        if ($estado && in_array($estado, ['pendiente', 'aprobada', 'rechazada'])) {
            $where = 'r.estado = ?';
            $params[] = $estado;
        }

        $params[] = $limit;
        $params[] = $offset;

        return Database::getInstance()->fetchAll(
            "SELECT r.*,
                    c.nombre as comercio_nombre, c.slug as comercio_slug,
                    u.nombre as usuario_nombre, u.email as usuario_email
             FROM comercio_renovaciones r
             INNER JOIN comercios c ON r.comercio_id = c.id
             LEFT JOIN admin_usuarios u ON r.usuario_id = u.id
             WHERE {$where}
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?",
            $params
        );
    }

    /**
     * Conteo para paginación
     */
    public static function countFiltered(?string $estado): int
    {
        if ($estado && in_array($estado, ['pendiente', 'aprobada', 'rechazada'])) {
            return Database::getInstance()->count('comercio_renovaciones', 'estado = ?', [$estado]);
        }
        return Database::getInstance()->count('comercio_renovaciones');
    }

    /**
     * Conteo por estado (para badges)
     */
    public static function countByEstado(string $estado): int
    {
        return Database::getInstance()->count('comercio_renovaciones', 'estado = ?', [$estado]);
    }

    /**
     * Historial de renovaciones de un comercio
     */
    public static function getLatestByComercio(int $comercioId, int $limit = 10): array
    {
        return Database::getInstance()->fetchAll(
            "SELECT r.*,
                    a.nombre as admin_nombre
             FROM comercio_renovaciones r
             LEFT JOIN admin_usuarios a ON r.aprobado_por = a.id
             WHERE r.comercio_id = ?
             ORDER BY r.created_at DESC
             LIMIT ?",
            [$comercioId, $limit]
        );
    }

    /**
     * Última solicitud pendiente de un comercio (para dashboard comerciante)
     */
    public static function getLatestPendienteByComercio(int $comercioId): ?array
    {
        return Database::getInstance()->fetch(
            "SELECT * FROM comercio_renovaciones
             WHERE comercio_id = ? AND estado = 'pendiente'
             ORDER BY created_at DESC LIMIT 1",
            [$comercioId]
        );
    }

    /**
     * Crear nueva solicitud
     */
    public static function create(array $data): int
    {
        return Database::getInstance()->insert('comercio_renovaciones', $data);
    }

    /**
     * Actualizar por ID
     */
    public static function updateById(int $id, array $data): int
    {
        return Database::getInstance()->update('comercio_renovaciones', $data, 'id = ?', [$id]);
    }

    /**
     * Aprobar renovación (transacción atómica)
     * Actualiza la renovación + reactiva el comercio con nuevas fechas
     * Retorna false si la renovación ya fue procesada
     */
    public static function aprobar(int $id, int $adminId, ?string $notas = null): bool
    {
        $db = Database::getInstance();
        $pdo = $db->getPDO();

        try {
            $pdo->beginTransaction();

            // Verificar que sigue pendiente (dentro de la transacción)
            $renovacion = $db->fetch(
                "SELECT r.*, c.id as cid
                 FROM comercio_renovaciones r
                 INNER JOIN comercios c ON r.comercio_id = c.id
                 WHERE r.id = ? AND r.estado = 'pendiente'
                 FOR UPDATE",
                [$id]
            );

            if (!$renovacion) {
                $pdo->rollBack();
                return false;
            }

            // Obtener duración del plan solicitado
            $planConfig = PlanConfig::findBySlug($renovacion['plan_solicitado']);
            $duracion = $planConfig ? (int)($planConfig['duracion_dias'] ?: 30) : 30;
            $precioRegular = $planConfig ? (int)$planConfig['precio_regular'] : 0;

            // 1. Actualizar la renovación
            $db->update('comercio_renovaciones', [
                'estado'      => 'aprobada',
                'aprobado_por' => $adminId,
                'notas_admin' => $notas,
            ], 'id = ?', [$id]);

            // 2. Actualizar el comercio
            $db->update('comercios', [
                'plan'        => $renovacion['plan_solicitado'],
                'plan_precio' => $precioRegular,
                'plan_inicio' => date('Y-m-d'),
                'plan_fin'    => date('Y-m-d', strtotime("+{$duracion} days")),
                'activo'      => 1,
            ], 'id = ?', [$renovacion['comercio_id']]);

            $pdo->commit();
            return true;

        } catch (\Throwable $e) {
            $pdo->rollBack();
            error_log("[RenovacionComercio::aprobar] " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rechazar renovación
     * Retorna false si ya fue procesada
     */
    public static function rechazar(int $id, int $adminId, string $motivo, ?string $notas = null): bool
    {
        $db = Database::getInstance();

        // Verificar que sigue pendiente
        $renovacion = $db->fetch(
            "SELECT id FROM comercio_renovaciones WHERE id = ? AND estado = 'pendiente'",
            [$id]
        );

        if (!$renovacion) {
            return false;
        }

        $db->update('comercio_renovaciones', [
            'estado'         => 'rechazada',
            'motivo_rechazo' => $motivo,
            'aprobado_por'   => $adminId,
            'notas_admin'    => $notas,
        ], 'id = ?', [$id]);

        return true;
    }
}
