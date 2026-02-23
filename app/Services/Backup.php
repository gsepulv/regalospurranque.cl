<?php
namespace App\Services;

use App\Core\Database;

/**
 * Servicio de backups: BD (PDO puro) y archivos (ZipArchive)
 * Sin exec(), shell_exec(), mysqldump - compatible con hosting compartido
 */
class Backup
{
    private static string $backupDir = '';

    private static function dir(): string
    {
        if (self::$backupDir === '') {
            self::$backupDir = BASE_PATH . '/storage/backups';
            if (!is_dir(self::$backupDir)) {
                mkdir(self::$backupDir, 0755, true);
            }
        }
        return self::$backupDir;
    }

    /**
     * Exportar BD completa a archivo SQL usando PDO
     */
    public static function exportDatabase(): string|false
    {
        try {
            $pdo = Database::getInstance()->getPDO();
            $timestamp = date('Y-m-d_His');
            $filename = "backup_db_{$timestamp}.sql";
            $filepath = self::dir() . '/' . $filename;

            $handle = fopen($filepath, 'w');
            if (!$handle) return false;

            // Cabecera
            fwrite($handle, "-- Backup de BD: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Generado por Regalos Purranque v2\n\n");
            fwrite($handle, "SET NAMES utf8mb4;\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

            // Obtener todas las tablas
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);

            foreach ($tables as $table) {
                // Estructura
                $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $createSql = $create['Create Table'] ?? '';

                fwrite($handle, "-- -----------------------------------------------\n");
                fwrite($handle, "-- Tabla: {$table}\n");
                fwrite($handle, "-- -----------------------------------------------\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($handle, $createSql . ";\n\n");

                // Datos
                $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
                if ($count == 0) continue;

                $stmt = $pdo->query("SELECT * FROM `{$table}`");
                $columns = null;

                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    if ($columns === null) {
                        $columns = '`' . implode('`, `', array_keys($row)) . '`';
                    }

                    $values = array_map(function ($val) use ($pdo) {
                        if ($val === null) return 'NULL';
                        return $pdo->quote($val);
                    }, array_values($row));

                    fwrite($handle, "INSERT INTO `{$table}` ({$columns}) VALUES (" . implode(', ', $values) . ");\n");
                }
                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
            fclose($handle);

            return $filepath;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Exportar BD comprimida a .sql.gz
     */
    public static function exportDatabaseGz(): string|false
    {
        $sqlPath = self::exportDatabase();
        if (!$sqlPath) return false;

        try {
            $gzPath = preg_replace('/\.sql$/', '.sql.gz', $sqlPath);
            $gzHandle = gzopen($gzPath, 'wb9');
            if (!$gzHandle) {
                @unlink($sqlPath);
                return false;
            }

            $sqlHandle = fopen($sqlPath, 'rb');
            while (!feof($sqlHandle)) {
                gzwrite($gzHandle, fread($sqlHandle, 524288)); // 512KB chunks
            }
            fclose($sqlHandle);
            gzclose($gzHandle);

            @unlink($sqlPath);
            return $gzPath;
        } catch (\Throwable $e) {
            @unlink($sqlPath);
            return false;
        }
    }

    /**
     * Exportar archivos del sitio a ZIP (sin exec)
     */
    public static function exportFiles(array $exclude = []): string|false
    {
        if (!class_exists('ZipArchive')) return false;

        try {
            $timestamp = date('Y-m-d_His');
            $filename = "backup_files_{$timestamp}.zip";
            $filepath = self::dir() . '/' . $filename;

            $defaultExclude = [
                'storage/backups',
                '.git',
                'vendor',
                'node_modules',
            ];
            $exclude = array_merge($defaultExclude, $exclude);

            $zip = new \ZipArchive();
            if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return false;
            }

            self::addDirToZip($zip, BASE_PATH, BASE_PATH, $exclude);
            $zip->close();

            return $filepath;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Backup completo: BD + archivos en un solo ZIP
     */
    public static function exportFull(): string|false
    {
        try {
            $timestamp = date('Y-m-d_His');
            $filename = "backup_full_{$timestamp}.zip";
            $filepath = self::dir() . '/' . $filename;

            // Primero generar el SQL
            $sqlPath = self::exportDatabase();
            if (!$sqlPath) return false;

            $zip = new \ZipArchive();
            if ($zip->open($filepath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                @unlink($sqlPath);
                return false;
            }

            // Agregar SQL dump
            $zip->addFile($sqlPath, 'database/' . basename($sqlPath));

            // Agregar archivos del sitio
            $exclude = ['storage/backups', 'storage/logs', 'storage/cache', '.git', 'vendor', 'node_modules'];
            self::addDirToZip($zip, BASE_PATH, BASE_PATH, $exclude);

            $zip->close();

            // Eliminar SQL temporal
            @unlink($sqlPath);

            return $filepath;
        } catch (\Throwable $e) {
            error_log('[Backup] exportFull falló: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Listar backups existentes con metadata
     */
    public static function listBackups(): array
    {
        $dir = self::dir();
        $backups = [];

        $files = glob($dir . '/backup_*');
        if (!$files) return [];

        foreach ($files as $file) {
            $name = basename($file);
            $type = 'desconocido';
            if (str_starts_with($name, 'backup_db_')) $type = 'db';
            elseif (str_starts_with($name, 'backup_files_')) $type = 'files';
            elseif (str_starts_with($name, 'backup_full_')) $type = 'full';

            $backups[] = [
                'nombre'  => $name,
                'tipo'    => $type,
                'tamano'  => filesize($file),
                'fecha'   => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        // Ordenar por fecha desc
        usort($backups, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));

        return $backups;
    }

    /**
     * Eliminar un backup por nombre
     */
    public static function deleteBackup(string $filename): bool
    {
        // Prevenir path traversal
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            return false;
        }

        $filepath = self::dir() . '/' . $filename;
        $real = realpath($filepath);
        $realDir = realpath(self::dir());

        if (!$real || !$realDir || !str_starts_with($real, $realDir)) {
            return false;
        }

        if (file_exists($real)) {
            return unlink($real);
        }

        return false;
    }

    /**
     * Tamaño formateado de un archivo
     */
    public static function getBackupSize(string $filename): string
    {
        $filepath = self::dir() . '/' . $filename;
        if (!file_exists($filepath)) return '0 B';
        return self::formatSize(filesize($filepath));
    }

    /**
     * Espacio total usado por backups
     */
    public static function getTotalBackupSize(): int
    {
        $total = 0;
        $files = glob(self::dir() . '/backup_*');
        if ($files) {
            foreach ($files as $file) {
                $total += filesize($file);
            }
        }
        return $total;
    }

    /**
     * Espacio libre en disco
     */
    public static function getDiskFreeSpace(): int
    {
        return (int) @disk_free_space(BASE_PATH);
    }

    /**
     * Formatear bytes a unidad legible
     */
    public static function formatSize(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Agregar directorio recursivamente al ZIP
     */
    private static function addDirToZip(\ZipArchive $zip, string $dir, string $baseDir, array $exclude): void
    {
        $items = @scandir($dir);
        if (!$items) return;

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $fullPath = $dir . '/' . $item;
            $relativePath = ltrim(str_replace($baseDir, '', $fullPath), '/\\');

            // Verificar exclusiones
            $skip = false;
            foreach ($exclude as $ex) {
                if (str_starts_with($relativePath, $ex) || str_starts_with($relativePath, str_replace('/', '\\', $ex))) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            if (is_dir($fullPath)) {
                $zip->addEmptyDir($relativePath);
                self::addDirToZip($zip, $fullPath, $baseDir, $exclude);
            } else {
                // Limitar tamaño individual a 50MB
                if (filesize($fullPath) < 50 * 1024 * 1024) {
                    $zip->addFile($fullPath, $relativePath);
                }
            }
        }
    }

    /**
     * Eliminar backups automáticos antiguos (> días)
     */
    public static function cleanOldBackups(int $days = 30): int
    {
        $dir = self::dir();
        $cutoff = time() - ($days * 86400);
        $deleted = 0;

        $files = glob($dir . '/backup_*');
        if (!$files) return 0;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
