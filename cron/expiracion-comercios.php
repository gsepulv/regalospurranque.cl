<?php
/**
 * Cron: Expiración automática de comercios
 * Desactiva comercios cuyo plan_fin ya venció.
 * Ejecutar vía cPanel: una vez al día a las 00:10
 * Comando: php /home/purranque/v2.regalos.purranque.info/cron/expiracion-comercios.php
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
    $hoy = date('Y-m-d');

    echo "[" . date('Y-m-d H:i:s') . "] Verificando comercios con plan vencido...\n";

    // Obtener comercios activos cuyo plan_fin ya pasó
    $vencidos = $db->fetchAll(
        "SELECT id, nombre, slug, plan, plan_fin, registrado_por
         FROM comercios
         WHERE activo = 1 AND plan_fin IS NOT NULL AND plan_fin < ?",
        [$hoy]
    );

    if (empty($vencidos)) {
        echo "[" . date('Y-m-d H:i:s') . "] No hay comercios vencidos.\n";
        exit(0);
    }

    $count = 0;
    foreach ($vencidos as $c) {
        $db->execute(
            "UPDATE comercios SET activo = 0 WHERE id = ?",
            [$c['id']]
        );
        $count++;
        echo "  - Desactivado: {$c['nombre']} (ID {$c['id']}, plan: {$c['plan']}, vencido: {$c['plan_fin']})\n";
    }

    echo "[" . date('Y-m-d H:i:s') . "] Total desactivados: {$count}\n";

} catch (\Throwable $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    error_log("[cron/expiracion-comercios] " . $e->getMessage());
    exit(1);
}
