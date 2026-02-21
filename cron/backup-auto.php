<?php
/**
 * Cron: Backup automático de BD
 * Ejecutar vía cPanel: una vez al día
 * Comando: php /home/user/public_html/cron/backup-auto.php
 */

// Solo ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    die('Solo CLI');
}

// Bootstrap mínimo
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';
if (file_exists(BASE_PATH . '/config/backup.php')) {
    require BASE_PATH . '/config/backup.php';
}

spl_autoload_register(function (string $class) {
    $path = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    $path = str_replace('/App/', '/app/', $path);
    if (file_exists($path)) require_once $path;
});

use App\Services\Backup;
use App\Core\Database;

try {
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando backup automático de BD...\n";

    $result = Backup::exportDatabase();

    if ($result) {
        $size = Backup::formatSize(filesize($result));
        echo "[" . date('Y-m-d H:i:s') . "] Backup generado: " . basename($result) . " ({$size})\n";

        // Registrar en log
        Database::getInstance()->insert('admin_log', [
            'usuario_id'     => 0,
            'usuario_nombre' => 'Sistema (Cron)',
            'modulo'         => 'mantenimiento',
            'accion'         => 'backup_auto',
            'entidad_tipo'   => 'backup',
            'entidad_id'     => 0,
            'detalle'        => 'Backup automático de BD: ' . basename($result) . " ({$size})",
            'ip'             => '127.0.0.1',
            'user_agent'     => 'CLI/Cron',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
        // Subir a Google Drive si está habilitado
        if (defined('GDRIVE_ENABLED') && GDRIVE_ENABLED) {
            echo "[" . date('Y-m-d H:i:s') . "] Subiendo backup a Google Drive...\n";

            $driveResult = \App\Services\GoogleDrive::subirArchivo($result, basename($result));

            if ($driveResult['ok']) {
                echo "[" . date('Y-m-d H:i:s') . "] Backup subido a Drive (ID: " . ($driveResult['fileId'] ?? 'N/A') . ")\n";

                Database::getInstance()->insert('admin_log', [
                    'usuario_id'     => 0,
                    'usuario_nombre' => 'Sistema (Cron)',
                    'modulo'         => 'mantenimiento',
                    'accion'         => 'backup_drive_upload',
                    'entidad_tipo'   => 'backup',
                    'entidad_id'     => 0,
                    'detalle'        => 'Backup subido a Drive: ' . basename($result),
                    'ip'             => '127.0.0.1',
                    'user_agent'     => 'CLI/Cron',
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            } else {
                echo "[" . date('Y-m-d H:i:s') . "] ERROR Drive: " . ($driveResult['message'] ?? 'Error desconocido') . "\n";
            }
        }
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] ERROR: No se pudo generar el backup\n";
    }

    // Limpiar backups antiguos (> 30 días)
    $deleted = Backup::cleanOldBackups(30);
    if ($deleted > 0) {
        echo "[" . date('Y-m-d H:i:s') . "] Backups antiguos eliminados: {$deleted}\n";
    }

    // Limpiar backups antiguos en Drive
    if (defined('GDRIVE_ENABLED') && GDRIVE_ENABLED) {
        $retentionDays = defined('GDRIVE_RETENTION_DAYS') ? GDRIVE_RETENTION_DAYS : 30;
        $driveDeleted = \App\Services\GoogleDrive::cleanOldDriveBackups($retentionDays);
        if ($driveDeleted > 0) {
            echo "[" . date('Y-m-d H:i:s') . "] Backups antiguos eliminados de Drive: {$driveDeleted}\n";
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Proceso completado.\n";

} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
