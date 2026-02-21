<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\Backup;
use App\Services\GoogleDrive;
use App\Services\Notification;

/**
 * Gestión de backups del sistema
 * Listar, crear, descargar y eliminar respaldos
 */
class BackupController extends Controller
{
    /**
     * GET /admin/mantenimiento/backups
     * Listar todos los backups existentes
     */
    public function listBackups(): void
    {
        $backups       = Backup::listBackups();
        $totalSize     = Backup::getTotalBackupSize();
        $diskFreeSpace = Backup::getDiskFreeSpace();

        // Google Drive (solo si habilitado)
        $driveEnabled = GoogleDrive::isEnabled();
        $driveStatus  = ['ok' => false, 'message' => 'No habilitado'];
        $driveFiles   = [];

        if ($driveEnabled) {
            $driveStatus = GoogleDrive::verificarConexion();
            $driveResult = GoogleDrive::listarArchivos();
            $driveFiles  = $driveResult['ok'] ? ($driveResult['files'] ?? []) : [];
        }

        $this->render('admin/mantenimiento/backups', [
            'title'         => 'Backups — ' . SITE_NAME,
            'tab'           => 'backups',
            'backups'       => $backups,
            'totalSize'     => $totalSize,
            'diskFreeSpace' => $diskFreeSpace,
            'driveEnabled'  => $driveEnabled,
            'driveStatus'   => $driveStatus,
            'driveFiles'    => $driveFiles,
        ]);
    }

    /**
     * POST /admin/mantenimiento/backup/db
     * Crear backup de base de datos
     */
    public function backupDb(): void
    {
        $result = Backup::exportDatabase();

        if ($result) {
            $filename = basename($result);
            $size = Backup::formatSize(filesize($result));
            $this->log('mantenimiento', 'backup_db', 'backup', 0, "Backup de BD creado: {$filename}");
            Notification::backupCompletado('base de datos', $filename, $size);
            $this->redirect('/admin/mantenimiento/backups', [
                'success' => 'Backup de base de datos creado correctamente',
            ]);
        } else {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Error al crear el backup de base de datos',
            ]);
        }
    }

    /**
     * POST /admin/mantenimiento/backup/archivos
     * Crear backup de archivos
     */
    public function backupFiles(): void
    {
        $result = Backup::exportFiles();

        if ($result) {
            $filename = basename($result);
            $size = Backup::formatSize(filesize($result));
            $this->log('mantenimiento', 'backup_files', 'backup', 0, "Backup de archivos creado: {$filename}");
            Notification::backupCompletado('archivos', $filename, $size);
            $this->redirect('/admin/mantenimiento/backups', [
                'success' => 'Backup de archivos creado correctamente',
            ]);
        } else {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Error al crear el backup de archivos',
            ]);
        }
    }

    /**
     * POST /admin/mantenimiento/backup/completo
     * Crear backup completo (BD + archivos)
     */
    public function backupFull(): void
    {
        $result = Backup::exportFull();

        if ($result) {
            $filename = basename($result);
            $size = Backup::formatSize(filesize($result));
            $this->log('mantenimiento', 'backup_full', 'backup', 0, "Backup completo creado: {$filename}");
            Notification::backupCompletado('completo', $filename, $size);
            $this->redirect('/admin/mantenimiento/backups', [
                'success' => 'Backup completo creado correctamente',
            ]);
        } else {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Error al crear el backup completo',
            ]);
        }
    }

    /**
     * GET /admin/mantenimiento/backup/descargar/{file}
     * Descargar un archivo de backup
     */
    public function downloadBackup(string $file): void
    {
        // Validar path traversal
        if (str_contains($file, '..') || str_contains($file, '/') || str_contains($file, '\\')) {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Nombre de archivo no válido',
            ]);
            return;
        }

        $filepath = BASE_PATH . '/storage/backups/' . $file;

        if (!file_exists($filepath)) {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'El archivo de backup no existe',
            ]);
            return;
        }

        Response::download($filepath, $file);
    }

    /**
     * POST /admin/mantenimiento/backup/eliminar/{file}
     * Eliminar un archivo de backup
     */
    public function deleteBackup(string $file): void
    {
        // Validar path traversal
        if (str_contains($file, '..') || str_contains($file, '/') || str_contains($file, '\\')) {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Nombre de archivo no válido',
            ]);
            return;
        }

        $deleted = Backup::deleteBackup($file);

        if ($deleted) {
            $this->log('mantenimiento', 'backup_delete', 'backup', 0, "Backup eliminado: {$file}");
            $this->redirect('/admin/mantenimiento/backups', [
                'success' => 'Backup eliminado correctamente',
            ]);
        } else {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Error al eliminar el backup',
            ]);
        }
    }

    // ─── Google Drive ────────────────────────────────────────────

    /**
     * POST /admin/mantenimiento/backup/drive/subir/{file}
     * Subir un backup local a Google Drive
     */
    public function uploadToDrive(string $file): void
    {
        if (str_contains($file, '..') || str_contains($file, '/') || str_contains($file, '\\')) {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Nombre de archivo no válido',
            ]);
            return;
        }

        $filepath = BASE_PATH . '/storage/backups/' . $file;

        if (!file_exists($filepath)) {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'El archivo de backup no existe',
            ]);
            return;
        }

        if (!GoogleDrive::isEnabled()) {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Google Drive no está habilitado',
            ]);
            return;
        }

        @set_time_limit(300);
        $result = GoogleDrive::subirArchivo($filepath, $file);

        if ($result['ok']) {
            $size = Backup::formatSize(filesize($filepath));
            $this->log('mantenimiento', 'backup_drive_upload', 'backup', 0, "Backup subido a Drive: {$file} ({$size})");
            $this->redirect('/admin/mantenimiento/backups', [
                'success' => 'Backup subido a Google Drive correctamente',
            ]);
        } else {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Error al subir a Drive: ' . ($result['message'] ?? 'Error desconocido'),
            ]);
        }
    }

    /**
     * POST /admin/mantenimiento/backup/drive/eliminar/{fileId}
     * Eliminar un archivo de Google Drive
     */
    public function deleteDriveBackup(string $fileId): void
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $fileId)) {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'ID de archivo no válido',
            ]);
            return;
        }

        $result = GoogleDrive::eliminarArchivo($fileId);

        if ($result['ok']) {
            $this->log('mantenimiento', 'backup_drive_delete', 'backup', 0, "Backup eliminado de Drive: {$fileId}");
            $this->redirect('/admin/mantenimiento/backups', [
                'success' => 'Backup eliminado de Google Drive correctamente',
            ]);
        } else {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Error al eliminar de Drive: ' . ($result['message'] ?? 'Error desconocido'),
            ]);
        }
    }

    /**
     * POST /admin/mantenimiento/backup/drive/test
     * Probar conexión con Google Drive
     */
    public function testDriveConnection(): void
    {
        $result = GoogleDrive::verificarConexion();

        if ($result['ok']) {
            $this->log('mantenimiento', 'backup_drive_test', 'backup', 0, 'Conexión con Google Drive verificada');
            $this->redirect('/admin/mantenimiento/backups', [
                'success' => 'Conexión con Google Drive exitosa: ' . ($result['email'] ?? ''),
            ]);
        } else {
            $this->redirect('/admin/mantenimiento/backups', [
                'error' => 'Error de conexión con Drive: ' . ($result['message'] ?? 'Error desconocido'),
            ]);
        }
    }
}
