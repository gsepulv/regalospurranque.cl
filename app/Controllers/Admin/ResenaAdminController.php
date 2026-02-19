<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Resena;
use App\Services\Notification;

/**
 * Gestión de reseñas y comentarios de usuarios
 */
class ResenaAdminController extends Controller
{
    /**
     * GET /admin/resenas — Listado con tabs, filtros y paginación
     */
    public function index(): void
    {
        $estado = $this->request->get('estado', '');
        $filters = [
            'q'            => $this->request->get('q', ''),
            'calificacion' => $this->request->get('calificacion', ''),
            'comercio_id'  => $this->request->get('comercio_id', ''),
        ];

        if ($estado !== '') {
            $filters['estado'] = $estado;
        }

        $page  = max(1, (int) $this->request->get('page', 1));
        $limit = ADMIN_PER_PAGE;
        $total = Resena::countAdmin($filters);
        $totalPages = max(1, (int) ceil($total / $limit));
        $page  = min($page, $totalPages);
        $offset = ($page - 1) * $limit;

        $resenas = Resena::getAdmin($filters, $limit, $offset);
        $counts  = Resena::countByEstado();
        $stats   = Resena::getEstadisticas();

        $this->render('admin/resenas/index', [
            'title'       => 'Reseñas — ' . SITE_NAME,
            'resenas'     => $resenas,
            'counts'      => $counts,
            'stats'       => $stats,
            'filters'     => array_merge($filters, ['estado' => $estado]),
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
        ]);
    }

    /**
     * GET /admin/resenas/{id} — Detalle de reseña
     */
    public function show(string $id): void
    {
        $id = (int) $id;
        $resena = Resena::getById($id);

        if (!$resena) {
            $this->redirect('/admin/resenas', ['error' => 'Reseña no encontrada']);
            return;
        }

        $reportes = Resena::getReportesByResena($id);

        $this->render('admin/resenas/show', [
            'title'    => 'Reseña #' . $id . ' — ' . SITE_NAME,
            'resena'   => $resena,
            'reportes' => $reportes,
        ]);
    }

    /**
     * POST /admin/resenas/aprobar/{id}
     */
    public function aprobar(string $id): void
    {
        $id = (int) $id;
        $resena = Resena::getById($id);

        if (!$resena) {
            $this->back(['error' => 'Reseña no encontrada']);
            return;
        }

        Resena::aprobar($id);
        $this->log('resenas', 'aprobar', 'resena', $id, "Reseña de {$resena['nombre_autor']} aprobada");
        Notification::resenaAprobada($resena);

        if ($this->request->isAjax()) {
            $this->json(['ok' => true, 'csrf' => $_SESSION['csrf_token']]);
            return;
        }

        $this->back(['success' => 'Reseña aprobada correctamente']);
    }

    /**
     * POST /admin/resenas/rechazar/{id}
     */
    public function rechazar(string $id): void
    {
        $id = (int) $id;
        $resena = Resena::getById($id);

        if (!$resena) {
            $this->back(['error' => 'Reseña no encontrada']);
            return;
        }

        Resena::rechazar($id);
        $this->log('resenas', 'rechazar', 'resena', $id, "Reseña de {$resena['nombre_autor']} rechazada");
        Notification::resenaRechazada($resena);

        if ($this->request->isAjax()) {
            $this->json(['ok' => true, 'csrf' => $_SESSION['csrf_token']]);
            return;
        }

        $this->back(['success' => 'Reseña rechazada correctamente']);
    }

    /**
     * POST /admin/resenas/responder/{id}
     */
    public function responder(string $id): void
    {
        $id = (int) $id;
        $resena = Resena::getById($id);

        if (!$resena) {
            $this->back(['error' => 'Reseña no encontrada']);
            return;
        }

        $respuesta = trim($this->request->post('respuesta', ''));
        if ($respuesta === '') {
            $this->back(['error' => 'La respuesta no puede estar vacía']);
            return;
        }

        Resena::responder($id, $respuesta);
        $this->log('resenas', 'responder', 'resena', $id, "Respuesta agregada a reseña de {$resena['nombre_autor']}");
        Notification::resenaRespondida($resena, $respuesta);

        $this->back(['success' => 'Respuesta guardada correctamente']);
    }

    /**
     * POST /admin/resenas/eliminar/{id}
     */
    public function eliminar(string $id): void
    {
        $id = (int) $id;
        $resena = Resena::getById($id);

        if (!$resena) {
            $this->back(['error' => 'Reseña no encontrada']);
            return;
        }

        Resena::eliminar($id);
        $this->log('resenas', 'eliminar', 'resena', $id, "Reseña de {$resena['nombre_autor']} eliminada");

        $this->redirect('/admin/resenas', ['success' => 'Reseña eliminada correctamente']);
    }

    /**
     * POST /admin/resenas/bulk — Acciones masivas
     */
    public function bulk(): void
    {
        $action = $this->request->post('bulk_action', '');
        $ids    = $this->request->post('ids', []);

        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_map('intval', array_filter($ids));

        if (empty($ids) || !in_array($action, ['aprobar', 'rechazar', 'eliminar'])) {
            $this->back(['error' => 'Selecciona reseñas y una acción válida']);
            return;
        }

        $affected = Resena::bulkAction($ids, $action);
        $this->log('resenas', 'bulk_' . $action, 'resena', 0, "{$affected} reseñas: {$action}");

        $labels = ['aprobar' => 'aprobadas', 'rechazar' => 'rechazadas', 'eliminar' => 'eliminadas'];
        $this->back(['success' => "{$affected} reseñas {$labels[$action]} correctamente"]);
    }

    /**
     * GET /admin/resenas/reportes — Listado de reportes
     */
    public function reportes(): void
    {
        $page   = max(1, (int) $this->request->get('page', 1));
        $limit  = ADMIN_PER_PAGE;
        $total  = Resena::countReportes();
        $totalPages = max(1, (int) ceil($total / $limit));
        $page   = min($page, $totalPages);
        $offset = ($page - 1) * $limit;

        $reportes = Resena::getReportes($limit, $offset);

        $this->render('admin/resenas/reportes', [
            'title'       => 'Reportes de Reseñas — ' . SITE_NAME,
            'reportes'    => $reportes,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
        ]);
    }

    /**
     * POST /admin/resenas/reportes/eliminar/{id}
     */
    public function deleteReport(string $id): void
    {
        $id = (int) $id;
        Resena::eliminarReporte($id);
        $this->log('resenas', 'eliminar_reporte', 'resena_reporte', $id, "Reporte #{$id} eliminado");

        $this->back(['success' => 'Reporte eliminado correctamente']);
    }
}
