<?php
/**
 * Funciones helper globales
 * Regalos Purranque v2
 */

/**
 * Escapar HTML para prevenir XSS
 */
function e(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generar URL completa a partir de un path
 */
function url(string $path = ''): string
{
    if (str_starts_with($path, 'http')) {
        return $path;
    }
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Generar URL de un asset
 */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Obtener valor anterior de formulario (para repoblar tras error)
 */
function old(string $field, string $default = ''): string
{
    return e($_SESSION['flash']['old'][$field] ?? $default);
}

/**
 * Generar campo hidden con token CSRF
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

/**
 * Obtener o generar token CSRF
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Debug: dump y die (solo en desarrollo)
 */
function dd(mixed ...$vars): void
{
    if (!defined('APP_DEBUG') || !APP_DEBUG) {
        return;
    }
    echo '<pre style="background:#1e293b;color:#f8fafc;padding:20px;margin:10px;border-radius:8px;font-size:13px;overflow-x:auto;">';
    foreach ($vars as $var) {
        var_dump($var);
        echo "\n";
    }
    echo '</pre>';
    die();
}

/**
 * Generar slug a partir de texto
 */
function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    // Reemplazar caracteres especiales del español
    $text = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
        ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
        $text
    );
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

/**
 * Formatear fecha en español
 */
function fecha_es(string $date, string $format = 'd/m/Y'): string
{
    return date($format, strtotime($date));
}

/**
 * Truncar texto con puntos suspensivos
 */
function truncate(string $text, int $length = 100): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Generar etiqueta <picture> con WebP y fallback, o <img> si no hay WebP
 *
 * @param string $imgPath Ruta relativa dentro de assets/ (ej: 'img/portadas/foto.jpg')
 * @param string $alt     Texto alternativo
 * @param string $class   Clase CSS
 * @param bool   $lazy    Usar loading="lazy" (false para above-the-fold)
 * @param int    $width   Ancho para prevenir CLS (0 = omitir)
 * @param int    $height  Alto para prevenir CLS (0 = omitir)
 */
function picture(string $imgPath, string $alt, string $class = '', bool $lazy = true, int $width = 0, int $height = 0): string
{
    $alt = e($alt);
    $src = asset($imgPath);
    $attrs = $class ? ' class="' . $class . '"' : '';
    $attrs .= $lazy ? ' loading="lazy"' : '';
    $attrs .= $width ? ' width="' . $width . '"' : '';
    $attrs .= $height ? ' height="' . $height . '"' : '';

    // Buscar versión WebP
    $webpPath = preg_replace('/\.(jpe?g|png)$/i', '.webp', $imgPath);
    $webpFile = BASE_PATH . '/assets/' . $webpPath;

    if ($webpPath !== $imgPath && file_exists($webpFile)) {
        $webpSrc = asset($webpPath);
        return '<picture>'
            . '<source srcset="' . $webpSrc . '" type="image/webp">'
            . '<img src="' . $src . '" alt="' . $alt . '"' . $attrs . '>'
            . '</picture>';
    }

    return '<img src="' . $src . '" alt="' . $alt . '"' . $attrs . '>';
}
