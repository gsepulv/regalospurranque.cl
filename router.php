<?php
/**
 * Router — Regalos Purranque v2
 *
 * Uso dual:
 *   1) Desarrollo local:  php -S localhost:8000 router.php
 *   2) Producción:        auto_prepend_file via .user.ini
 *
 * En hostings donde mod_rewrite no redirige a index.php (PHP-FPM),
 * este archivo se configura como auto_prepend_file en .user.ini.
 * Detecta si la URL es un archivo real o una ruta del framework
 * y, en el segundo caso, incluye index.php directamente.
 */

// ── CLI: cron jobs, scripts — no interferir ──
if (php_sapi_name() === 'cli') {
    return;
}

// ── PHP built-in server (desarrollo local) ──
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && is_file(__DIR__ . $path)) {
        return false; // Servir archivo estático
    }
    require __DIR__ . '/index.php';
    return true;
}

// ── Producción: auto_prepend_file ──

// Si ya estamos ejecutando index.php, no re-incluir (evitar loop)
$scriptName = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
if ($scriptName === 'index.php') {
    return;
}

// Scripts de deploy que se ejecutan directamente
$directScripts = ['fix-permissions.php', 'verify.php'];
if (in_array($scriptName, $directScripts)) {
    return;
}

// Si el request es para un archivo real (CSS, JS, imagen), dejar pasar
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$filePath = __DIR__ . $uri;
if ($uri !== '/' && is_file($filePath)) {
    return;
}

// Es una ruta del framework → incluir index.php y salir
require __DIR__ . '/index.php';
exit;
