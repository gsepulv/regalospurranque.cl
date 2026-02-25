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

try {
    $db = Database::getInstance();
    $ayer = date('Y-m-d', strtotime('-1 day'));

    echo "[" . date('Y-m-d H:i:s') . "] Consolidando analytics del {$ayer}...\n";

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

    echo "[" . date('Y-m-d H:i:s') . "] Consolidados {$inserted} registros para {$ayer}\n";

    // Limpiar visitas_log > 90 días (mantener solo resumen diario)
    $cutoff = date('Y-m-d', strtotime('-90 days'));
    $result = $db->execute(
        "DELETE FROM visitas_log WHERE created_at < ?",
        [$cutoff . ' 00:00:00']
    );

    echo "[" . date('Y-m-d H:i:s') . "] Registros antiguos eliminados (anteriores a {$cutoff})\n";

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

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado.\n";

} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
