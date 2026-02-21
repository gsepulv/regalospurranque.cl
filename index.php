<?php
/**
 * Regalos Purranque v2 — Front Controller
 * Único punto de entrada de la aplicación
 */

// Base path del proyecto (raíz — estructura flat para hosting compartido)
define('BASE_PATH', __DIR__);

// OPcache: invalidar archivos modificados (one-time fix, eliminar después)
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__DIR__ . '/app/Services/GoogleDrive.php', true);
}

// Cargar configuración
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';
if (file_exists(BASE_PATH . '/config/captcha.php')) {
    require BASE_PATH . '/config/captcha.php';
}
if (file_exists(BASE_PATH . '/config/backup.php')) {
    require BASE_PATH . '/config/backup.php';
}

// Autoloader PSR-4 sin Composer
spl_autoload_register(function (string $class): void {
    // App\Core\Router → app/Core/Router.php
    $path = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    // Corregir mayúscula: App/ → app/
    $path = str_replace('/App/', '/app/', $path);
    if (file_exists($path)) {
        require_once $path;
    }
});

// Manejo de errores según entorno
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_PATH . '/php_errors.log');
}

// Bootstrap y ejecución
try {
    $app = new App\Core\App();
    $app->run();
} catch (\Throwable $e) {
    if (APP_DEBUG) {
        http_response_code(500);
        echo '<div style="font-family:system-ui;max-width:900px;margin:40px auto;padding:20px;">';
        echo '<h1 style="color:#e11d48;">Error de aplicación</h1>';
        echo '<p style="color:#64748b;font-size:1.1rem;">' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre style="background:#1e293b;color:#f8fafc;padding:20px;border-radius:8px;overflow-x:auto;font-size:13px;">';
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre></div>';
    } else {
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error</title></head>';
        echo '<body style="font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#f8fafc;">';
        echo '<div style="text-align:center"><h1 style="color:#1e293b;">Error del servidor</h1>';
        echo '<p style="color:#64748b;">Lo sentimos, ha ocurrido un error inesperado.</p>';
        echo '<a href="/" style="color:#2563eb;">Volver al inicio</a></div></body></html>';
    }
}
