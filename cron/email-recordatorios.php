<?php
/**
 * Cron: Envio de recordatorios de registro (Nurturing)
 * Ejecutar 1 vez al dia (ej: 10:00)
 * Comando: php /home/purranque/v2.regalos.purranque.info/cron/email-recordatorios.php
 */

if (php_sapi_name() !== 'cli') {
    die('Solo CLI');
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';

spl_autoload_register(function (string $class) {
    $path = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    $path = str_replace('/App/', '/app/', $path);
    if (file_exists($path)) require_once $path;
});

use App\Core\Database;
use App\Models\NurturingConfig;
use App\Models\NurturingPlantilla;
use App\Models\NurturingLog;
use App\Services\Mailer;

// Lock file para evitar ejecuciones simultáneas
$lockFile = BASE_PATH . '/storage/logs/cron-recordatorios.lock';
$lockFp = fopen($lockFile, 'c');
if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
    echo "[" . date('Y-m-d H:i:s') . "] Otra instancia ya está ejecutándose. Saliendo.\n";
    exit(0);
}

$db = Database::getInstance();

try {
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando cron de recordatorios...\n";

    // PASO 1: Verificar servicio activo
    if (!NurturingConfig::isServicioActivo()) {
        echo "[" . date('Y-m-d H:i:s') . "] Servicio desactivado. Saliendo.\n";
        exit(0);
    }

    // PASO 2: Leer configuracion
    $maxRec           = NurturingConfig::getMaxRecordatorios();
    $intervaloDias    = NurturingConfig::getIntervaloDias();
    $soloInstrucciones = (bool) (int) NurturingConfig::get('solo_con_instrucciones', '1');
    $estadosExcluidos = NurturingConfig::getEstadosExcluidos();
    $desActiva        = (bool) (int) NurturingConfig::get('desuscripcion_activa', '1');
    $txtDesuscripcion = NurturingConfig::get('texto_desuscripcion', 'Si no deseas recibir mas correos, haz clic aqui');

    echo "[" . date('Y-m-d H:i:s') . "] Config: max={$maxRec}, intervalo={$intervaloDias}d, excluidos=" . implode(',', $estadosExcluidos) . "\n";

    // PASO 3: Buscar contactos pendientes
    $excludePlaceholders = !empty($estadosExcluidos)
        ? implode(',', array_fill(0, count($estadosExcluidos), '?'))
        : "'__none__'";

    $params = [];
    $where = "mc.proximo_recordatorio_at IS NOT NULL
        AND mc.proximo_recordatorio_at <= NOW()
        AND mc.recordatorios_enviados < ?
        AND mc.desuscrito = 0
        AND mc.nurturing_pausado = 0";
    $params[] = $maxRec;

    if (!empty($estadosExcluidos)) {
        $where .= " AND mc.estado NOT IN ({$excludePlaceholders})";
        $params = array_merge($params, $estadosExcluidos);
    }

    if ($soloInstrucciones) {
        $where .= " AND mc.instrucciones_enviadas = 1";
    }

    $contactos = $db->fetchAll(
        "SELECT mc.* FROM mensajes_contacto mc WHERE {$where} ORDER BY mc.proximo_recordatorio_at ASC",
        $params
    );

    echo "[" . date('Y-m-d H:i:s') . "] Contactos pendientes: " . count($contactos) . "\n";

    $totalEnviados = 0;
    $totalFallidos = 0;
    $totalComercios = $db->count('comercios', 'activo = 1');
    $mailer = new Mailer();

    // PASO 4: Procesar cada contacto
    foreach ($contactos as $mc) {
        $numSiguiente = $mc['recordatorios_enviados'] + 1;

        // Buscar plantilla activa
        $plantilla = NurturingPlantilla::getByNumero($numSiguiente);
        if (!$plantilla) {
            echo "[" . date('Y-m-d H:i:s') . "] ADVERTENCIA: No hay plantilla activa para R{$numSiguiente}. Saltando {$mc['email']}\n";
            continue;
        }

        // Renderizar variables
        $vars = [
            '{nombre}'             => $mc['nombre'],
            '{email}'              => $mc['email'],
            '{total_comercios}'    => (string) $totalComercios,
            '{link_registro}'      => SITE_URL . '/registrar-comercio',
            '{link_desuscripcion}' => SITE_URL . '/desuscribir/' . ($mc['token_desuscripcion'] ?? ''),
        ];

        $asunto = str_replace(array_keys($vars), array_values($vars), $plantilla['asunto']);
        $html = str_replace(array_keys($vars), array_values($vars), $plantilla['contenido_html']);

        // Footer de desuscripcion
        if ($desActiva && !empty($mc['token_desuscripcion'])) {
            $html .= '<hr style="margin:20px 0;border:none;border-top:1px solid #e2e8f0;">'
                . '<p style="text-align:center;font-size:12px;color:#999;">'
                . '<a href="' . SITE_URL . '/desuscribir/' . htmlspecialchars($mc['token_desuscripcion']) . '" style="color:#999;">'
                . htmlspecialchars($txtDesuscripcion) . '</a></p>';
        }

        // Enviar
        $enviado = $mailer->sendHtml($mc['email'], $asunto, $html, 'nurturing-recordatorio');

        if ($enviado) {
            $proximoAt = $numSiguiente < $maxRec
                ? date('Y-m-d H:i:s', strtotime("+{$intervaloDias} days"))
                : null;

            $db->update('mensajes_contacto', [
                'recordatorios_enviados'  => $numSiguiente,
                'ultimo_recordatorio_at'  => date('Y-m-d H:i:s'),
                'proximo_recordatorio_at' => $proximoAt,
            ], 'id = ?', [$mc['id']]);

            NurturingLog::registrar([
                'mensaje_id'          => $mc['id'],
                'plantilla_id'        => $plantilla['id'],
                'numero_recordatorio'  => $numSiguiente,
                'estado_envio'        => 'enviado',
                'email_destino'       => $mc['email'],
                'asunto_enviado'      => $asunto,
            ]);

            $db->insert('mensajes_respuestas', [
                'mensaje_id'    => $mc['id'],
                'tipo'          => 'seguimiento',
                'asunto'        => $asunto,
                'contenido'     => "Recordatorio #{$numSiguiente}: {$plantilla['nombre']}",
                'email_destino' => $mc['email'],
                'enviado_por'   => 'sistema',
            ]);

            $totalEnviados++;
            echo "[" . date('Y-m-d H:i:s') . "] R{$numSiguiente} enviado a: {$mc['email']}\n";
        } else {
            NurturingLog::registrar([
                'mensaje_id'          => $mc['id'],
                'plantilla_id'        => $plantilla['id'],
                'numero_recordatorio'  => $numSiguiente,
                'estado_envio'        => 'fallido',
                'email_destino'       => $mc['email'],
                'asunto_enviado'      => $asunto,
                'error_detalle'       => 'Envio fallido via Mailer',
            ]);

            $totalFallidos++;
            echo "[" . date('Y-m-d H:i:s') . "] FALLO R{$numSiguiente} para: {$mc['email']}\n";
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Enviados: {$totalEnviados}, Fallidos: {$totalFallidos}\n";

    // PASO 5: Deteccion de conversiones
    echo "[" . date('Y-m-d H:i:s') . "] Detectando conversiones...\n";

    $conversiones = $db->fetchAll(
        "SELECT mc.id AS mensaje_id, mc.email, c.id AS comercio_id, c.nombre AS comercio_nombre
         FROM mensajes_contacto mc
         INNER JOIN comercios c ON LOWER(mc.email) = LOWER(c.email)
         WHERE mc.estado != 'convertido'
           AND mc.comercio_id IS NULL"
    );

    $totalConversiones = 0;
    foreach ($conversiones as $conv) {
        $db->update('mensajes_contacto', [
            'estado'                  => 'convertido',
            'comercio_id'             => (int) $conv['comercio_id'],
            'convertido_at'           => date('Y-m-d H:i:s'),
            'proximo_recordatorio_at' => null,
        ], 'id = ?', [$conv['mensaje_id']]);
        $totalConversiones++;
        echo "[" . date('Y-m-d H:i:s') . "] Conversion: {$conv['email']} -> {$conv['comercio_nombre']}\n";
    }

    // PASO 6: Actualizar seguimiento_conversiones
    $hoy = date('Y-m-d');
    $statsHoy = $db->fetch(
        "SELECT
            COUNT(*) as recibidos,
            SUM(estado IN ('leido','respondido','convertido')) as leidos,
            SUM(estado = 'respondido') as respondidos,
            SUM(estado = 'convertido') as convertidos,
            SUM(estado = 'descartado') as descartados,
            SUM(desuscrito = 1) as desuscritos_total,
            AVG(CASE WHEN respondido_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, respondido_at) END) as avg_resp,
            AVG(CASE WHEN convertido_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, convertido_at) END) as avg_conv
         FROM mensajes_contacto"
    );

    $totalRecordatoriosLog = $db->fetch(
        "SELECT COUNT(*) as c FROM nurturing_log WHERE estado_envio = 'enviado'"
    );

    $db->execute(
        "INSERT INTO seguimiento_conversiones
            (fecha, mensajes_recibidos, mensajes_leidos, mensajes_respondidos, mensajes_convertidos,
             mensajes_descartados, recordatorios_enviados, desuscritos, tiempo_respuesta_avg, tiempo_conversion_avg)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            mensajes_recibidos    = VALUES(mensajes_recibidos),
            mensajes_leidos       = VALUES(mensajes_leidos),
            mensajes_respondidos  = VALUES(mensajes_respondidos),
            mensajes_convertidos  = VALUES(mensajes_convertidos),
            mensajes_descartados  = VALUES(mensajes_descartados),
            recordatorios_enviados = VALUES(recordatorios_enviados),
            desuscritos            = VALUES(desuscritos),
            tiempo_respuesta_avg  = VALUES(tiempo_respuesta_avg),
            tiempo_conversion_avg = VALUES(tiempo_conversion_avg)",
        [
            $hoy,
            (int) ($statsHoy['recibidos'] ?? 0),
            (int) ($statsHoy['leidos'] ?? 0),
            (int) ($statsHoy['respondidos'] ?? 0),
            (int) ($statsHoy['convertidos'] ?? 0),
            (int) ($statsHoy['descartados'] ?? 0),
            (int) ($totalRecordatoriosLog['c'] ?? 0),
            (int) ($statsHoy['desuscritos_total'] ?? 0),
            $statsHoy['avg_resp'] ? (int) round((float) $statsHoy['avg_resp']) : null,
            $statsHoy['avg_conv'] ? (int) round((float) $statsHoy['avg_conv']) : null,
        ]
    );

    // PASO 7: Log resumen
    $db->insert('admin_log', [
        'usuario_id'     => 0,
        'usuario_nombre' => 'Sistema (Cron)',
        'modulo'         => 'nurturing',
        'accion'         => 'cron_recordatorios',
        'entidad_tipo'   => 'mensaje_contacto',
        'entidad_id'     => 0,
        'detalle'        => "Recordatorios: {$totalEnviados} enviados, {$totalFallidos} fallidos. Conversiones: {$totalConversiones}",
        'ip'             => '127.0.0.1',
        'user_agent'     => 'CLI/Cron',
        'created_at'     => date('Y-m-d H:i:s'),
    ]);

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado.\n";

} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
} finally {
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    @unlink($lockFile);
}
