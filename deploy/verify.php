<?php
/**
 * Verificación Post-Deploy — Regalos Purranque v2
 * Estructura flat, sin dependencia de curl
 * URL: https://v2.regalos.purranque.info/deploy/verify.php?key=regalos_verify_2026
 * Se auto-elimina al confirmar.
 */

$VERIFY_KEY = 'regalos_verify_2026';
if (($_GET['key'] ?? '') !== $VERIFY_KEY) {
    http_response_code(403);
    die('Acceso denegado');
}

set_time_limit(30);
header('Content-Type: text/html; charset=utf-8');

$basePath = dirname(__DIR__);
$passed = 0;
$warnings = 0;
$total  = 0;

function check(string $name, bool $result, string $detail = ''): void {
    global $passed, $total;
    $total++;
    if ($result) $passed++;
    $cls = $result ? 'pass' : 'fail';
    $ico = $result ? '&#9989;' : '&#10060;';
    $det = $detail ? " &mdash; <small>{$detail}</small>" : '';
    echo "<div class=\"ck {$cls}\">{$ico} {$name}{$det}</div>\n";
    flush();
}

function warn(string $name, string $detail = ''): void {
    global $warnings;
    $warnings++;
    $det = $detail ? " &mdash; <small>{$detail}</small>" : '';
    echo "<div class=\"ck wrn\">&#9888;&#65039; {$name}{$det}</div>\n";
    flush();
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Verify Deploy</title>
<style>
body{font-family:system-ui;max-width:700px;margin:40px auto;padding:0 20px;background:#f8fafc;color:#1e293b}
h1{margin-bottom:5px}
.ck{padding:8px 14px;margin:4px 0;border-radius:6px;font-size:14px}
.pass{background:#dcfce7;color:#166534}.fail{background:#fee2e2;color:#991b1b}.wrn{background:#fef3c7;color:#854d0e}
.sc{font-size:2rem;font-weight:800;text-align:center;margin:20px 0}
.sc-g{color:#059669}.sc-m{color:#d97706}.sc-b{color:#dc2626}
h2{font-size:1rem;color:#64748b;margin:18px 0 6px;border-bottom:1px solid #e2e8f0;padding-bottom:4px}
</style></head><body>
<h1>Verificacion Post-Deploy</h1>
<p style="color:#64748b">Regalos Purranque v2 &mdash; Estructura flat</p>

<h2>PHP y extensiones</h2>
<?php

// 1. PHP
check('PHP >= 8.0', version_compare(PHP_VERSION, '8.0.0', '>='), 'PHP ' . PHP_VERSION);

// 2. Extensiones (sin curl)
$req = ['pdo_mysql', 'mbstring', 'gd', 'openssl', 'json', 'session', 'fileinfo'];
$miss = array_filter($req, fn($e) => !extension_loaded($e));
check('Extensiones PHP', empty($miss), empty($miss) ? implode(', ', $req) : 'Faltan: ' . implode(', ', $miss));

// curl es opcional
if (!extension_loaded('curl')) {
    warn('curl no disponible', 'Opcional — se usa file_get_contents como alternativa');
}

echo '<h2>Base de datos</h2>';

// 3. BD
$dbOk = false;
$dbErr = '';
try {
    $dbCfg = $basePath . '/config/database.php';
    if (file_exists($dbCfg)) {
        require_once $dbCfg;
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        $dbOk = true;
    } else {
        $dbErr = 'config/database.php no encontrado';
    }
} catch (PDOException $e) {
    $dbErr = $e->getMessage();
}
check('Conexion a BD', $dbOk, $dbOk ? DB_NAME . '@' . DB_HOST : $dbErr);

// 4. Tablas
if ($dbOk) {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $need = ['admin_usuarios', 'comercios', 'categorias', 'comercio_categoria', 'fechas_especiales',
             'comercio_fecha', 'noticias', 'banners', 'resenas', 'visitas_log', 'redes_sociales_config'];
    $missing = array_diff($need, $tables);
    check('Tablas BD (' . count($need) . ' requeridas)', empty($missing),
        empty($missing) ? count($tables) . ' tablas total' : 'Faltan: ' . implode(', ', $missing));
}

// 5. Admin
if ($dbOk) {
    $n = (int)$pdo->query("SELECT COUNT(*) FROM admin_usuarios WHERE rol='admin' AND activo=1")->fetchColumn();
    check('Usuario admin activo', $n > 0, "{$n} admin(s)");
}

echo '<h2>Archivos y estructura</h2>';

// 6. Archivos criticos (estructura flat)
$critical = [
    'index.php', 'router.php', '.htaccess',
    'config/app.php', 'config/database.php', 'config/routes.php', 'config/middleware.php',
    'assets/css/main.css', 'assets/js/app.js',
    'manifest.json', 'robots.txt', 'favicon.ico',
];
$missingFiles = [];
foreach ($critical as $f) {
    if (!file_exists($basePath . '/' . $f)) $missingFiles[] = $f;
}
check('Archivos criticos (' . count($critical) . ')', empty($missingFiles),
    empty($missingFiles) ? 'Todos presentes' : 'Faltan: ' . implode(', ', $missingFiles));

// 7. router.php existe
check('router.php (auto_prepend_file)', file_exists($basePath . '/router.php'));

// 8. .user.ini existe
$userIni = file_exists($basePath . '/.user.ini');
if ($userIni) {
    $iniContent = file_get_contents($basePath . '/.user.ini');
    $hasPrepend = str_contains($iniContent, 'auto_prepend_file') && !str_starts_with(trim($iniContent), ';');
    check('.user.ini con auto_prepend_file', $hasPrepend,
        $hasPrepend ? 'Configurado' : 'Existe pero auto_prepend_file no esta activo');
} else {
    warn('.user.ini no encontrado', 'Copiar .user.ini.example como .user.ini y configurar ruta');
}

// 9. .htaccess en raiz
check('.htaccess en raiz', file_exists($basePath . '/.htaccess'));

// 10. No existe carpeta public/
$oldPublic = is_dir($basePath . '/public');
if ($oldPublic) {
    warn('Carpeta public/ aun existe', 'Puede eliminarse si esta vacia');
}

echo '<h2>Permisos</h2>';

// 11. Permisos de carpetas (deben ser 755)
$checkDirs = ['app', 'app/Controllers', 'app/Core', 'app/Models', 'app/Services',
              'config', 'views', 'views/layouts', 'views/public', 'views/admin'];
$badPerms = [];
foreach ($checkDirs as $d) {
    $fp = $basePath . '/' . $d;
    if (is_dir($fp)) {
        $perms = substr(sprintf('%o', fileperms($fp)), -4);
        if ($perms !== '0755' && $perms !== '0775') {
            $badPerms[] = "$d ($perms)";
        }
    }
}
check('Carpetas del framework (755)', empty($badPerms),
    empty($badPerms) ? 'Todas OK' : 'Incorrectos: ' . implode(', ', $badPerms));

// 12. Permisos escritura storage
$wrDirs = ['storage/logs', 'storage/cache', 'storage/temp', 'storage/backups'];
$notWr = [];
foreach ($wrDirs as $d) {
    $fp = $basePath . '/' . $d;
    if (!is_dir($fp) || !is_writable($fp)) $notWr[] = $d;
}
check('Storage con escritura', empty($notWr),
    empty($notWr) ? count($wrDirs) . ' dirs OK' : 'Sin escritura: ' . implode(', ', $notWr));

// 13. Permisos escritura uploads
$imgDirs = ['assets/img/portadas', 'assets/img/logos', 'assets/img/noticias',
            'assets/img/galeria', 'assets/img/banners'];
$notWrImg = [];
foreach ($imgDirs as $d) {
    $fp = $basePath . '/' . $d;
    if (!is_dir($fp) || !is_writable($fp)) $notWrImg[] = $d;
}
check('Uploads con escritura', empty($notWrImg),
    empty($notWrImg) ? count($imgDirs) . ' dirs OK' : 'Sin escritura: ' . implode(', ', $notWrImg));

echo '<h2>Entorno</h2>';

// 14. APP_ENV
if (file_exists($basePath . '/config/app.php')) {
    require_once $basePath . '/config/app.php';
    if (defined('APP_ENV')) {
        check('APP_ENV = production', APP_ENV === 'production', 'Actual: ' . APP_ENV);
    }
}

// 15. HTTPS
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (($_SERVER['SERVER_PORT'] ?? 0) == 443);
if ($https) { check('HTTPS activo', true); }
else { warn('HTTPS no detectado', 'Normal en localhost'); }

// 16. OG image
$ogExists = file_exists($basePath . '/assets/img/og/default.jpg');
check('OG image default', $ogExists, $ogExists ? 'assets/img/og/default.jpg' : 'No encontrada');

// 17. storage protegido
check('storage/ protegido', file_exists($basePath . '/storage/backups/.htaccess'));

echo '<h2>Test de rutas</h2>';

// 18. Test de rutas usando file_get_contents (sin curl)
$siteUrl = defined('SITE_URL') ? SITE_URL : '';
if ($siteUrl) {
    $testPaths = ['/' => 'Home'];
    foreach ($testPaths as $path => $name) {
        $testUrl = $siteUrl . $path;
        $ctx = stream_context_create([
            'http' => ['timeout' => 5, 'ignore_errors' => true],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $response = @file_get_contents($testUrl, false, $ctx);
        $code = 0;
        if (isset($http_response_header[0])) {
            preg_match('/\d{3}/', $http_response_header[0], $m);
            $code = (int)($m[0] ?? 0);
        }
        if ($response !== false && $code === 200) {
            check("Ruta {$name} ({$path}) responde", true, "HTTP {$code}");
        } elseif ($response !== false) {
            check("Ruta {$name} ({$path}) responde", false, "HTTP {$code}");
        } else {
            warn("Ruta {$name} ({$path})", 'No se pudo conectar — verificar manualmente');
        }
    }
} else {
    warn('Test de rutas omitido', 'SITE_URL no definida');
}

// Score
$score = $total > 0 ? round(($passed / $total) * 100) : 0;
$cls = $score >= 80 ? 'sc-g' : ($score >= 50 ? 'sc-m' : 'sc-b');
echo "<div class=\"sc {$cls}\">{$passed}/{$total} OK ({$score}%)</div>\n";

if ($warnings > 0) {
    echo "<p style='text-align:center;color:#d97706'>{$warnings} advertencia(s)</p>";
}

if ($score >= 80) {
    echo '<p style="text-align:center;color:#059669;font-weight:600">Deploy verificado. El sitio esta listo.</p>';
} else {
    echo '<p style="text-align:center;color:#dc2626;font-weight:600">Hay verificaciones fallidas. Revisar arriba.</p>';
}

// Auto-delete option
if (isset($_GET['delete'])) {
    @unlink(__FILE__);
    echo file_exists(__FILE__)
        ? '<p style="color:red;text-align:center">No se pudo eliminar. Hazlo manualmente.</p>'
        : '<p style="color:green;text-align:center">verify.php eliminado correctamente.</p>';
} else {
    $delUrl = "?key={$VERIFY_KEY}&delete=1";
    echo "<p style='text-align:center;margin-top:20px'><a href='{$delUrl}' style='color:#dc2626;font-weight:bold'>Eliminar verify.php del servidor</a></p>";
}
?>
</body></html>
