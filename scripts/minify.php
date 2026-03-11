<?php
/**
 * Minificación básica de CSS y JS sin dependencias externas.
 * Uso: php scripts/minify.php
 *
 * Genera archivos .min.css y .min.js junto a los originales.
 */

$basePath = dirname(__DIR__);

// CSS: eliminar comentarios, espacios redundantes, saltos de línea
function minifyCss(string $css): string
{
    // Eliminar comentarios /* ... */
    $css = preg_replace('/\/\*[\s\S]*?\*\//', '', $css);
    // Eliminar espacios antes/después de { } : ; ,
    $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
    // Colapsar espacios múltiples
    $css = preg_replace('/\s+/', ' ', $css);
    // Eliminar ; antes de }
    $css = str_replace(';}', '}', $css);
    return trim($css);
}

// JS: eliminar comentarios de línea y multi-línea, colapsar espacios (conservador)
function minifyJs(string $js): string
{
    // Eliminar comentarios multi-línea
    $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
    // Eliminar comentarios de línea (cuidando URLs con //)
    $js = preg_replace('/(?<![:\'"\\\\])\/\/[^\n]*/', '', $js);
    // Colapsar líneas vacías
    $js = preg_replace('/\n\s*\n/', "\n", $js);
    // Eliminar espacios al inicio de línea
    $js = preg_replace('/^\s+/m', '', $js);
    return trim($js);
}

$files = [
    ['css', $basePath . '/assets/css/rp2.css'],
    ['js',  $basePath . '/assets/js/app.js'],
    ['css', $basePath . '/assets/css/mapa.css'],
    ['js',  $basePath . '/assets/js/mapa.js'],
];

foreach ($files as [$type, $path]) {
    if (!file_exists($path)) {
        echo "SKIP: {$path} (no existe)\n";
        continue;
    }

    $content = file_get_contents($path);
    $original = strlen($content);

    $minified = $type === 'css' ? minifyCss($content) : minifyJs($content);
    $newSize = strlen($minified);

    $ext = $type === 'css' ? '.min.css' : '.min.js';
    $outPath = preg_replace('/\.(css|js)$/', $ext, $path);

    file_put_contents($outPath, $minified);

    $reduction = round((1 - $newSize / $original) * 100, 1);
    $base = basename($path);
    echo "OK: {$base} → " . basename($outPath) . " ({$original} → {$newSize} bytes, -{$reduction}%)\n";
}

echo "\nMinificación completada.\n";
echo "Recuerda actualizar las referencias en layouts para usar los archivos .min\n";
