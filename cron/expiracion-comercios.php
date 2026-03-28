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

// Lock file para evitar ejecuciones simultáneas
$lockFile = BASE_PATH . '/storage/logs/cron-expiracion.lock';
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
    file_put_contents($logDir . '/cron-expiracion.log', $line, FILE_APPEND | LOCK_EX);
}

try {
    $db = Database::getInstance();
    $hoy = date('Y-m-d');

    cronLog("Verificando comercios con plan vencido...");

    // Obtener comercios activos cuyo plan_fin ya pasó
    $vencidos = $db->fetchAll(
        "SELECT id, nombre, slug, plan, plan_fin, registrado_por
         FROM comercios
         WHERE activo = 1 AND plan_fin IS NOT NULL AND plan_fin < ?",
        [$hoy]
    );

    if (empty($vencidos)) {
        cronLog("No hay comercios vencidos.");
    }

    // Separar freemiums (auto-renovar) de pagos (desactivar)
    $freemiums = [];
    $pagos = [];
    foreach ($vencidos as $c) {
        if ($c['plan'] === 'freemium') {
            $freemiums[] = $c;
        } else {
            $pagos[] = $c;
        }
    }

    // Auto-renovar freemiums
    $countRenovados = 0;
    if (!empty($freemiums)) {
        $planFreemium = $db->fetch(
            "SELECT duracion_dias FROM planes_config WHERE slug = 'freemium' AND activo = 1"
        );
        $duracion = $planFreemium ? (int)$planFreemium['duracion_dias'] : 30;

        foreach ($freemiums as $c) {
            $db->execute(
                "UPDATE comercios SET plan_inicio = CURDATE(), plan_fin = DATE_ADD(CURDATE(), INTERVAL ? DAY) WHERE id = ?",
                [$duracion, $c['id']]
            );
            $countRenovados++;
            cronLog("Auto-renovado freemium: {$c['nombre']} (ID {$c['id']}, nuevo vencimiento: " . date('Y-m-d', strtotime("+{$duracion} days")) . ")");
        }
    }

    // Desactivar planes pagos vencidos
    $countDesactivados = 0;
    foreach ($pagos as $c) {
        $db->execute(
            "UPDATE comercios SET activo = 0 WHERE id = ?",
            [$c['id']]
        );
        $countDesactivados++;
        cronLog("Desactivado: {$c['nombre']} (ID {$c['id']}, plan: {$c['plan']}, vencido: {$c['plan_fin']})");
    }

    if ($countDesactivados > 0) {
        cronLog("Total desactivados: {$countDesactivados}");
    }
    if ($countRenovados > 0) {
        cronLog("Total freemiums auto-renovados: {$countRenovados}");
    }

    // Registrar en admin_log
    if ($countDesactivados > 0) {
        $nombres = array_column($pagos, 'nombre');
        $db->insert('admin_log', [
            'usuario_id'     => null,
            'usuario_nombre' => 'cron',
            'modulo'         => 'comercios',
            'accion'         => 'expiracion_automatica',
            'entidad_tipo'   => 'comercio',
            'entidad_id'     => null,
            'detalle'        => "Desactivados {$countDesactivados} comercios vencidos: " . implode(', ', $nombres),
            'ip'             => '127.0.0.1',
        ]);
    }
    if ($countRenovados > 0) {
        $nombres = array_column($freemiums, 'nombre');
        $db->insert('admin_log', [
            'usuario_id'     => null,
            'usuario_nombre' => 'cron',
            'modulo'         => 'comercios',
            'accion'         => 'freemium_auto_renovacion',
            'entidad_tipo'   => 'comercio',
            'entidad_id'     => null,
            'detalle'        => "Auto-renovados {$countRenovados} freemiums: " . implode(', ', $nombres),
            'ip'             => '127.0.0.1',
        ]);
    }

} catch (\Throwable $e) {
    cronLog("ERROR: " . $e->getMessage());
    exit(1);
} finally {
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    @unlink($lockFile);
}
