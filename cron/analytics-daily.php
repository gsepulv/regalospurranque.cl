<?php
/**
 * Cron: Consolidación diaria de analytics
 * Ejecutar vía cPanel: una vez al día a las 00:05
 * Comando: php /home/purranque/v2.regalos.purranque.info/cron/analytics-daily.php
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

// Lock file para evitar ejecuciones simultáneas
$lockFile = BASE_PATH . '/storage/logs/cron-analytics-daily.lock';
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
    file_put_contents($logDir . '/cron-analytics.log', $line, FILE_APPEND | LOCK_EX);
}

try {
    $db = Database::getInstance();
    $ayer = date('Y-m-d', strtotime('-1 day'));

    cronLog("Consolidando analytics del {$ayer}...");

    // Consolidar visitas del día anterior en analytics_diario
    $visitas = $db->fetchAll(
        "SELECT pagina, COUNT(*) as visitas, COUNT(DISTINCT ip) as visitantes_unicos
         FROM visitas_log
         WHERE DATE(created_at) = ?
         GROUP BY pagina",
        [$ayer]
    );

    $inserted = 0;
    foreach ($visitas as $v) {
        $db->execute(
            "INSERT INTO analytics_diario (fecha, pagina, visitas, visitantes_unicos)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE visitas = VALUES(visitas), visitantes_unicos = VALUES(visitantes_unicos)",
            [$ayer, $v['pagina'], $v['visitas'], $v['visitantes_unicos']]
        );
        $inserted++;
    }

    cronLog("Consolidados {$inserted} registros para {$ayer}");

    // Limpiar visitas_log > 90 días (mantener solo resumen diario)
    $cutoff = date('Y-m-d', strtotime('-90 days'));
    $result = $db->execute(
        "DELETE FROM visitas_log WHERE created_at < ?",
        [$cutoff . ' 00:00:00']
    );

    cronLog("Registros antiguos eliminados (anteriores a {$cutoff})");

    // Registrar en log
    $db->insert('admin_log', [
        'usuario_id'     => 0,
        'usuario_nombre' => 'Sistema (Cron)',
        'modulo'         => 'analytics',
        'accion'         => 'consolidar_diario',
        'entidad_tipo'   => 'analytics',
        'entidad_id'     => 0,
        'detalle'        => "Consolidados {$inserted} registros para {$ayer}",
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
