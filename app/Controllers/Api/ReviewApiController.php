<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Models\Resena;
use App\Services\Notification;

/**
 * API de resenas: crear, listar, reportar
 */
class ReviewApiController extends Controller
{
    /**
     * POST /api/reviews/create — Crear resena
     */
    public function create(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $this->json(['error' => 'Datos inválidos'], 400);
            return;
        }

        // Validar CSRF
        $csrfToken = $data['_csrf'] ?? '';
        if (empty($csrfToken) || $csrfToken !== ($_SESSION['csrf_token'] ?? '')) {
            $this->json(['error' => 'Token CSRF inválido'], 403);
            return;
        }

        // Validar campos
        $v = $this->validate($data, [
            'comercio_id'  => 'required|integer',
            'nombre'       => 'required|string|min:2|max:100',
            'email'        => 'required|email',
            'calificacion' => 'required|integer',
            'comentario'   => 'required|string|min:10|max:2000',
        ]);

        if ($v->fails()) {
            $this->json(['error' => 'Datos inválidos', 'errors' => $v->errors()], 422);
            return;
        }

        // Validar calificacion 1-5
        $calificacion = (int) $data['calificacion'];
        if ($calificacion < 1 || $calificacion > 5) {
            $this->json(['error' => 'La calificación debe ser entre 1 y 5'], 422);
            return;
        }

        // Verificar que el comercio existe
        $comercio = $this->db->fetch(
            "SELECT id FROM comercios WHERE id = ? AND activo = 1",
            [(int) $data['comercio_id']]
        );

        if (!$comercio) {
            $this->json(['error' => 'Comercio no encontrado'], 404);
            return;
        }

        // Verificar que no haya enviado resena recientemente (misma IP, mismo comercio, ultimas 24h)
        $ip = $this->request->ip();
        $reciente = $this->db->fetch(
            "SELECT id FROM resenas
             WHERE comercio_id = ? AND ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
            [(int) $data['comercio_id'], $ip]
        );

        if ($reciente) {
            $this->json(['error' => 'Ya has enviado una reseña para este comercio recientemente'], 429);
            return;
        }

        $validated = $v->validated();
        $validated['ip'] = $ip;

        $id = Resena::crear($validated);

        // Notificar a admins
        $comercioData = $this->db->fetch("SELECT * FROM comercios WHERE id = ?", [(int)$data['comercio_id']]);
        if ($comercioData) {
            Notification::nuevaResena(
                array_merge($validated, ['id' => $id]),
                $comercioData
            );
        }

        $this->json([
            'success' => true,
            'message' => 'Tu reseña ha sido enviada y está pendiente de aprobación',
            'id' => $id,
        ], 201);
    }

    /**
     * GET /api/reviews/list/{id} — Resenas aprobadas de un comercio
     */
    public function list(string $id): void
    {
        $comercioId = (int) $id;
        $page = max(1, (int) ($this->request->get('page', 1)));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $resenas = Resena::getByComercio($comercioId, 'aprobada', $perPage, $offset);
        $total = Resena::countByComercio($comercioId);
        $promedio = Resena::getPromedio($comercioId);

        $this->json([
            'data'     => $resenas,
            'total'    => $total,
            'promedio' => $promedio,
            'page'     => $page,
            'perPage'  => $perPage,
            'pages'    => max(1, (int) ceil($total / $perPage)),
        ]);
    }

    /**
     * POST /api/reviews/report — Reportar resena
     */
    public function report(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $this->json(['error' => 'Datos inválidos'], 400);
            return;
        }

        $v = $this->validate($data, [
            'resena_id' => 'required|integer',
            'motivo'    => 'required|string|min:3|max:100',
        ]);

        if ($v->fails()) {
            $this->json(['error' => 'Datos inválidos', 'errors' => $v->errors()], 422);
            return;
        }

        // Verificar que la resena existe
        $resena = $this->db->fetch(
            "SELECT id FROM resenas WHERE id = ?",
            [(int) $data['resena_id']]
        );

        if (!$resena) {
            $this->json(['error' => 'Reseña no encontrada'], 404);
            return;
        }

        $reporteData = [
            'motivo'      => $data['motivo'],
            'descripcion' => $data['descripcion'] ?? '',
            'ip'          => $this->request->ip(),
        ];

        Resena::reportar((int) $data['resena_id'], $reporteData);

        // Notificar a admins
        $resenaFull = $this->db->fetch("SELECT * FROM resenas WHERE id = ?", [(int)$data['resena_id']]);
        if ($resenaFull) {
            Notification::resenaReportada($resenaFull, $reporteData);
        }

        $this->json(['success' => true, 'message' => 'Reporte enviado correctamente']);
    }
}
