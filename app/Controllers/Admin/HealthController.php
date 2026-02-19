<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\Backup;

/**
 * Diagnóstico de salud del sistema
 * Ejecuta múltiples verificaciones y calcula un puntaje global
 */
class HealthController extends Controller
{
    /**
     * GET /admin/mantenimiento/salud
     * Mostrar panel de diagnóstico de salud
     */
    public function index(): void
    {
        $checks     = $this->runAllChecks();
        $score      = $this->calculateScore($checks);
        $serverInfo = $this->getServerInfo();
        $dbSizeData = $this->getDbSizeData();

        $this->render('admin/mantenimiento/salud', [
            'title'       => 'Salud del Sistema — ' . SITE_NAME,
            'tab'         => 'salud',
            'checks'      => $checks,
            'score'       => $score,
            'serverInfo'  => $serverInfo,
            'dbSize'      => $dbSizeData['tables'],
            'totalDbSize' => $dbSizeData['total'],
        ]);
    }

    /**
     * POST /admin/mantenimiento/salud/refresh
     * Refrescar diagnóstico y redirigir
     */
    public function refresh(): void
    {
        $this->redirect('/admin/mantenimiento/salud', [
            'success' => 'Diagnóstico actualizado correctamente',
        ]);
    }

    // ───────────────────────────────────────────────
    // Motor de verificaciones
    // ───────────────────────────────────────────────

    /**
     * Ejecutar todas las verificaciones de salud
     * @return array<int, array{name: string, status: string, detail: string, points: int, maxPoints: int}>
     */
    private function runAllChecks(): array
    {
        $checks = [];

        $checks[] = $this->checkPhpVersion();
        $checks[] = $this->checkMysqlConnection();
        $checks[] = $this->checkMysqlVersion();
        $checks[] = $this->checkPhpExtensions();
        $checks[] = $this->checkStorageWritable();
        $checks[] = $this->checkPublicImgWritable();
        $checks[] = $this->checkHtaccess();
        $checks[] = $this->checkDatabaseConfig();
        $checks[] = $this->checkSsl();
        $checks[] = $this->checkDbSize();
        $checks[] = $this->checkDiskSpace();
        $checks[] = $this->checkDbTables();
        $checks[] = $this->checkAdminUser();
        $checks[] = $this->checkExpiredSessions();
        $checks[] = $this->checkOldLogs();
        $checks[] = $this->checkCacheFolder();
        $checks[] = $this->checkRecentBackup();
        $checks[] = $this->checkPhpErrorLog();

        return $checks;
    }

    /**
     * Calcular puntaje global (0-100)
     */
    private function calculateScore(array $checks): float
    {
        $obtained = 0;
        $max      = 0;

        foreach ($checks as $check) {
            $obtained += $check['points'];
            $max      += $check['maxPoints'];
        }

        if ($max === 0) return 0;

        return round(($obtained / $max) * 100, 1);
    }

    /**
     * Obtener tamaño de tablas de la BD para la vista
     */
    private function getDbSizeData(): array
    {
        try {
            $pdo = $this->db->getPDO();
            $stmt = $pdo->query(
                "SELECT table_name AS name,
                        (data_length + index_length) AS size
                 FROM information_schema.TABLES
                 WHERE table_schema = DATABASE()
                 ORDER BY size DESC"
            );
            $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $total = 0;
            foreach ($tables as $t) {
                $total += (int) $t['size'];
            }

            return ['tables' => $tables, 'total' => $total];
        } catch (\Throwable $e) {
            return ['tables' => [], 'total' => 0];
        }
    }

    /**
     * Recopilar información del servidor
     */
    private function getServerInfo(): array
    {
        $info = [
            'php_version'   => PHP_VERSION,
            'mysql_version' => 'Desconocida',
            'os'            => PHP_OS . ' ' . php_uname('r'),
            'disk_space'    => Backup::formatSize(Backup::getDiskFreeSpace()),
            'disk_total'    => Backup::formatSize((int) @disk_total_space(BASE_PATH)),
            'memory_limit'  => ini_get('memory_limit') ?: 'No definido',
            'max_upload'    => ini_get('upload_max_filesize') ?: 'No definido',
            'max_post'      => ini_get('post_max_size') ?: 'No definido',
        ];

        try {
            $stmt = $this->db->getPDO()->query("SELECT VERSION()");
            $info['mysql_version'] = $stmt->fetchColumn();
        } catch (\Throwable $e) {
            // Se mantiene "Desconocida"
        }

        return $info;
    }

    // ───────────────────────────────────────────────
    // Verificaciones individuales
    // ───────────────────────────────────────────────

    /**
     * 1. Versión de PHP >= 8.0 (10 pts)
     */
    private function checkPhpVersion(): array
    {
        $version = PHP_VERSION;
        $ok      = version_compare($version, '8.0.0', '>=');

        return [
            'name'      => 'Versión de PHP',
            'status'    => $ok ? 'ok' : 'error',
            'detail'    => "PHP {$version}" . ($ok ? '' : ' — Se requiere PHP 8.0 o superior'),
            'points'    => $ok ? 10 : 0,
            'maxPoints' => 10,
        ];
    }

    /**
     * 2. Conexión a MySQL (10 pts)
     */
    private function checkMysqlConnection(): array
    {
        try {
            $this->db->getPDO()->query("SELECT 1");
            return [
                'name'      => 'Conexión a MySQL',
                'status'    => 'ok',
                'detail'    => 'Conexión establecida correctamente',
                'points'    => 10,
                'maxPoints' => 10,
            ];
        } catch (\Throwable $e) {
            return [
                'name'      => 'Conexión a MySQL',
                'status'    => 'error',
                'detail'    => 'No se pudo conectar: ' . $e->getMessage(),
                'points'    => 0,
                'maxPoints' => 10,
            ];
        }
    }

    /**
     * 3. Versión de MySQL >= 8.0 (5 pts)
     */
    private function checkMysqlVersion(): array
    {
        try {
            $version = $this->db->getPDO()->query("SELECT VERSION()")->fetchColumn();
            // Extraer solo la parte numérica (ej: "8.0.36-0ubuntu0.22.04.1" => "8.0.36")
            preg_match('/^(\d+\.\d+(\.\d+)?)/', $version, $matches);
            $numericVersion = $matches[1] ?? '0.0.0';

            $ok = version_compare($numericVersion, '8.0.0', '>=');

            return [
                'name'      => 'Versión de MySQL',
                'status'    => $ok ? 'ok' : 'warning',
                'detail'    => "MySQL {$version}" . ($ok ? '' : ' — Se recomienda MySQL 8.0+'),
                'points'    => $ok ? 5 : 2,
                'maxPoints' => 5,
            ];
        } catch (\Throwable $e) {
            return [
                'name'      => 'Versión de MySQL',
                'status'    => 'error',
                'detail'    => 'No se pudo determinar la versión',
                'points'    => 0,
                'maxPoints' => 5,
            ];
        }
    }

    /**
     * 4. Extensiones PHP requeridas (5 pts)
     */
    private function checkPhpExtensions(): array
    {
        $required = ['pdo_mysql', 'mbstring', 'gd', 'openssl', 'json', 'session', 'fileinfo'];
        $missing  = [];

        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $missing[] = $ext;
            }
        }

        if (empty($missing)) {
            return [
                'name'      => 'Extensiones PHP',
                'status'    => 'ok',
                'detail'    => 'Todas las extensiones requeridas están instaladas (' . implode(', ', $required) . ')',
                'points'    => 5,
                'maxPoints' => 5,
            ];
        }

        $allMissing = count($missing) === count($required);
        return [
            'name'      => 'Extensiones PHP',
            'status'    => $allMissing ? 'error' : 'warning',
            'detail'    => 'Extensiones faltantes: ' . implode(', ', $missing),
            'points'    => $allMissing ? 0 : 2,
            'maxPoints' => 5,
        ];
    }

    /**
     * 5. Directorios de storage/ con permisos de escritura (10 pts)
     */
    private function checkStorageWritable(): array
    {
        $dirs = [
            'storage/backups',
            'storage/logs',
            'storage/cache',
            'storage/temp',
        ];

        $notWritable = [];
        foreach ($dirs as $dir) {
            $fullPath = BASE_PATH . '/' . $dir;
            if (!is_dir($fullPath)) {
                $notWritable[] = $dir . ' (no existe)';
            } elseif (!is_writable($fullPath)) {
                $notWritable[] = $dir;
            }
        }

        if (empty($notWritable)) {
            return [
                'name'      => 'Directorio storage/',
                'status'    => 'ok',
                'detail'    => 'Todos los subdirectorios de storage/ tienen permisos de escritura',
                'points'    => 10,
                'maxPoints' => 10,
            ];
        }

        $allFail = count($notWritable) === count($dirs);
        return [
            'name'      => 'Directorio storage/',
            'status'    => $allFail ? 'error' : 'warning',
            'detail'    => 'Sin permisos de escritura: ' . implode(', ', $notWritable),
            'points'    => $allFail ? 0 : 5,
            'maxPoints' => 10,
        ];
    }

    /**
     * 6. assets/img/ con permisos de escritura (5 pts)
     */
    private function checkPublicImgWritable(): array
    {
        $path = BASE_PATH . '/assets/img';

        if (!is_dir($path)) {
            return [
                'name'      => 'Directorio de imágenes',
                'status'    => 'error',
                'detail'    => 'El directorio assets/img/ no existe',
                'points'    => 0,
                'maxPoints' => 5,
            ];
        }

        $writable = is_writable($path);
        return [
            'name'      => 'Directorio de imágenes',
            'status'    => $writable ? 'ok' : 'warning',
            'detail'    => $writable
                ? 'assets/img/ tiene permisos de escritura'
                : 'assets/img/ no tiene permisos de escritura',
            'points'    => $writable ? 5 : 0,
            'maxPoints' => 5,
        ];
    }

    /**
     * 7. .htaccess existe en raíz (5 pts)
     */
    private function checkHtaccess(): array
    {
        $exists = file_exists(BASE_PATH . '/.htaccess');
        return [
            'name'      => 'Archivo .htaccess',
            'status'    => $exists ? 'ok' : 'error',
            'detail'    => $exists
                ? '.htaccess existe'
                : '.htaccess no encontrado — el enrutamiento podría no funcionar',
            'points'    => $exists ? 5 : 0,
            'maxPoints' => 5,
        ];
    }

    /**
     * 8. config/database.php existe (5 pts)
     */
    private function checkDatabaseConfig(): array
    {
        $exists = file_exists(BASE_PATH . '/config/database.php');
        return [
            'name'      => 'Configuración de BD',
            'status'    => $exists ? 'ok' : 'error',
            'detail'    => $exists
                ? 'config/database.php existe'
                : 'config/database.php no encontrado — la conexión a BD podría fallar',
            'points'    => $exists ? 5 : 0,
            'maxPoints' => 5,
        ];
    }

    /**
     * 9. SSL/HTTPS activo — solo verificar en producción (3 pts)
     */
    private function checkSsl(): array
    {
        // Solo verificar en producción
        if (defined('APP_ENV') && APP_ENV !== 'production') {
            return [
                'name'      => 'SSL/HTTPS',
                'status'    => 'ok',
                'detail'    => 'Verificación omitida en entorno de desarrollo (APP_ENV=' . APP_ENV . ')',
                'points'    => 3,
                'maxPoints' => 3,
            ];
        }

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (!empty($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
            || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        return [
            'name'      => 'SSL/HTTPS',
            'status'    => $isHttps ? 'ok' : 'warning',
            'detail'    => $isHttps
                ? 'Conexión HTTPS activa'
                : 'El sitio no está usando HTTPS — se recomienda activar SSL',
            'points'    => $isHttps ? 3 : 0,
            'maxPoints' => 3,
        ];
    }

    /**
     * 10. Tamaño de la base de datos (3 pts — informativo)
     */
    private function checkDbSize(): array
    {
        try {
            $pdo = $this->db->getPDO();
            $stmt = $pdo->query(
                "SELECT table_name AS table_name,
                        (data_length + index_length) AS size
                 FROM information_schema.TABLES
                 WHERE table_schema = DATABASE()
                 ORDER BY size DESC"
            );
            $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $totalSize = 0;
            $tableDetails = [];
            foreach ($tables as $table) {
                $size = (int) $table['size'];
                $totalSize += $size;
                $tableDetails[] = $table['table_name'] . ': ' . Backup::formatSize($size);
            }

            $tableCount = count($tables);
            return [
                'name'      => 'Tamaño de Base de Datos',
                'status'    => 'ok',
                'detail'    => "Total: " . Backup::formatSize($totalSize) . " en {$tableCount} tablas. "
                    . implode(', ', array_slice($tableDetails, 0, 5))
                    . ($tableCount > 5 ? '...' : ''),
                'points'    => 3,
                'maxPoints' => 3,
            ];
        } catch (\Throwable $e) {
            return [
                'name'      => 'Tamaño de Base de Datos',
                'status'    => 'error',
                'detail'    => 'No se pudo obtener información: ' . $e->getMessage(),
                'points'    => 0,
                'maxPoints' => 3,
            ];
        }
    }

    /**
     * 11. Espacio en disco (5 pts)
     */
    private function checkDiskSpace(): array
    {
        $freeBytes = Backup::getDiskFreeSpace();
        $freeMb    = $freeBytes / (1024 * 1024);

        if ($freeMb > 500) {
            $status = 'ok';
            $points = 5;
        } elseif ($freeMb > 100) {
            $status = 'warning';
            $points = 3;
        } else {
            $status = 'error';
            $points = 0;
        }

        return [
            'name'      => 'Espacio en Disco',
            'status'    => $status,
            'detail'    => 'Espacio libre: ' . Backup::formatSize($freeBytes),
            'points'    => $points,
            'maxPoints' => 5,
        ];
    }

    /**
     * 12. Tablas en la BD — se esperan 22+ tablas (5 pts)
     */
    private function checkDbTables(): array
    {
        try {
            $pdo = $this->db->getPDO();
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            $count  = count($tables);

            $ok = $count >= 22;
            return [
                'name'      => 'Tablas de Base de Datos',
                'status'    => $ok ? 'ok' : 'warning',
                'detail'    => "{$count} tablas encontradas" . ($ok ? '' : ' — se esperan al menos 22 tablas'),
                'points'    => $ok ? 5 : 2,
                'maxPoints' => 5,
            ];
        } catch (\Throwable $e) {
            return [
                'name'      => 'Tablas de Base de Datos',
                'status'    => 'error',
                'detail'    => 'No se pudo consultar las tablas: ' . $e->getMessage(),
                'points'    => 0,
                'maxPoints' => 5,
            ];
        }
    }

    /**
     * 13. Al menos 1 usuario admin activo (3 pts)
     */
    private function checkAdminUser(): array
    {
        try {
            $count = $this->db->count('admin_usuarios', "rol = 'admin' AND activo = 1");

            if ($count > 0) {
                return [
                    'name'      => 'Usuario Administrador',
                    'status'    => 'ok',
                    'detail'    => "{$count} usuario(s) administrador(es) activo(s)",
                    'points'    => 3,
                    'maxPoints' => 3,
                ];
            }

            return [
                'name'      => 'Usuario Administrador',
                'status'    => 'error',
                'detail'    => 'No hay usuarios administradores activos',
                'points'    => 0,
                'maxPoints' => 3,
            ];
        } catch (\Throwable $e) {
            return [
                'name'      => 'Usuario Administrador',
                'status'    => 'warning',
                'detail'    => 'No se pudo verificar: ' . $e->getMessage(),
                'points'    => 0,
                'maxPoints' => 3,
            ];
        }
    }

    /**
     * 14. Sesiones expiradas — sugerir limpieza (3 pts)
     */
    private function checkExpiredSessions(): array
    {
        try {
            $count = $this->db->count(
                'sesiones_admin',
                'expira < NOW()'
            );

            if ($count === 0) {
                return [
                    'name'      => 'Sesiones Expiradas',
                    'status'    => 'ok',
                    'detail'    => 'No hay sesiones expiradas pendientes de limpieza',
                    'points'    => 3,
                    'maxPoints' => 3,
                ];
            }

            return [
                'name'      => 'Sesiones Expiradas',
                'status'    => $count > 100 ? 'warning' : 'ok',
                'detail'    => "{$count} sesión(es) expirada(s) — se recomienda limpiar periódicamente",
                'points'    => $count > 100 ? 2 : 3,
                'maxPoints' => 3,
            ];
        } catch (\Throwable $e) {
            // Tabla podría no existir
            return [
                'name'      => 'Sesiones Expiradas',
                'status'    => 'warning',
                'detail'    => 'No se pudo verificar (¿tabla sesiones existe?)',
                'points'    => 1,
                'maxPoints' => 3,
            ];
        }
    }

    /**
     * 15. Logs antiguos > 90 días (3 pts)
     */
    private function checkOldLogs(): array
    {
        try {
            $count = $this->db->count(
                'admin_log',
                'created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)'
            );

            if ($count === 0) {
                return [
                    'name'      => 'Logs Antiguos',
                    'status'    => 'ok',
                    'detail'    => 'No hay registros de log con más de 90 días',
                    'points'    => 3,
                    'maxPoints' => 3,
                ];
            }

            return [
                'name'      => 'Logs Antiguos',
                'status'    => $count > 1000 ? 'warning' : 'ok',
                'detail'    => "{$count} registro(s) de log con más de 90 días" . ($count > 1000 ? ' — se recomienda purgar' : ''),
                'points'    => $count > 1000 ? 2 : 3,
                'maxPoints' => 3,
            ];
        } catch (\Throwable $e) {
            return [
                'name'      => 'Logs Antiguos',
                'status'    => 'warning',
                'detail'    => 'No se pudo verificar (¿tabla admin_logs existe?)',
                'points'    => 1,
                'maxPoints' => 3,
            ];
        }
    }

    /**
     * 16. Estado de la carpeta de caché (3 pts)
     */
    private function checkCacheFolder(): array
    {
        $cachePath = BASE_PATH . '/storage/cache';

        if (!is_dir($cachePath)) {
            return [
                'name'      => 'Carpeta de Caché',
                'status'    => 'warning',
                'detail'    => 'storage/cache/ no existe — se creará automáticamente cuando sea necesario',
                'points'    => 1,
                'maxPoints' => 3,
            ];
        }

        if (!is_writable($cachePath)) {
            return [
                'name'      => 'Carpeta de Caché',
                'status'    => 'error',
                'detail'    => 'storage/cache/ no tiene permisos de escritura',
                'points'    => 0,
                'maxPoints' => 3,
            ];
        }

        // Contar archivos en caché
        $files = @scandir($cachePath);
        $fileCount = $files ? count(array_diff($files, ['.', '..'])) : 0;

        return [
            'name'      => 'Carpeta de Caché',
            'status'    => 'ok',
            'detail'    => "storage/cache/ operativo ({$fileCount} archivo(s) en caché)",
            'points'    => 3,
            'maxPoints' => 3,
        ];
    }

    /**
     * 17. Backup reciente en los últimos 7 días (5 pts)
     */
    private function checkRecentBackup(): array
    {
        $backups = Backup::listBackups();

        if (empty($backups)) {
            return [
                'name'      => 'Backup Reciente',
                'status'    => 'error',
                'detail'    => 'No se encontraron backups — se recomienda crear uno',
                'points'    => 0,
                'maxPoints' => 5,
            ];
        }

        // Los backups ya vienen ordenados por fecha desc
        $lastBackup = $backups[0];
        $lastDate   = strtotime($lastBackup['fecha']);
        $sevenDaysAgo = strtotime('-7 days');

        if ($lastDate >= $sevenDaysAgo) {
            return [
                'name'      => 'Backup Reciente',
                'status'    => 'ok',
                'detail'    => "Último backup: {$lastBackup['nombre']} ({$lastBackup['fecha']})",
                'points'    => 5,
                'maxPoints' => 5,
            ];
        }

        return [
            'name'      => 'Backup Reciente',
            'status'    => 'warning',
            'detail'    => "Último backup hace más de 7 días: {$lastBackup['fecha']} — se recomienda crear uno nuevo",
            'points'    => 2,
            'maxPoints' => 5,
        ];
    }

    /**
     * 18. Log de errores PHP (3 pts)
     */
    private function checkPhpErrorLog(): array
    {
        $errorLog = ini_get('error_log');

        // Verificar también en ubicaciones comunes del proyecto
        $projectLog = BASE_PATH . '/storage/logs/php_errors.log';
        $hasProjectLog = file_exists($projectLog);

        if ($errorLog && file_exists($errorLog)) {
            $size = filesize($errorLog);
            $sizeFormatted = Backup::formatSize($size);
            $sizeMb = $size / (1024 * 1024);

            if ($sizeMb > 50) {
                return [
                    'name'      => 'Log de Errores PHP',
                    'status'    => 'warning',
                    'detail'    => "Log de errores muy grande: {$sizeFormatted} ({$errorLog}) — considere rotarlo",
                    'points'    => 1,
                    'maxPoints' => 3,
                ];
            }

            return [
                'name'      => 'Log de Errores PHP',
                'status'    => 'ok',
                'detail'    => "Log de errores configurado: {$sizeFormatted} ({$errorLog})",
                'points'    => 3,
                'maxPoints' => 3,
            ];
        }

        if ($hasProjectLog) {
            $size = filesize($projectLog);
            $sizeFormatted = Backup::formatSize($size);

            return [
                'name'      => 'Log de Errores PHP',
                'status'    => 'ok',
                'detail'    => "Log del proyecto encontrado: {$sizeFormatted} (storage/logs/php_errors.log)",
                'points'    => 3,
                'maxPoints' => 3,
            ];
        }

        return [
            'name'      => 'Log de Errores PHP',
            'status'    => 'warning',
            'detail'    => 'No se detectó un archivo de log de errores PHP configurado',
            'points'    => 1,
            'maxPoints' => 3,
        ];
    }
}
