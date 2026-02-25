<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Comercio;
use App\Models\PlanConfig;
use App\Models\RenovacionComercio;
use App\Services\Notification;

/**
 * Gestión de solicitudes de renovación de planes
 * Admin revisa, aprueba o rechaza solicitudes de comerciantes
 */
class RenovacionAdminController extends Controller
{
    /**
     * Listado de renovaciones con filtro por estado
     */
    public function index(): void
    {
        $estado = $this->request->get('estado', 'pendiente');
        $filtroEstado = in_array($estado, ['pendiente', 'aprobada', 'rechazada']) ? $estado : null;

        $page = max(1, (int)($this->request->get('page', 1)));
        $limit = ADMIN_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $renovaciones = RenovacionComercio::getFiltered($filtroEstado, $limit, $offset);
        $total = RenovacionComercio::countFiltered($filtroEstado);

        $conteos = [
            'pendiente'  => RenovacionComercio::countByEstado('pendiente'),
            'aprobada'   => RenovacionComercio::countByEstado('aprobada'),
            'rechazada'  => RenovacionComercio::countByEstado('rechazada'),
        ];

        $this->render('admin/renovaciones/index', [
            'title'        => 'Renovaciones — ' . SITE_NAME,
            'renovaciones' => $renovaciones,
            'conteos'      => $conteos,
            'estadoActual' => $estado,
            'currentPage'  => $page,
            'totalPages'   => max(1, ceil($total / $limit)),
            'total'        => $total,
        ]);
    }

    /**
     * Ver detalle de una solicitud
     */
    public function show(string $id): void
    {
        $renovacion = RenovacionComercio::find((int)$id);

        if (!$renovacion) {
            $this->redirect('/admin/renovaciones', ['error' => 'Solicitud no encontrada.']);
            return;
        }

        $comercio = Comercio::find($renovacion['comercio_id']);
        $planActual = PlanConfig::findBySlug($renovacion['plan_actual']);
        $planSolicitado = PlanConfig::findBySlug($renovacion['plan_solicitado']);
        $historial = RenovacionComercio::getLatestByComercio($renovacion['comercio_id']);

        $this->render('admin/renovaciones/show', [
            'title'          => 'Revisar renovación — ' . SITE_NAME,
            'renovacion'     => $renovacion,
            'comercio'       => $comercio,
            'planActual'     => $planActual,
            'planSolicitado' => $planSolicitado,
            'historial'      => $historial,
        ]);
    }

    /**
     * Aprobar solicitud de renovación
     */
    public function aprobar(string $id): void
    {
        $id = (int)$id;
        $renovacion = RenovacionComercio::findPendiente($id);

        if (!$renovacion) {
            $this->redirect('/admin/renovaciones', ['error' => 'Solicitud no encontrada o ya procesada.']);
            return;
        }

        $notas = trim($_POST['notas'] ?? '');
        $adminId = \App\Services\Auth::id();

        $result = RenovacionComercio::aprobar($id, $adminId, $notas ?: null);

        if ($result) {
            $this->log('renovaciones', 'aprobar', 'renovacion', $id,
                "Renovación aprobada: comercio ID {$renovacion['comercio_id']}, plan {$renovacion['plan_solicitado']}");

            // Recargar datos frescos para la notificación
            $renovacionFresh = RenovacionComercio::find($id);
            if ($renovacionFresh) {
                Notification::renovacionAprobada($renovacionFresh);
            }

            $this->redirect('/admin/renovaciones', ['success' => "Renovación aprobada. El comercio \"{$renovacion['comercio_nombre']}\" ha sido reactivado."]);
        } else {
            $this->redirect('/admin/renovaciones', ['error' => 'No se pudo aprobar. La solicitud ya fue procesada.']);
        }
    }

    /**
     * Rechazar solicitud de renovación
     */
    public function rechazar(string $id): void
    {
        $id = (int)$id;
        $renovacion = RenovacionComercio::findPendiente($id);

        if (!$renovacion) {
            $this->redirect('/admin/renovaciones', ['error' => 'Solicitud no encontrada o ya procesada.']);
            return;
        }

        $motivo = trim($_POST['motivo'] ?? '');
        if (mb_strlen($motivo) < 10) {
            $this->redirect("/admin/renovaciones/ver/{$id}", ['error' => 'El motivo de rechazo debe tener al menos 10 caracteres.']);
            return;
        }

        $notas = trim($_POST['notas'] ?? '');
        $adminId = \App\Services\Auth::id();

        $result = RenovacionComercio::rechazar($id, $adminId, $motivo, $notas ?: null);

        if ($result) {
            $this->log('renovaciones', 'rechazar', 'renovacion', $id,
                "Renovación rechazada: comercio ID {$renovacion['comercio_id']}, motivo: {$motivo}");

            $renovacionFresh = RenovacionComercio::find($id);
            if ($renovacionFresh) {
                Notification::renovacionRechazada($renovacionFresh);
            }

            $this->redirect('/admin/renovaciones', ['success' => "Solicitud rechazada. Se ha notificado al comerciante."]);
        } else {
            $this->redirect('/admin/renovaciones', ['error' => 'No se pudo rechazar. La solicitud ya fue procesada.']);
        }
    }
}
