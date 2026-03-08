<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\NurturingConfig;
use App\Models\NurturingPlantilla;
use App\Models\NurturingLog;
use App\Models\MensajeContacto;
use App\Models\MensajeRespuesta;
use App\Services\Mailer;

class NurturingAdminController extends Controller
{
    // ── DASHBOARD ────────────────────────────────────────────

    public function dashboard(): void
    {
        $activo = NurturingConfig::isServicioActivo();
        $maxRec = NurturingConfig::getMaxRecordatorios();

        $enCola = $this->db->fetch(
            "SELECT COUNT(*) as c FROM mensajes_contacto
             WHERE proximo_recordatorio_at IS NOT NULL
               AND desuscrito = 0 AND nurturing_pausado = 0
               AND recordatorios_enviados < ?",
            [$maxRec]
        );

        $desuscritos = $this->db->fetch(
            "SELECT COUNT(*) as c FROM mensajes_contacto WHERE desuscrito = 1"
        );

        $proximos = $this->db->fetchAll(
            "SELECT mc.id, mc.nombre, mc.email, mc.recordatorios_enviados,
                    mc.proximo_recordatorio_at, mc.nurturing_pausado
             FROM mensajes_contacto mc
             WHERE mc.proximo_recordatorio_at IS NOT NULL
               AND mc.desuscrito = 0 AND mc.nurturing_pausado = 0
               AND mc.recordatorios_enviados < ?
             ORDER BY mc.proximo_recordatorio_at ASC LIMIT 20",
            [$maxRec]
        );

        $ultimosEnvios = NurturingLog::getUltimos(20);
        $stats = NurturingLog::getEstadisticas();

        $this->render('admin/nurturing/dashboard', [
            'title'        => 'Nurturing — ' . SITE_NAME,
            'activo'       => $activo,
            'maxRec'       => $maxRec,
            'enCola'       => (int) ($enCola['c'] ?? 0),
            'desuscritos'  => (int) ($desuscritos['c'] ?? 0),
            'enviadosHoy'  => NurturingLog::countHoy(),
            'enviadosSem'  => NurturingLog::countSemana(),
            'enviadosMes'  => NurturingLog::countMes(),
            'proximos'     => $proximos,
            'ultimos'      => $ultimosEnvios,
            'stats'        => $stats,
        ]);
    }

    // ── TOGGLE SERVICIO ──────────────────────────────────────

    public function toggleServicio(): void
    {
        $activo = NurturingConfig::isServicioActivo();
        NurturingConfig::set('servicio_activo', $activo ? '0' : '1');
        $nuevo = $activo ? 'desactivado' : 'activado';

        $this->log('nurturing', 'toggle_servicio', 'nurturing_config', 0,
            "Servicio de recordatorios {$nuevo}");

        $this->redirect('/admin/nurturing', [
            'success' => "Servicio de recordatorios {$nuevo}"
        ]);
    }

    // ── CONFIGURACION ────────────────────────────────────────

    public function configuracion(): void
    {
        $config = NurturingConfig::getAll();

        $this->render('admin/nurturing/configuracion', [
            'title'  => 'Configuracion Nurturing — ' . SITE_NAME,
            'config' => $config,
        ]);
    }

    public function guardarConfiguracion(): void
    {
        $campos = [
            'servicio_activo'        => $_POST['servicio_activo'] ?? '0',
            'max_recordatorios'      => max(1, min(10, (int) ($_POST['max_recordatorios'] ?? 4))),
            'intervalo_dias'         => max(1, min(30, (int) ($_POST['intervalo_dias'] ?? 7))),
            'hora_envio'             => $_POST['hora_envio'] ?? '10:00',
            'nombre_remitente'       => trim($_POST['nombre_remitente'] ?? ''),
            'email_remitente'        => trim($_POST['email_remitente'] ?? ''),
            'solo_con_instrucciones' => $_POST['solo_con_instrucciones'] ?? '0',
            'estados_excluidos'      => trim($_POST['estados_excluidos'] ?? ''),
            'dias_espera_primera'    => max(1, min(30, (int) ($_POST['dias_espera_primera'] ?? 7))),
            'desuscripcion_activa'   => $_POST['desuscripcion_activa'] ?? '0',
            'url_desuscripcion'      => trim($_POST['url_desuscripcion'] ?? ''),
            'texto_desuscripcion'    => trim($_POST['texto_desuscripcion'] ?? ''),
        ];

        $oldMax = NurturingConfig::getMaxRecordatorios();
        $newMax = (int) $campos['max_recordatorios'];

        NurturingConfig::setMultiple($campos);

        // Si se reduce max_recordatorios, cancelar programados que excedan
        if ($newMax < $oldMax) {
            $this->db->execute(
                "UPDATE mensajes_contacto
                 SET proximo_recordatorio_at = NULL
                 WHERE recordatorios_enviados >= ?
                   AND proximo_recordatorio_at IS NOT NULL",
                [$newMax]
            );
        }

        $this->log('nurturing', 'configuracion', 'nurturing_config', 0,
            'Configuracion de nurturing actualizada');

        $this->redirect('/admin/nurturing/configuracion', [
            'success' => 'Configuracion guardada correctamente'
        ]);
    }

    // ── PLANTILLAS ───────────────────────────────────────────

    public function plantillas(): void
    {
        $plantillas = NurturingPlantilla::getAll();
        $maxRec = NurturingConfig::getMaxRecordatorios();

        $this->render('admin/nurturing/plantillas/index', [
            'title'      => 'Plantillas Nurturing — ' . SITE_NAME,
            'plantillas' => $plantillas,
            'maxRec'     => $maxRec,
        ]);
    }

    public function crearPlantilla(): void
    {
        $maxRec = NurturingConfig::getMaxRecordatorios();
        $total = NurturingPlantilla::count();

        if ($total >= $maxRec) {
            $this->redirect('/admin/nurturing/plantillas', [
                'error' => "Ya hay {$total} plantillas (maximo: {$maxRec})"
            ]);
            return;
        }

        $this->render('admin/nurturing/plantillas/form', [
            'title'     => 'Nueva Plantilla — ' . SITE_NAME,
            'plantilla' => null,
        ]);
    }

    public function guardarPlantilla(): void
    {
        $datos = [
            'nombre'          => trim($_POST['nombre'] ?? ''),
            'tono'            => trim($_POST['tono'] ?? 'amigable'),
            'asunto'          => trim($_POST['asunto'] ?? ''),
            'contenido_html'  => $_POST['contenido_html'] ?? '',
            'contenido_texto' => $_POST['contenido_texto'] ?? '',
        ];

        if (empty($datos['nombre']) || empty($datos['asunto']) || empty($datos['contenido_html'])) {
            $this->back(['error' => 'Nombre, asunto y contenido son obligatorios', 'old' => $_POST]);
            return;
        }

        $id = NurturingPlantilla::crear($datos);

        $this->log('nurturing', 'crear_plantilla', 'nurturing_plantilla', $id,
            "Plantilla creada: {$datos['nombre']}");

        $this->redirect('/admin/nurturing/plantillas', [
            'success' => 'Plantilla creada correctamente'
        ]);
    }

    public function editarPlantilla(int $id): void
    {
        $plantilla = NurturingPlantilla::getById($id);
        if (!$plantilla) {
            $this->redirect('/admin/nurturing/plantillas', ['error' => 'Plantilla no encontrada']);
            return;
        }

        $this->render('admin/nurturing/plantillas/form', [
            'title'     => 'Editar Plantilla — ' . SITE_NAME,
            'plantilla' => $plantilla,
        ]);
    }

    public function actualizarPlantilla(int $id): void
    {
        $plantilla = NurturingPlantilla::getById($id);
        if (!$plantilla) {
            $this->redirect('/admin/nurturing/plantillas', ['error' => 'Plantilla no encontrada']);
            return;
        }

        $datos = [
            'nombre'          => trim($_POST['nombre'] ?? ''),
            'tono'            => trim($_POST['tono'] ?? 'amigable'),
            'asunto'          => trim($_POST['asunto'] ?? ''),
            'contenido_html'  => $_POST['contenido_html'] ?? '',
            'contenido_texto' => $_POST['contenido_texto'] ?? '',
        ];

        if (empty($datos['nombre']) || empty($datos['asunto']) || empty($datos['contenido_html'])) {
            $this->back(['error' => 'Nombre, asunto y contenido son obligatorios']);
            return;
        }

        NurturingPlantilla::actualizar($id, $datos);

        $this->log('nurturing', 'editar_plantilla', 'nurturing_plantilla', $id,
            "Plantilla editada: {$datos['nombre']}");

        $this->redirect('/admin/nurturing/plantillas', [
            'success' => 'Plantilla actualizada correctamente'
        ]);
    }

    public function eliminarPlantilla(int $id): void
    {
        $plantilla = NurturingPlantilla::getById($id);
        if (!$plantilla) {
            $this->redirect('/admin/nurturing/plantillas', ['error' => 'Plantilla no encontrada']);
            return;
        }

        if (!NurturingPlantilla::eliminar($id)) {
            $this->redirect('/admin/nurturing/plantillas', [
                'error' => 'No se puede eliminar la unica plantilla'
            ]);
            return;
        }

        $this->log('nurturing', 'eliminar_plantilla', 'nurturing_plantilla', $id,
            "Plantilla eliminada: {$plantilla['nombre']}");

        $this->redirect('/admin/nurturing/plantillas', [
            'success' => 'Plantilla eliminada'
        ]);
    }

    public function previewPlantilla(int $id): void
    {
        $html = NurturingPlantilla::preview($id);
        if ($html === null) {
            $this->json(['error' => 'Plantilla no encontrada'], 404);
            return;
        }

        $desuscripcionActiva = (bool) (int) NurturingConfig::get('desuscripcion_activa', '1');
        $textoDesuscripcion = NurturingConfig::get('texto_desuscripcion', 'Si no deseas recibir mas correos, haz clic aqui');

        if ($desuscripcionActiva) {
            $html .= '<hr style="margin:20px 0;border:none;border-top:1px solid #e2e8f0;">'
                . '<p style="text-align:center;font-size:12px;color:#999;">'
                . '<a href="' . SITE_URL . '/desuscribir/ejemplo-token" style="color:#999;">'
                . htmlspecialchars($textoDesuscripcion) . '</a></p>';
        }

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    public function reordenarPlantillas(): void
    {
        $ids = json_decode(file_get_contents('php://input'), true);
        if (!is_array($ids)) {
            $this->json(['error' => 'Datos invalidos'], 400);
            return;
        }

        NurturingPlantilla::reordenar($ids);
        $this->json(['ok' => true]);
    }

    public function togglePlantilla(int $id): void
    {
        NurturingPlantilla::toggleActivo($id);
        $this->json(['ok' => true]);
    }

    // ── CONTACTOS ────────────────────────────────────────────

    public function contactos(): void
    {
        $filtro = $this->request->get('filtro', 'todos');
        $page   = max(1, (int) $this->request->get('page', 1));
        $limit  = ADMIN_PER_PAGE;
        $maxRec = NurturingConfig::getMaxRecordatorios();

        $where = '(mc.instrucciones_enviadas = 1 OR mc.proximo_recordatorio_at IS NOT NULL OR mc.recordatorios_enviados > 0 OR mc.desuscrito = 1)';
        $params = [];

        switch ($filtro) {
            case 'en_cola':
                $where .= ' AND mc.proximo_recordatorio_at IS NOT NULL AND mc.desuscrito = 0 AND mc.nurturing_pausado = 0 AND mc.recordatorios_enviados < ?';
                $params[] = $maxRec;
                break;
            case 'completados':
                $where .= ' AND mc.recordatorios_enviados >= ?';
                $params[] = $maxRec;
                break;
            case 'pausados':
                $where .= ' AND mc.nurturing_pausado = 1';
                break;
            case 'desuscritos':
                $where .= ' AND mc.desuscrito = 1';
                break;
        }

        $countRow = $this->db->fetch(
            "SELECT COUNT(*) as total FROM mensajes_contacto mc WHERE {$where}", $params
        );
        $total = (int) ($countRow['total'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $limit));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $limit;

        $contactos = $this->db->fetchAll(
            "SELECT mc.id, mc.nombre, mc.email, mc.recordatorios_enviados,
                    mc.ultimo_recordatorio_at, mc.proximo_recordatorio_at,
                    mc.nurturing_pausado, mc.desuscrito, mc.estado
             FROM mensajes_contacto mc
             WHERE {$where}
             ORDER BY mc.proximo_recordatorio_at ASC, mc.created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        // Contadores para tabs
        $contadores = [
            'todos' => (int) ($this->db->fetch(
                "SELECT COUNT(*) as c FROM mensajes_contacto mc
                 WHERE (mc.instrucciones_enviadas = 1 OR mc.proximo_recordatorio_at IS NOT NULL OR mc.recordatorios_enviados > 0 OR mc.desuscrito = 1)"
            )['c'] ?? 0),
            'en_cola' => (int) ($this->db->fetch(
                "SELECT COUNT(*) as c FROM mensajes_contacto
                 WHERE proximo_recordatorio_at IS NOT NULL AND desuscrito = 0 AND nurturing_pausado = 0 AND recordatorios_enviados < ?",
                [$maxRec]
            )['c'] ?? 0),
            'completados' => (int) ($this->db->fetch(
                "SELECT COUNT(*) as c FROM mensajes_contacto WHERE recordatorios_enviados >= ?",
                [$maxRec]
            )['c'] ?? 0),
            'pausados' => (int) ($this->db->fetch(
                "SELECT COUNT(*) as c FROM mensajes_contacto WHERE nurturing_pausado = 1"
            )['c'] ?? 0),
            'desuscritos' => (int) ($this->db->fetch(
                "SELECT COUNT(*) as c FROM mensajes_contacto WHERE desuscrito = 1"
            )['c'] ?? 0),
        ];

        $this->render('admin/nurturing/contactos', [
            'title'       => 'Contactos Nurturing — ' . SITE_NAME,
            'contactos'   => $contactos,
            'contadores'  => $contadores,
            'filtro'      => $filtro,
            'maxRec'      => $maxRec,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
            'baseUrl'     => '/admin/nurturing/contactos',
            'queryParams' => array_filter(['filtro' => $filtro]),
        ]);
    }

    public function editarFechaEnvio(int $id): void
    {
        $fecha = trim($_POST['proximo_recordatorio_at'] ?? '');
        if (empty($fecha)) {
            $this->back(['error' => 'Fecha obligatoria']);
            return;
        }

        $this->db->update('mensajes_contacto', [
            'proximo_recordatorio_at' => $fecha,
        ], 'id = ?', [$id]);

        $this->log('nurturing', 'editar_fecha', 'mensaje_contacto', $id,
            "Proximo recordatorio reprogramado a: {$fecha}");

        $this->back(['success' => 'Fecha de envio actualizada']);
    }

    public function pausarContacto(int $id): void
    {
        $this->db->update('mensajes_contacto', [
            'nurturing_pausado'    => 1,
            'nurturing_pausado_por' => 'admin',
        ], 'id = ?', [$id]);

        $this->log('nurturing', 'pausar', 'mensaje_contacto', $id, 'Nurturing pausado por admin');
        $this->back(['success' => 'Contacto pausado']);
    }

    public function reanudarContacto(int $id): void
    {
        $msg = MensajeContacto::find($id);
        if (!$msg) {
            $this->back(['error' => 'Contacto no encontrado']);
            return;
        }

        $update = [
            'nurturing_pausado'     => 0,
            'nurturing_pausado_por' => null,
        ];

        // Si proximo_recordatorio_at ya paso, reprogramar
        if (!empty($msg['proximo_recordatorio_at']) && strtotime($msg['proximo_recordatorio_at']) < time()) {
            $intervalo = NurturingConfig::getIntervaloDias();
            $update['proximo_recordatorio_at'] = date('Y-m-d H:i:s', strtotime("+{$intervalo} days"));
        }

        $this->db->update('mensajes_contacto', $update, 'id = ?', [$id]);

        $this->log('nurturing', 'reanudar', 'mensaje_contacto', $id, 'Nurturing reanudado');
        $this->back(['success' => 'Contacto reanudado']);
    }

    public function cancelarContacto(int $id): void
    {
        $maxRec = NurturingConfig::getMaxRecordatorios();
        $this->db->update('mensajes_contacto', [
            'proximo_recordatorio_at' => null,
            'recordatorios_enviados'  => $maxRec,
        ], 'id = ?', [$id]);

        $this->log('nurturing', 'cancelar', 'mensaje_contacto', $id,
            'Recordatorios cancelados por admin');
        $this->back(['success' => 'Recordatorios cancelados']);
    }

    public function enviarAhora(int $id): void
    {
        $msg = MensajeContacto::find($id);
        if (!$msg) {
            $this->back(['error' => 'Contacto no encontrado']);
            return;
        }

        $numSiguiente = $msg['recordatorios_enviados'] + 1;
        $maxRec = NurturingConfig::getMaxRecordatorios();

        if ($numSiguiente > $maxRec) {
            $this->back(['error' => 'Ya se enviaron todos los recordatorios']);
            return;
        }

        $plantilla = NurturingPlantilla::getByNumero($numSiguiente);
        if (!$plantilla) {
            $this->back(['error' => "No hay plantilla activa para recordatorio #{$numSiguiente}"]);
            return;
        }

        // Renderizar
        $totalComercios = $this->db->count('comercios', 'activo = 1');
        $vars = [
            '{nombre}'             => $msg['nombre'],
            '{email}'              => $msg['email'],
            '{total_comercios}'    => (string) $totalComercios,
            '{link_registro}'      => SITE_URL . '/registrar-comercio',
            '{link_desuscripcion}' => SITE_URL . '/desuscribir/' . ($msg['token_desuscripcion'] ?? ''),
        ];

        $asunto = str_replace(array_keys($vars), array_values($vars), $plantilla['asunto']);
        $html = str_replace(array_keys($vars), array_values($vars), $plantilla['contenido_html']);

        // Agregar footer de desuscripcion
        $desActiva = (bool) (int) NurturingConfig::get('desuscripcion_activa', '1');
        if ($desActiva && !empty($msg['token_desuscripcion'])) {
            $txtDes = NurturingConfig::get('texto_desuscripcion', 'Si no deseas recibir mas correos, haz clic aqui');
            $html .= '<hr style="margin:20px 0;border:none;border-top:1px solid #e2e8f0;">'
                . '<p style="text-align:center;font-size:12px;color:#999;">'
                . '<a href="' . SITE_URL . '/desuscribir/' . htmlspecialchars($msg['token_desuscripcion']) . '" style="color:#999;">'
                . htmlspecialchars($txtDes) . '</a></p>';
        }

        $mailer = new Mailer();
        $enviado = $mailer->sendHtml($msg['email'], $asunto, $html, 'nurturing-recordatorio');

        if ($enviado) {
            $intervalo = NurturingConfig::getIntervaloDias();
            $proximoAt = $numSiguiente < $maxRec
                ? date('Y-m-d H:i:s', strtotime("+{$intervalo} days"))
                : null;

            $this->db->update('mensajes_contacto', [
                'recordatorios_enviados'  => $numSiguiente,
                'ultimo_recordatorio_at'  => date('Y-m-d H:i:s'),
                'proximo_recordatorio_at' => $proximoAt,
            ], 'id = ?', [$id]);

            NurturingLog::registrar([
                'mensaje_id'          => $id,
                'plantilla_id'        => $plantilla['id'],
                'numero_recordatorio'  => $numSiguiente,
                'estado'              => 'enviado',
                'email_destino'       => $msg['email'],
                'asunto_enviado'      => $asunto,
            ]);

            MensajeRespuesta::crear([
                'mensaje_id'    => $id,
                'tipo'          => 'seguimiento',
                'asunto'        => $asunto,
                'contenido'     => "Recordatorio #{$numSiguiente}: {$plantilla['nombre']}",
                'email_destino' => $msg['email'],
                'enviado_por'   => 'admin-manual',
            ]);

            $this->log('nurturing', 'enviar_ahora', 'mensaje_contacto', $id,
                "Recordatorio #{$numSiguiente} enviado manualmente a {$msg['email']}");

            $this->back(['success' => "Recordatorio #{$numSiguiente} enviado a {$msg['email']}"]);
        } else {
            NurturingLog::registrar([
                'mensaje_id'          => $id,
                'plantilla_id'        => $plantilla['id'],
                'numero_recordatorio'  => $numSiguiente,
                'estado'              => 'fallido',
                'email_destino'       => $msg['email'],
                'asunto_enviado'      => $asunto,
                'error_detalle'       => 'Envio manual fallido',
            ]);

            $this->back(['error' => 'Error al enviar el recordatorio']);
        }
    }

    // ── ACCIONES MASIVAS ─────────────────────────────────────

    public function accionMasiva(): void
    {
        $accion = $_POST['accion'] ?? '';
        $maxRec = NurturingConfig::getMaxRecordatorios();

        switch ($accion) {
            case 'pausar_todos':
                $result = $this->db->execute(
                    "UPDATE mensajes_contacto SET nurturing_pausado = 1, nurturing_pausado_por = 'admin'
                     WHERE proximo_recordatorio_at IS NOT NULL AND desuscrito = 0 AND nurturing_pausado = 0"
                );
                $this->log('nurturing', 'pausar_todos', 'mensaje_contacto', 0, 'Todos los contactos pausados');
                $this->redirect('/admin/nurturing/contactos', ['success' => 'Todos los contactos en cola han sido pausados']);
                break;

            case 'reanudar_todos':
                $this->db->execute(
                    "UPDATE mensajes_contacto SET nurturing_pausado = 0, nurturing_pausado_por = NULL
                     WHERE nurturing_pausado = 1"
                );
                $this->log('nurturing', 'reanudar_todos', 'mensaje_contacto', 0, 'Todos los contactos reanudados');
                $this->redirect('/admin/nurturing/contactos', ['success' => 'Todos los contactos pausados han sido reanudados']);
                break;

            case 'reprogramar_todos':
                $intervalo = NurturingConfig::getIntervaloDias();
                $this->db->execute(
                    "UPDATE mensajes_contacto
                     SET proximo_recordatorio_at = DATE_ADD(NOW(), INTERVAL ? DAY)
                     WHERE proximo_recordatorio_at IS NOT NULL
                       AND desuscrito = 0 AND nurturing_pausado = 0
                       AND recordatorios_enviados < ?",
                    [$intervalo, $maxRec]
                );
                $this->log('nurturing', 'reprogramar_todos', 'mensaje_contacto', 0,
                    "Todos los contactos reprogramados con intervalo de {$intervalo} dias");
                $this->redirect('/admin/nurturing/contactos', ['success' => 'Todos los contactos reprogramados']);
                break;

            default:
                $this->redirect('/admin/nurturing/contactos', ['error' => 'Accion no valida']);
        }
    }
}
