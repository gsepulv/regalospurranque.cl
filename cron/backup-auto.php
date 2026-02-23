<?php
/**
 * Cron: Backup automático diario (BD + Completo)
 * Genera 2 backups y sube ambos a Google Drive:
 *   1. BD comprimida (.sql.gz) — crítico, siempre por separado
 *   2. Completo (.zip) — BD + archivos del sitio
 * Ejecutar vía cPanel: una vez al día
 * Comando: php /home/purranque/v2.regalos.purranque.info/cron/backup-auto.php
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

$db = Database::getInstance();
$driveEnabled = defined('GDRIVE_ENABLED') && GDRIVE_ENABLED;

try {
    echo "[" . date('Y-m-d H:i:s') . "] Iniciando backup automático...\n";

    // ── 1. Backup de BD comprimido (.sql.gz) ─────────────────
    echo "[" . date('Y-m-d H:i:s') . "] Generando backup de BD comprimido...\n";

    $dbBackup = Backup::exportDatabaseGz();

    if ($dbBackup) {
        $dbSize = Backup::formatSize(filesize($dbBackup));
        echo "[" . date('Y-m-d H:i:s') . "] Backup BD generado: " . basename($dbBackup) . " ({$dbSize})\n";

        $db->insert('admin_log', [
            'usuario_id'     => 0,
            'usuario_nombre' => 'Sistema (Cron)',
            'modulo'         => 'mantenimiento',
            'accion'         => 'backup_auto',
            'entidad_tipo'   => 'backup',
            'entidad_id'     => 0,
            'detalle'        => 'Backup automático BD: ' . basename($dbBackup) . " ({$dbSize})",
            'ip'             => '127.0.0.1',
            'user_agent'     => 'CLI/Cron',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        // Subir BD a Drive
        if ($driveEnabled) {
            echo "[" . date('Y-m-d H:i:s') . "] Subiendo backup BD a Google Drive...\n";
            $driveDb = \App\Services\GoogleDrive::subirArchivo($dbBackup, basename($dbBackup));

            if ($driveDb['ok']) {
                echo "[" . date('Y-m-d H:i:s') . "] BD subida a Drive (ID: " . ($driveDb['fileId'] ?? 'N/A') . ")\n";
                $db->insert('admin_log', [
                    'usuario_id'     => 0,
                    'usuario_nombre' => 'Sistema (Cron)',
                    'modulo'         => 'mantenimiento',
                    'accion'         => 'backup_drive_upload',
                    'entidad_tipo'   => 'backup',
                    'entidad_id'     => 0,
                    'detalle'        => 'Backup BD subido a Drive: ' . basename($dbBackup),
                    'ip'             => '127.0.0.1',
                    'user_agent'     => 'CLI/Cron',
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            } else {
                echo "[" . date('Y-m-d H:i:s') . "] ERROR Drive (BD): " . ($driveDb['message'] ?? 'Error desconocido') . "\n";
            }
        }
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] ERROR: No se pudo generar el backup de BD\n";
    }

    // ── 2. Backup completo (.zip = BD + archivos) ────────────
    echo "[" . date('Y-m-d H:i:s') . "] Generando backup completo...\n";

    $fullBackup = Backup::exportFull();

    if ($fullBackup) {
        $fullSize = Backup::formatSize(filesize($fullBackup));
        echo "[" . date('Y-m-d H:i:s') . "] Backup completo generado: " . basename($fullBackup) . " ({$fullSize})\n";

        $db->insert('admin_log', [
            'usuario_id'     => 0,
            'usuario_nombre' => 'Sistema (Cron)',
            'modulo'         => 'mantenimiento',
            'accion'         => 'backup_auto',
            'entidad_tipo'   => 'backup',
            'entidad_id'     => 0,
            'detalle'        => 'Backup automático completo: ' . basename($fullBackup) . " ({$fullSize})",
            'ip'             => '127.0.0.1',
            'user_agent'     => 'CLI/Cron',
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        // Subir completo a Drive
        if ($driveEnabled) {
            echo "[" . date('Y-m-d H:i:s') . "] Subiendo backup completo a Google Drive...\n";
            $driveFull = \App\Services\GoogleDrive::subirArchivo($fullBackup, basename($fullBackup));

            if ($driveFull['ok']) {
                echo "[" . date('Y-m-d H:i:s') . "] Completo subido a Drive (ID: " . ($driveFull['fileId'] ?? 'N/A') . ")\n";
                $db->insert('admin_log', [
                    'usuario_id'     => 0,
                    'usuario_nombre' => 'Sistema (Cron)',
                    'modulo'         => 'mantenimiento',
                    'accion'         => 'backup_drive_upload',
                    'entidad_tipo'   => 'backup',
                    'entidad_id'     => 0,
                    'detalle'        => 'Backup completo subido a Drive: ' . basename($fullBackup),
                    'ip'             => '127.0.0.1',
                    'user_agent'     => 'CLI/Cron',
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            } else {
                echo "[" . date('Y-m-d H:i:s') . "] ERROR Drive (completo): " . ($driveFull['message'] ?? 'Error desconocido') . "\n";
            }
        }
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] ERROR: No se pudo generar el backup completo\n";
    }

    // ── 3. Limpieza de backups antiguos (>30 días) ───────────
    $deleted = Backup::cleanOldBackups(30);
    if ($deleted > 0) {
        echo "[" . date('Y-m-d H:i:s') . "] Backups locales antiguos eliminados: {$deleted}\n";
    }

    if ($driveEnabled) {
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
