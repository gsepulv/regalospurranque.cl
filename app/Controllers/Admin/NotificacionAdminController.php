<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\Notification;

/**
 * Gestión de notificaciones por email
 * Configuración y log de envíos
 */
class NotificacionAdminController extends Controller
{
    /**
     * GET /admin/notificaciones — Configuración
     */
    public function index(): void
    {
        // Obtener configuración actual
        $config = $this->getNotifConfig();

        $this->render('admin/notificaciones/index', [
            'title'  => 'Notificaciones — ' . SITE_NAME,
            'config' => $config,
        ]);
    }

    /**
     * POST /admin/notificaciones/config — Guardar configuración
     */
    public function saveConfig(): void
    {
        $keys = [
            'notificaciones_activas',
            'email_from',
            'email_reply_to',
            'notif_nueva_resena',
            'notif_resena_aprobada',
            'notif_resena_rechazada',
            'notif_resena_respuesta',
            'notif_reporte_resena',
            'notif_nuevo_comercio',
            'notif_bienvenida_comercio',
            'notif_backup',
            'notif_error_sistema',
            'notif_resumen_semanal',
            'notif_fecha_proxima',
        ];

        $checkboxes = array_filter($keys, fn($k) => $k !== 'email_from' && $k !== 'email_reply_to');

        foreach ($keys as $key) {
            if (in_array($key, ['email_from', 'email_reply_to'])) {
                $value = trim($this->request->post($key, ''));
            } else {
                $value = isset($_POST[$key]) ? '1' : '0';
            }

            $exists = $this->db->fetch(
                "SELECT clave FROM configuracion WHERE clave = ?",
                [$key]
            );

            if ($exists) {
                $this->db->update('configuracion', ['valor' => $value], 'clave = ?', [$key]);
            } else {
                $this->db->insert('configuracion', [
                    'clave' => $key,
                    'valor' => $value,
                    'grupo' => 'notificaciones',
                ]);
            }
        }

        $this->log('notificaciones', 'configurar', 'configuracion', 0, 'Configuración de notificaciones actualizada');
        $this->back(['success' => 'Configuración de notificaciones guardada']);
    }

    /**
     * POST /admin/notificaciones/test — Enviar email de prueba
     */
    public function test(): void
    {
        $email = trim($this->request->post('test_email', ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->back(['error' => 'Ingresa un email válido para la prueba']);
            return;
        }

        $sent = Notification::test($email);

        if ($sent) {
            $this->log('notificaciones', 'test', 'notificacion', 0, "Email de prueba enviado a {$email}");
            $this->back(['success' => "Email de prueba enviado a {$email}"]);
        } else {
            $this->back(['error' => 'No se pudo enviar el email de prueba. Revisa la configuración.']);
        }
    }

    /**
     * GET /admin/notificaciones/log — Historial de notificaciones
     */
    public function logView(): void
    {
        $page    = max(1, (int) $this->request->get('page', 1));
        $estado  = $this->request->get('estado', '');
        $limit   = ADMIN_PER_PAGE;

        $where  = '1=1';
        $params = [];

        if (in_array($estado, ['enviado', 'fallido'])) {
            $where .= ' AND estado = ?';
            $params[] = $estado;
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as total FROM notificaciones_log WHERE {$where}",
            $params
        )['total'] ?? 0;

        $totalPages = max(1, (int) ceil($total / $limit));
        $page       = min($page, $totalPages);
        $offset     = ($page - 1) * $limit;

        $logs = $this->db->fetchAll(
            "SELECT * FROM notificaciones_log
             WHERE {$where}
             ORDER BY created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        // Estadísticas
        $stats = [
            'total'   => $this->db->count('notificaciones_log'),
            'enviado' => $this->db->count('notificaciones_log', "estado = 'enviado'"),
            'fallido' => $this->db->count('notificaciones_log', "estado = 'fallido'"),
        ];

        $this->render('admin/notificaciones/log', [
            'title'       => 'Log de Notificaciones — ' . SITE_NAME,
            'logs'        => $logs,
            'stats'       => $stats,
            'filters'     => ['estado' => $estado],
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
        ]);
    }

    /**
     * POST /admin/notificaciones/log/limpiar — Limpiar log antiguo
     */
    public function cleanLog(): void
    {
        $dias = max(7, (int) $this->request->post('dias', 30));

        $deleted = $this->db->execute(
            "DELETE FROM notificaciones_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$dias]
        );

        $this->log('notificaciones', 'limpiar_log', 'notificacion', 0, "{$deleted} registros eliminados (>{$dias} días)");
        $this->back(['success' => "{$deleted} registros eliminados del log"]);
    }

    /**
     * Obtener configuración de notificaciones
     */
    private function getNotifConfig(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT clave, valor FROM configuracion WHERE grupo = 'notificaciones'"
        );

        $config = [];
        foreach ($rows as $row) {
            $config[$row['clave']] = $row['valor'];
        }

        // Defaults
        $defaults = [
            'notificaciones_activas'   => '1',
            'email_from'               => '',
            'email_reply_to'           => '',
            'notif_nueva_resena'       => '1',
            'notif_resena_aprobada'    => '1',
            'notif_resena_rechazada'   => '0',
            'notif_resena_respuesta'   => '1',
            'notif_reporte_resena'     => '1',
            'notif_nuevo_comercio'     => '1',
            'notif_bienvenida_comercio'=> '1',
            'notif_backup'             => '0',
            'notif_error_sistema'      => '1',
            'notif_resumen_semanal'    => '1',
            'notif_fecha_proxima'      => '1',
        ];

        return array_merge($defaults, $config);
    }
}
