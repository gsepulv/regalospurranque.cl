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

// Helper para log persistente
function cronLog(string $msg): void {
    $line = "[" . date('Y-m-d H:i:s') . "] {$msg}\n";
    echo $line;
    $logDir = BASE_PATH . '/storage/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    file_put_contents($logDir . '/cron-backup.log', $line, FILE_APPEND | LOCK_EX);
}

$db = Database::getInstance();
$driveEnabled = defined('GDRIVE_ENABLED') && GDRIVE_ENABLED;

// Lock file para evitar ejecuciones simultáneas
$lockFile = BASE_PATH . '/storage/logs/cron-backup-auto.lock';
$lockFp = fopen($lockFile, 'c');
if (!$lockFp || !flock($lockFp, LOCK_EX | LOCK_NB)) {
    cronLog("Otra instancia ya está ejecutándose. Saliendo.");
    exit(0);
}

try {
    cronLog("Iniciando backup automático...");

    // ── 1. Backup de BD comprimido (.sql.gz) ─────────────────
    cronLog("Generando backup de BD comprimido...");

    $dbBackup = Backup::exportDatabaseGz();

    if ($dbBackup) {
        $dbSize = Backup::formatSize(filesize($dbBackup));
        cronLog("Backup BD generado: " . basename($dbBackup) . " ({$dbSize})");

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
            cronLog("Subiendo backup BD a Google Drive...");
            $driveDb = \App\Services\GoogleDrive::subirArchivo($dbBackup, basename($dbBackup));

            if ($driveDb['ok']) {
                cronLog("BD subida a Drive (ID: " . ($driveDb['fileId'] ?? 'N/A') . ")");
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
                cronLog("ERROR Drive (BD): " . ($driveDb['message'] ?? 'Error desconocido'));
            }
        }
    } else {
        cronLog("ERROR: No se pudo generar el backup de BD");
    }

    // ── 2. Backup completo (.zip = BD + archivos) ────────────
    cronLog("Generando backup completo...");

    $fullBackup = Backup::exportFull();

    if ($fullBackup) {
        $fullSize = Backup::formatSize(filesize($fullBackup));
        cronLog("Backup completo generado: " . basename($fullBackup) . " ({$fullSize})");

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
            cronLog("Subiendo backup completo a Google Drive...");
            $driveFull = \App\Services\GoogleDrive::subirArchivo($fullBackup, basename($fullBackup));

            if ($driveFull['ok']) {
                cronLog("Completo subido a Drive (ID: " . ($driveFull['fileId'] ?? 'N/A') . ")");
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
                cronLog("ERROR Drive (completo): " . ($driveFull['message'] ?? 'Error desconocido'));
            }
        }
    } else {
        cronLog("ERROR: No se pudo generar el backup completo");
    }

    // ── 3. Limpieza de backups antiguos (>30 días) ───────────
    $deleted = Backup::cleanOldBackups(30);
    if ($deleted > 0) {
        cronLog("Backups locales antiguos eliminados: {$deleted}");
    }

    if ($driveEnabled) {
        $retentionDays = defined('GDRIVE_RETENTION_DAYS') ? GDRIVE_RETENTION_DAYS : 30;
        $driveDeleted = \App\Services\GoogleDrive::cleanOldDriveBackups($retentionDays);
        if ($driveDeleted > 0) {
            cronLog("Backups antiguos eliminados de Drive: {$driveDeleted}");
        }
    }

    cronLog("Proceso completado.");

} catch (\Throwable $e) {
    cronLog("ERROR: " . $e->getMessage());
    exit(1);
} finally {
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    @unlink($lockFile);
}
