<?php
/**
 * Script de optimización masiva: genera versiones WebP de imágenes existentes
 *
 * Uso CLI:   php scripts/optimizar-imagenes.php
 * Uso web:   Proteger con token o IP
 *
 * NO modifica los originales — solo crea archivos .webp adicionales
 */

// Protección básica: solo CLI o con token secreto
$esCli = php_sapi_name() === 'cli';
if (!$esCli) {
    $token = $_GET['token'] ?? '';
    if ($token !== 'optimizar_' . date('Ymd')) {
        http_response_code(403);
        die('Acceso denegado');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

// Base path del proyecto
define('BASE_PATH', dirname(__DIR__));
$imgBase = BASE_PATH . '/assets/img';

// Verificar soporte WebP
if (!function_exists('imagewebp')) {
    echo "ERROR: PHP GD no soporta WebP. Verificar con: php -r \"var_dump(gd_info());\"\n";
    exit(1);
}

echo "=== Optimizador de Imágenes → WebP ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Carpetas a procesar (ruta => incluir thumbs)
$carpetas = [
    'portadas'           => true,
    'logos'              => true,
    'galeria'            => true,
    'noticias'           => true,
    'noticias/contenido' => false,
    'banners'            => true,
    'categorias'         => false,
    'fechas'             => false,
    'og'                 => true,
];

$stats = ['total' => 0, 'procesadas' => 0, 'existentes' => 0, 'errores' => 0, 'ahorro_bytes' => 0];

foreach ($carpetas as $carpeta => $incluirThumbs) {
    $dir = $imgBase . '/' . $carpeta;
    if (!is_dir($dir)) {
        echo "SKIP  {$carpeta}/ (no existe)\n";
        continue;
    }

    echo "\n--- {$carpeta}/ ---\n";
    procesarDirectorio($dir, $stats);

    // Thumbs
    if ($incluirThumbs && is_dir($dir . '/thumbs')) {
        echo "--- {$carpeta}/thumbs/ ---\n";
        procesarDirectorio($dir . '/thumbs', $stats);
    }
}

echo "\n=== RESUMEN ===\n";
echo "Total archivos:  {$stats['total']}\n";
echo "WebP generados:  {$stats['procesadas']}\n";
echo "Ya tenían WebP:  {$stats['existentes']}\n";
echo "Errores:         {$stats['errores']}\n";
if ($stats['ahorro_bytes'] > 0) {
    echo "Ahorro total:    " . round($stats['ahorro_bytes'] / 1024) . " KB\n";
}
echo "Listo.\n";

// ─────────────────────────────────────────────

function procesarDirectorio(string $dir, array &$stats): void
{
    $archivos = array_merge(
        glob($dir . '/*.jpg') ?: [],
        glob($dir . '/*.jpeg') ?: [],
        glob($dir . '/*.png') ?: []
    );

    foreach ($archivos as $archivo) {
        $stats['total']++;
        $nombre = basename($archivo);
        $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $archivo);

        // Saltar si ya existe WebP
        if (file_exists($webpPath)) {
            $stats['existentes']++;
            continue;
        }

        // Crear WebP
        $info = @getimagesize($archivo);
        if (!$info) {
            echo "  ERR  {$nombre} (no se pudo leer)\n";
            $stats['errores']++;
            continue;
        }

        $src = match ($info['mime']) {
            'image/jpeg' => @imagecreatefromjpeg($archivo),
            'image/png'  => @imagecreatefrompng($archivo),
            default      => false,
        };

        if (!$src) {
            echo "  ERR  {$nombre} (GD no pudo cargar)\n";
            $stats['errores']++;
            continue;
        }

        // Preservar transparencia para PNG
        if ($info['mime'] === 'image/png') {
            imagepalettetotruecolor($src);
            imagealphablending($src, true);
            imagesavealpha($src, true);
        }

        imagewebp($src, $webpPath, 80);
        imagedestroy($src);

        if (file_exists($webpPath)) {
            $tamOriginal = filesize($archivo);
            $tamWebp = filesize($webpPath);
            $ahorro = $tamOriginal > 0 ? round((1 - $tamWebp / $tamOriginal) * 100) : 0;
            $stats['procesadas']++;
            $stats['ahorro_bytes'] += ($tamOriginal - $tamWebp);
            echo "  OK   {$nombre} → .webp ({$ahorro}% ahorro)\n";
        } else {
            $stats['errores']++;
            echo "  ERR  {$nombre} (WebP no se creó)\n";
        }
    }
}
