<?php
/**
 * Fix Permissions — Regalos Purranque v2
 * Ejecutar UNA VEZ después de subir el ZIP al servidor.
 * Se auto-elimina al terminar.
 *
 * IMPORTANTE: Los ZIP extraídos en cPanel asignan 0644 a TODO (carpetas y archivos).
 * Las carpetas DEBEN ser 0755 para que PHP pueda leer su contenido.
 */

if (php_sapi_name() === 'cli') {
    echo "Este script debe ejecutarse desde el navegador.\n";
    exit(1);
}

set_time_limit(120);
$baseDir = dirname(__DIR__);
$dirCount = 0;
$fileCount = 0;
$dirErrors = [];
$storageCount = 0;

// ── PASO 1: Todas las carpetas → 0755 ──
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $path = $item->getRealPath();

    // Saltar .git y carpetas del sistema
    if (strpos($path, '.git') !== false || strpos($path, '.well-known') !== false || strpos($path, 'cgi-bin') !== false) {
        continue;
    }

    if ($item->isDir()) {
        if (@chmod($path, 0755)) {
            $dirCount++;
        } else {
            $dirErrors[] = str_replace($baseDir, '', $path);
        }
    } elseif ($item->isFile()) {
        @chmod($path, 0644);
        $fileCount++;
    }
}

// ── PASO 2: storage/ y subdirectorios → 0775 ──
$storagePaths = [
    'storage', 'storage/logs', 'storage/cache', 'storage/temp', 'storage/backups',
];
foreach ($storagePaths as $dir) {
    $full = $baseDir . '/' . $dir;
    if (is_dir($full) && @chmod($full, 0775)) {
        $storageCount++;
    }
}

// ── PASO 3: assets/img/ y subdirectorios → 0775 (para uploads) ──
$imgPaths = [
    'assets/img', 'assets/img/portadas', 'assets/img/logos',
    'assets/img/noticias', 'assets/img/noticias/contenido', 'assets/img/noticias/thumbs',
    'assets/img/galeria', 'assets/img/banners', 'assets/img/og', 'assets/img/og/thumbs',
    'assets/img/config',
];
foreach ($imgPaths as $dir) {
    $full = $baseDir . '/' . $dir;
    if (is_dir($full) && @chmod($full, 0775)) {
        $storageCount++;
    }
}

$hasErrors = count($dirErrors) > 0;
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Fix Permissions</title>
<style>
body{font-family:system-ui;max-width:650px;margin:40px auto;padding:20px;background:#f8fafc;color:#1e293b}
h1{margin-bottom:5px}
.ok{color:#16a34a;font-weight:bold}.err{color:#dc2626;font-weight:bold}
.summary{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:20px;margin:15px 0}
.summary p{margin:6px 0}
pre{background:#1e293b;color:#f8fafc;padding:15px;border-radius:8px;font-size:13px;overflow-x:auto}
</style>
</head>
<body>
<h1>Fix Permissions</h1>
<p style="color:#64748b">Regalos Purranque v2</p>

<div class="summary">
    <p>Carpetas procesadas: <strong><?= $dirCount ?></strong> → 0755</p>
    <p>Archivos procesados: <strong><?= $fileCount ?></strong> → 0644</p>
    <p>Storage + uploads: <strong><?= $storageCount ?></strong> → 0775</p>
</div>

<?php if (!$hasErrors): ?>
    <p class="ok">Todo correcto. 0 errores.</p>
<?php else: ?>
    <p class="err"><?= count($dirErrors) ?> carpetas no pudieron cambiarse:</p>
    <pre><?= htmlspecialchars(implode("\n", $dirErrors)) ?></pre>
<?php endif; ?>

<hr>
<p><strong>Auto-eliminando este archivo...</strong></p>
<?php
@unlink(__FILE__);
echo file_exists(__FILE__)
    ? '<p class="err">No se pudo auto-eliminar. Eliminalo manualmente desde File Manager.</p>'
    : '<p class="ok">fix-permissions.php eliminado correctamente.</p>';
?>
</body>
</html>
