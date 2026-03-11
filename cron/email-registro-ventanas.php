<?php
/**
 * Cron: Instrucciones de registro por ventanas horarias
 * Procesa mensajes de contacto pendientes y envía instrucciones
 * de registro a quienes mencionan keywords relevantes.
 * Ejecutar 7 veces al día: 08, 10, 12, 14, 16, 18, 20h
 * Comando: php /home/purranque/v2.regalos.purranque.info/cron/email-registro-ventanas.php
 */

// Solo ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    die('Solo CLI');
}

// Bootstrap mínimo
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';

spl_autoload_register(function (string $class) {
    $path = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    $path = str_replace('/App/', '/app/', $path);
    if (file_exists($path)) require_once $path;
});

use App\Core\Database;
use App\Services\Notification;

// Lock file para evitar ejecuciones simultáneas
$lockFile = BASE_PATH . '/storage/logs/cron-registro-ventanas.lock';
$lockFp = fopen($lockFile, 'c');
if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
    echo "[" . date('Y-m-d H:i:s') . "] Otra instancia ya está ejecutándose. Saliendo.\n";
    exit(0);
}

$db = Database::getInstance();

try {
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando cron de instrucciones de registro...\n";

    // Obtener mensajes no procesados
    $mensajes = $db->fetchAll(
        "SELECT id, nombre, email, asunto, mensaje
         FROM mensajes_contacto
         WHERE instrucciones_enviadas = 0
         ORDER BY id ASC"
    );

    $totalProcesados = count($mensajes);
    $totalEnviados = 0;

    $keywords = ['registro', 'registrar', 'inscribir', 'incluir', 'publicar', 'negocio', 'comercio', 'cómo puedo'];

    foreach ($mensajes as $msg) {
        $texto = mb_strtolower($msg['asunto'] . ' ' . $msg['mensaje']);
        $enviar = false;

        foreach ($keywords as $kw) {
            if (str_contains($texto, $kw)) {
                $enviar = true;
                break;
            }
        }

        if ($enviar) {
            Notification::instruccionesRegistro([
                'nombre' => $msg['nombre'],
                'email'  => $msg['email'],
            ]);
            $totalEnviados++;
            echo "[" . date('Y-m-d H:i:s') . "] Instrucciones enviadas a: {$msg['email']} (ID: {$msg['id']})\n";

            // Registrar en mensajes_respuestas
            $db->insert('mensajes_respuestas', [
                'mensaje_id'    => $msg['id'],
                'tipo'          => 'instrucciones_registro',
                'asunto'        => 'Instrucciones de registro',
                'email_destino' => $msg['email'],
                'enviado_por'   => 'sistema',
            ]);

            // Generar token de desuscripcion y programar primer recordatorio
            $token = hash('sha256', $msg['id'] . $msg['email'] . time() . bin2hex(random_bytes(8)));
            $diasEspera = 7;
            try {
                $cfgRow = $db->fetch("SELECT valor FROM nurturing_config WHERE clave = 'dias_espera_primera'");
                if ($cfgRow) $diasEspera = max(1, (int) $cfgRow['valor']);
            } catch (\Throwable $e) {}

            $db->execute(
                "UPDATE mensajes_contacto SET token_desuscripcion = ?, proximo_recordatorio_at = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?",
                [$token, $diasEspera, $msg['id']]
            );
        }

        // Marcar como procesado (con o sin envío)
        $db->execute(
            "UPDATE mensajes_contacto SET instrucciones_enviadas = 1 WHERE id = ?",
            [$msg['id']]
        );
    }

    echo "[" . date('Y-m-d H:i:s') . "] Procesados: {$totalProcesados}, Enviados: {$totalEnviados}\n";

    // ── Detección automática de conversiones ────────────────
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
            'estado'        => 'convertido',
            'comercio_id'   => (int) $conv['comercio_id'],
            'convertido_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [$conv['mensaje_id']]);

        $totalConversiones++;
        echo "[" . date('Y-m-d H:i:s') . "] Conversion detectada: {$conv['email']} -> {$conv['comercio_nombre']}\n";
    }

    echo "[" . date('Y-m-d H:i:s') . "] Conversiones detectadas: {$totalConversiones}\n";

    // ── Actualizar seguimiento_conversiones del día ─────────
    $hoy = date('Y-m-d');

    $statsHoy = $db->fetch(
        "SELECT
            COUNT(*) as recibidos,
            SUM(estado IN ('leido','respondido','convertido')) as leidos,
            SUM(estado = 'respondido') as respondidos,
            SUM(estado = 'convertido') as convertidos,
            SUM(estado = 'descartado') as descartados,
            AVG(CASE WHEN respondido_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, respondido_at) END) as avg_resp,
            AVG(CASE WHEN convertido_at IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, created_at, convertido_at) END) as avg_conv
         FROM mensajes_contacto"
    );

    $db->execute(
        "INSERT INTO seguimiento_conversiones
            (fecha, mensajes_recibidos, mensajes_leidos, mensajes_respondidos, mensajes_convertidos, mensajes_descartados, tiempo_respuesta_avg, tiempo_conversion_avg)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            mensajes_recibidos    = VALUES(mensajes_recibidos),
            mensajes_leidos       = VALUES(mensajes_leidos),
            mensajes_respondidos  = VALUES(mensajes_respondidos),
            mensajes_convertidos  = VALUES(mensajes_convertidos),
            mensajes_descartados  = VALUES(mensajes_descartados),
            tiempo_respuesta_avg  = VALUES(tiempo_respuesta_avg),
            tiempo_conversion_avg = VALUES(tiempo_conversion_avg)",
        [
            $hoy,
            (int) ($statsHoy['recibidos'] ?? 0),
            (int) ($statsHoy['leidos'] ?? 0),
            (int) ($statsHoy['respondidos'] ?? 0),
            (int) ($statsHoy['convertidos'] ?? 0),
            (int) ($statsHoy['descartados'] ?? 0),
            $statsHoy['avg_resp'] ? (int) round((float) $statsHoy['avg_resp']) : null,
            $statsHoy['avg_conv'] ? (int) round((float) $statsHoy['avg_conv']) : null,
        ]
    );

    // Registrar ejecución
    $db->insert('admin_log', [
        'usuario_id'     => 0,
        'usuario_nombre' => 'Sistema (Cron)',
        'modulo'         => 'contacto',
        'accion'         => 'cron_instrucciones_registro',
        'entidad_tipo'   => 'mensaje_contacto',
        'entidad_id'     => 0,
        'detalle'        => "Instrucciones: {$totalProcesados} procesados, {$totalEnviados} enviados. Conversiones: {$totalConversiones}",
        'ip'             => '127.0.0.1',
        'user_agent'     => 'CLI/Cron',
        'created_at'     => date('Y-m-d H:i:s'),
    ]);

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado.\n";

} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    @unlink($lockFile);
}
