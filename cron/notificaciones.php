<?php
/**
 * Cron: Notificaciones programadas
 * - Resumen semanal (ejecutar los lunes)
 * - Fechas especiales próximas (7 días antes)
 * Ejecutar vía cPanel: una vez al día
 * Comando: php /home/purranque/v2.regalos.purranque.info/cron/notificaciones.php
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
$lockFile = BASE_PATH . '/storage/logs/cron-notificaciones.lock';
$lockFp = fopen($lockFile, 'c');
if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
    echo "[" . date('Y-m-d H:i:s') . "] Otra instancia ya está ejecutándose. Saliendo.\n";
    exit(0);
}

// Helper para log persistente
function cronLog(string $msg): void {
    $line = "[" . date('Y-m-d H:i:s') . "] {$msg}\n";
    echo $line;
    $logDir = BASE_PATH . '/storage/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    file_put_contents($logDir . '/cron-notificaciones.log', $line, FILE_APPEND | LOCK_EX);
}

$db = Database::getInstance();

try {
    cronLog("Iniciando cron de notificaciones...");

    // ── Resumen semanal (solo los lunes) ──────────────────────
    if ((int) date('N') === 1) {
        cronLog("Generando resumen semanal...");

        $semanaAtras = date('Y-m-d', strtotime('-7 days'));

        $stats = [
            'visitas' => $db->fetch(
                "SELECT COUNT(*) as total FROM visitas_log WHERE created_at >= ?",
                [$semanaAtras]
            )['total'] ?? 0,

            'comercios_activos' => $db->count('comercios', 'activo = 1'),

            'resenas_nuevas' => $db->fetch(
                "SELECT COUNT(*) as total FROM resenas WHERE created_at >= ?",
                [$semanaAtras]
            )['total'] ?? 0,

            'resenas_pendientes' => $db->count('resenas', "estado = 'pendiente'"),

            'noticias' => $db->fetch(
                "SELECT COUNT(*) as total FROM noticias WHERE created_at >= ?",
                [$semanaAtras]
            )['total'] ?? 0,

            'top_comercios' => $db->fetchAll(
                "SELECT nombre, visitas FROM comercios
                 WHERE activo = 1
                 ORDER BY visitas DESC
                 LIMIT 5"
            ),
        ];

        Notification::resumenSemanal($stats);
        cronLog("Resumen semanal enviado.");
    }

    // ── Fechas especiales próximas (7 días antes) ────────────
    cronLog("Verificando fechas próximas...");

    $en7Dias = date('Y-m-d', strtotime('+7 days'));
    $fechasProximas = $db->fetchAll(
        "SELECT * FROM fechas_especiales
         WHERE activo = 1
           AND fecha_inicio = ?",
        [$en7Dias]
    );

    foreach ($fechasProximas as $fecha) {
        Notification::fechaProxima($fecha);
        cronLog("Notificación enviada: {$fecha['nombre']} ({$fecha['fecha_inicio']})");
    }

    if (empty($fechasProximas)) {
        cronLog("No hay fechas especiales próximas.");
    }

    // Registrar ejecución
    $db->insert('admin_log', [
        'usuario_id'     => 0,
        'usuario_nombre' => 'Sistema (Cron)',
        'modulo'         => 'notificaciones',
        'accion'         => 'cron_notificaciones',
        'entidad_tipo'   => 'notificacion',
        'entidad_id'     => 0,
        'detalle'        => 'Cron de notificaciones ejecutado. Fechas próximas: ' . count($fechasProximas),
        'ip'             => '127.0.0.1',
        'user_agent'     => 'CLI/Cron',
        'created_at'     => date('Y-m-d H:i:s'),
    ]);

    cronLog("Proceso completado.");

} catch (\Throwable $e) {
    cronLog("ERROR: " . $e->getMessage());
    exit(1);
} finally {
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    @unlink($lockFile);
}
