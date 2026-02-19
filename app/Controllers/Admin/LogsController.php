<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

/**
 * Gestión del log de actividad del panel admin
 * Consultar, filtrar, exportar y limpiar registros de auditoría
 */
class LogsController extends Controller
{
    private const PER_PAGE = 50;

    /**
     * GET /admin/mantenimiento/logs
     * Listado paginado con filtros y estadísticas
     */
    public function index(): void
    {
        // --- Filtros ---
        $usuario = trim($this->request->get('usuario', ''));
        $modulo  = trim($this->request->get('modulo', ''));
        $accion  = trim($this->request->get('accion', ''));
        $desde   = trim($this->request->get('desde', ''));
        $hasta   = trim($this->request->get('hasta', ''));
        $buscar  = trim($this->request->get('buscar', ''));
        $pagina  = max(1, (int) $this->request->get('pagina', 1));

        // Construir WHERE dinámico
        $where  = ['1=1'];
        $params = [];

        if ($usuario !== '') {
            $where[]  = 'usuario_nombre = ?';
            $params[] = $usuario;
        }
        if ($modulo !== '') {
            $where[]  = 'modulo = ?';
            $params[] = $modulo;
        }
        if ($accion !== '') {
            $where[]  = 'accion = ?';
            $params[] = $accion;
        }
        if ($desde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $where[]  = 'created_at >= ?';
            $params[] = $desde . ' 00:00:00';
        }
        if ($hasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $where[]  = 'created_at <= ?';
            $params[] = $hasta . ' 23:59:59';
        }
        if ($buscar !== '') {
            $where[]  = 'detalle LIKE ?';
            $params[] = '%' . $buscar . '%';
        }

        $whereSql = implode(' AND ', $where);

        // --- Contar total ---
        $totalResult = $this->db->fetch(
            "SELECT COUNT(*) as total FROM admin_log WHERE {$whereSql}",
            $params
        );
        $total      = (int) ($totalResult['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));
        $pagina     = min($pagina, $totalPages);
        $offset     = ($pagina - 1) * self::PER_PAGE;

        // --- Registros paginados ---
        $logs = $this->db->fetchAll(
            "SELECT * FROM admin_log WHERE {$whereSql} ORDER BY created_at DESC LIMIT " . self::PER_PAGE . " OFFSET {$offset}",
            $params
        );

        // --- Estadísticas generales ---
        $totalRecords = $this->db->fetch("SELECT COUNT(*) as total FROM admin_log");
        $totalRecords = (int) ($totalRecords['total'] ?? 0);

        // Top 5 acciones
        $topAcciones = $this->db->fetchAll(
            "SELECT accion, COUNT(*) as total
             FROM admin_log
             GROUP BY accion
             ORDER BY total DESC
             LIMIT 5"
        );

        // Top 5 usuarios
        $topUsuarios = $this->db->fetchAll(
            "SELECT usuario_nombre, COUNT(*) as total
             FROM admin_log
             GROUP BY usuario_nombre
             ORDER BY total DESC
             LIMIT 5"
        );

        // Top 5 módulos
        $topModulos = $this->db->fetchAll(
            "SELECT modulo, COUNT(*) as total
             FROM admin_log
             GROUP BY modulo
             ORDER BY total DESC
             LIMIT 5"
        );

        // Actividad últimos 30 días
        $actividad30d = $this->db->fetchAll(
            "SELECT DATE(created_at) as fecha, COUNT(*) as total
             FROM admin_log
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)
             ORDER BY fecha"
        );

        // Dropdowns de filtros: usuarios y módulos distintos
        $usuarios = $this->db->fetchAll(
            "SELECT DISTINCT usuario_nombre FROM admin_log ORDER BY usuario_nombre"
        );
        $modulos = $this->db->fetchAll(
            "SELECT DISTINCT modulo FROM admin_log ORDER BY modulo"
        );

        $this->render('admin/mantenimiento/logs', [
            'title'        => 'Logs de Actividad — ' . SITE_NAME,
            'tab'          => 'logs',
            'logs'         => $logs,
            'total'        => $total,
            'pagina'       => $pagina,
            'totalPages'   => $totalPages,
            'perPage'      => self::PER_PAGE,
            'totalRecords' => $totalRecords,
            'topAcciones'  => $topAcciones,
            'topUsuarios'  => $topUsuarios,
            'topModulos'   => $topModulos,
            'actividad30d' => $actividad30d,
            'usuarios'     => $usuarios,
            'modulos'      => $modulos,
            // Filtros activos (para mantener estado)
            'filtros' => [
                'usuario' => $usuario,
                'modulo'  => $modulo,
                'accion'  => $accion,
                'desde'   => $desde,
                'hasta'   => $hasta,
                'buscar'  => $buscar,
            ],
        ]);
    }

    /**
     * GET /admin/mantenimiento/logs/{id}
     * Detalle de una entrada de log
     */
    public function show(string $id): void
    {
        $id  = (int) $id;
        $log = $this->db->fetch("SELECT * FROM admin_log WHERE id = ?", [$id]);

        if (!$log) {
            $this->redirect('/admin/mantenimiento/logs', [
                'error' => 'Registro de log no encontrado',
            ]);
            return;
        }

        $this->render('admin/mantenimiento/logs-detalle', [
            'title' => 'Detalle de Log #' . $id . ' — ' . SITE_NAME,
            'tab'   => 'logs',
            'log'   => $log,
        ]);
    }

    /**
     * GET /admin/mantenimiento/logs/exportar
     * Exportar logs filtrados a CSV (UTF-8 BOM + semicolons)
     */
    public function export(): void
    {
        // Reutilizar misma lógica de filtros
        $usuario = trim($this->request->get('usuario', ''));
        $modulo  = trim($this->request->get('modulo', ''));
        $accion  = trim($this->request->get('accion', ''));
        $desde   = trim($this->request->get('desde', ''));
        $hasta   = trim($this->request->get('hasta', ''));
        $buscar  = trim($this->request->get('buscar', ''));

        $where  = ['1=1'];
        $params = [];

        if ($usuario !== '') {
            $where[]  = 'usuario_nombre = ?';
            $params[] = $usuario;
        }
        if ($modulo !== '') {
            $where[]  = 'modulo = ?';
            $params[] = $modulo;
        }
        if ($accion !== '') {
            $where[]  = 'accion = ?';
            $params[] = $accion;
        }
        if ($desde !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $where[]  = 'created_at >= ?';
            $params[] = $desde . ' 00:00:00';
        }
        if ($hasta !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $where[]  = 'created_at <= ?';
            $params[] = $hasta . ' 23:59:59';
        }
        if ($buscar !== '') {
            $where[]  = 'detalle LIKE ?';
            $params[] = '%' . $buscar . '%';
        }

        $whereSql = implode(' AND ', $where);

        $logs = $this->db->fetchAll(
            "SELECT created_at, usuario_nombre, modulo, accion, entidad_tipo, entidad_id, detalle, ip
             FROM admin_log WHERE {$whereSql} ORDER BY created_at DESC",
            $params
        );

        $filename = 'logs_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');

        $output = fopen('php://output', 'w');

        // BOM para Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Encabezados
        fputcsv($output, ['Fecha', 'Usuario', 'Módulo', 'Acción', 'Entidad', 'ID Entidad', 'Detalle', 'IP'], ';');

        // Filas
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['created_at'],
                $log['usuario_nombre'],
                $log['modulo'],
                $log['accion'],
                $log['entidad_tipo'],
                $log['entidad_id'],
                $log['detalle'],
                $log['ip'],
            ], ';');
        }

        fclose($output);
        exit;
    }

    /**
     * POST /admin/mantenimiento/logs/limpiar
     * Eliminar registros de log antiguos
     */
    public function clean(): void
    {
        $dias = max(1, (int) $this->request->post('dias', 90));

        // Contar registros a eliminar
        $countResult = $this->db->fetch(
            "SELECT COUNT(*) as total FROM admin_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$dias]
        );
        $count = (int) ($countResult['total'] ?? 0);

        // Eliminar
        $this->db->execute(
            "DELETE FROM admin_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [$dias]
        );

        // Registrar la limpieza
        $this->log('mantenimiento', 'limpiar_logs', 'admin_log', 0, "Eliminados {$count} registros anteriores a {$dias} días");

        $this->redirect('/admin/mantenimiento/logs', [
            'success' => "Se eliminaron {$count} registros de log anteriores a {$dias} días",
        ]);
    }
}
