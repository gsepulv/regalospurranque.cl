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
        }

        // Marcar como procesado (con o sin envío)
        $db->execute(
            "UPDATE mensajes_contacto SET instrucciones_enviadas = 1 WHERE id = ?",
            [$msg['id']]
        );
    }

    echo "[" . date('Y-m-d H:i:s') . "] Procesados: {$totalProcesados}, Enviados: {$totalEnviados}\n";

    // Registrar ejecución
    $db->insert('admin_log', [
        'usuario_id'     => 0,
        'usuario_nombre' => 'Sistema (Cron)',
        'modulo'         => 'contacto',
        'accion'         => 'cron_instrucciones_registro',
        'entidad_tipo'   => 'mensaje_contacto',
        'entidad_id'     => 0,
        'detalle'        => "Instrucciones de registro: {$totalProcesados} procesados, {$totalEnviados} enviados",
        'ip'             => '127.0.0.1',
        'user_agent'     => 'CLI/Cron',
        'created_at'     => date('Y-m-d H:i:s'),
    ]);

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado.\n";

} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
