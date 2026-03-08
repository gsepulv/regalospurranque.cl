<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\MensajeContacto;
use App\Models\MensajeRespuesta;
use App\Models\NurturingConfig;
use App\Models\NurturingLog;
use App\Services\Mailer;

class MensajeAdminController extends Controller
{
    /**
     * GET /admin/mensajes — Lista de mensajes con filtros y tabs por estado
     */
    public function index(): void
    {
        $page     = max(1, (int) $this->request->get('page', 1));
        $estado   = $this->request->get('estado', '');
        $busqueda = trim($this->request->get('q', ''));
        $desde    = $this->request->get('desde', '');
        $hasta    = $this->request->get('hasta', '');
        $limit    = ADMIN_PER_PAGE;

        $filtros = [
            'estado'      => $estado,
            'busqueda'    => $busqueda,
            'fecha_desde' => $desde,
            'fecha_hasta' => $hasta,
            'limit'       => $limit,
            'offset'      => 0,
        ];

        // Contar para paginacion
        $result     = MensajeContacto::getConFiltros($filtros);
        $total      = $result['total'];
        $totalPages = max(1, (int) ceil($total / $limit));
        $page       = min($page, $totalPages);
        $filtros['offset'] = ($page - 1) * $limit;

        $result   = MensajeContacto::getConFiltros($filtros);
        $mensajes = $result['data'];

        $contadores = MensajeContacto::countPorEstado();
        $maxRec = NurturingConfig::getMaxRecordatorios();

        $this->render('admin/mensajes/index', [
            'title'       => 'Seguimiento de Mensajes — ' . SITE_NAME,
            'mensajes'    => $mensajes,
            'contadores'  => $contadores,
            'filters'     => [
                'estado' => $estado,
                'q'      => $busqueda,
                'desde'  => $desde,
                'hasta'  => $hasta,
            ],
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
            'maxRec'      => $maxRec,
            'baseUrl'     => '/admin/mensajes',
            'queryParams' => array_filter([
                'estado' => $estado,
                'q'      => $busqueda,
                'desde'  => $desde,
                'hasta'  => $hasta,
            ]),
        ]);
    }

    /**
     * GET /admin/mensajes/dashboard — Estadisticas de conversion
     */
    public function dashboard(): void
    {
        $desde = $this->request->get('desde', '');
        $hasta = $this->request->get('hasta', '');

        $stats = MensajeContacto::getEstadisticas($desde ?: null, $hasta ?: null);
        $contadores = MensajeContacto::countPorEstado();

        // Conversiones recientes
        $recientes = MensajeContacto::getConFiltros([
            'estado' => 'convertido',
            'limit'  => 10,
            'offset' => 0,
        ]);

        // Nurturing stats for dashboard
        $totalMensajes = $stats['total'] ?: 1;
        $desuscritosCount = $this->db->fetch(
            "SELECT COUNT(*) as c FROM mensajes_contacto WHERE desuscrito = 1"
        );
        $nurturingStats = [
            'enviados_semana'     => NurturingLog::countSemana(),
            'tasa_desuscripcion'  => round(((int) ($desuscritosCount['c'] ?? 0) / $totalMensajes) * 100, 1),
        ];

        $this->render('admin/mensajes/dashboard', [
            'title'          => 'Dashboard de Conversiones — ' . SITE_NAME,
            'stats'          => $stats,
            'contadores'     => $contadores,
            'recientes'      => $recientes['data'],
            'nurturingStats' => $nurturingStats,
            'filters'        => ['desde' => $desde, 'hasta' => $hasta],
        ]);
    }

    /**
     * GET /admin/mensajes/{id} — Ficha detallada del contacto
     */
    public function ver(int $id): void
    {
        $mensaje = MensajeContacto::getById($id);
        if (!$mensaje) {
            $this->redirect('/admin/mensajes', ['error' => 'Mensaje no encontrado']);
            return;
        }

        // Marcar como leido si es nuevo
        if ($mensaje['estado'] === 'nuevo') {
            MensajeContacto::actualizarEstado($id, 'leido');
            $mensaje['estado'] = 'leido';
            $this->log('mensajes', 'leer', 'mensaje_contacto', $id, "Mensaje leido: {$mensaje['email']}");
        }

        $respuestas = MensajeRespuesta::getPorMensaje($id);

        // Buscar comercios para selector de conversion
        $comercios = $this->db->fetchAll(
            "SELECT id, nombre FROM comercios WHERE activo = 1 ORDER BY nombre ASC"
        );

        $nurturingLog = NurturingLog::getPorMensaje($id);
        $maxRec = NurturingConfig::getMaxRecordatorios();

        $this->render('admin/mensajes/ver', [
            'title'        => 'Mensaje #' . $id . ' — ' . SITE_NAME,
            'mensaje'      => $mensaje,
            'respuestas'   => $respuestas,
            'comercios'    => $comercios,
            'nurturingLog' => $nurturingLog,
            'maxRec'       => $maxRec,
        ]);
    }

    /**
     * POST /admin/mensajes/{id}/responder — Enviar email manual
     */
    public function responder(int $id): void
    {
        $mensaje = MensajeContacto::find($id);
        if (!$mensaje) {
            $this->redirect('/admin/mensajes', ['error' => 'Mensaje no encontrado']);
            return;
        }

        $asunto    = trim($_POST['asunto'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');

        if (empty($asunto) || empty($contenido)) {
            $this->back(['error' => 'Asunto y contenido son obligatorios']);
            return;
        }

        // Enviar email
        $mailer = new Mailer();
        $htmlContent = '<p>Hola ' . htmlspecialchars($mensaje['nombre']) . ',</p>'
            . '<div>' . nl2br(htmlspecialchars($contenido)) . '</div>'
            . '<hr><p style="color:#999;font-size:12px;">En respuesta a: '
            . htmlspecialchars($mensaje['asunto']) . '</p>';

        $enviado = $mailer->sendHtml(
            $mensaje['email'],
            $asunto,
            $htmlContent,
            'respuesta-contacto'
        );

        // Registrar respuesta
        MensajeRespuesta::crear([
            'mensaje_id'    => $id,
            'tipo'          => 'respuesta_manual',
            'asunto'        => $asunto,
            'contenido'     => $contenido,
            'email_destino' => $mensaje['email'],
            'enviado_por'   => $_SESSION['admin']['nombre'] ?? 'Admin',
        ]);

        // Actualizar estado si era nuevo o leido
        if (in_array($mensaje['estado'], ['nuevo', 'leido'])) {
            MensajeContacto::actualizarEstado($id, 'respondido');
        }

        $this->log('mensajes', 'responder', 'mensaje_contacto', $id,
            "Respuesta enviada a {$mensaje['email']}: {$asunto}");

        $this->redirect('/admin/mensajes/' . $id, ['success' => 'Respuesta enviada correctamente']);
    }

    /**
     * POST /admin/mensajes/{id}/estado — Cambiar estado
     */
    public function actualizarEstado(int $id): void
    {
        $mensaje = MensajeContacto::find($id);
        if (!$mensaje) {
            $this->redirect('/admin/mensajes', ['error' => 'Mensaje no encontrado']);
            return;
        }

        $estado = $_POST['estado'] ?? '';
        $permitidos = ['nuevo', 'leido', 'respondido', 'convertido', 'descartado'];
        if (!in_array($estado, $permitidos)) {
            $this->back(['error' => 'Estado no valido']);
            return;
        }

        $datos = [];
        if ($estado === 'convertido') {
            $comercioId = (int) ($_POST['comercio_id'] ?? 0);
            if ($comercioId > 0) {
                $datos['comercio_id'] = $comercioId;
            }
        }

        MensajeContacto::actualizarEstado($id, $estado, $datos);

        $this->log('mensajes', 'cambiar_estado', 'mensaje_contacto', $id,
            "Estado cambiado a '{$estado}' para {$mensaje['email']}");

        $this->redirect('/admin/mensajes/' . $id, ['success' => "Estado actualizado a: {$estado}"]);
    }

    /**
     * POST /admin/mensajes/{id}/nota — Guardar nota admin
     */
    public function guardarNota(int $id): void
    {
        $mensaje = MensajeContacto::find($id);
        if (!$mensaje) {
            $this->redirect('/admin/mensajes', ['error' => 'Mensaje no encontrado']);
            return;
        }

        $nota = trim($_POST['notas_admin'] ?? '');
        MensajeContacto::guardarNota($id, $nota);

        $this->log('mensajes', 'nota', 'mensaje_contacto', $id,
            "Nota actualizada para {$mensaje['email']}");

        $this->redirect('/admin/mensajes/' . $id, ['success' => 'Nota guardada']);
    }

    /**
     * POST /admin/mensajes/detectar — Auto-deteccion de conversiones
     */
    public function detectar(): void
    {
        $conversiones = MensajeContacto::detectarConversiones();
        $total = count($conversiones);

        if ($total > 0) {
            $nombres = array_map(fn($c) => $c['comercio_nombre'], $conversiones);
            $this->log('mensajes', 'detectar_conversiones', 'mensaje_contacto', 0,
                "Detectadas {$total} conversiones: " . implode(', ', $nombres));

            $this->redirect('/admin/mensajes', [
                'success' => "Se detectaron {$total} conversiones automaticamente"
            ]);
        } else {
            $this->redirect('/admin/mensajes', [
                'info' => 'No se detectaron nuevas conversiones'
            ]);
        }
    }
}
