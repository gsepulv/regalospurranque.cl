<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Services\Backup;
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

        $this->render('admin/mantenimiento/backups', [
            'title'         => 'Backups — ' . SITE_NAME,
            'tab'           => 'backups',
            'backups'       => $backups,
            'totalSize'     => $totalSize,
            'diskFreeSpace' => $diskFreeSpace,
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
}
