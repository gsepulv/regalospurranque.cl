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
function old(string $field, ?string $default = ''): string
{
    return e($_SESSION['flash']['old'][$field] ?? $default ?? '');
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
 * Sanitizar HTML de contenido WYSIWYG (TinyMCE)
 * Permite solo tags y atributos seguros, elimina scripts y event handlers
 */
function sanitize_html(?string $html): string
{
    if ($html === null || $html === '') {
        return '';
    }

    // Tags permitidos (lo que TinyMCE genera legítimamente)
    $allowedTags = [
        'h1','h2','h3','h4','h5','h6','p','br','hr',
        'strong','b','em','i','u','s','strike','del',
        'a','img','ul','ol','li','blockquote','pre','code',
        'table','thead','tbody','tfoot','tr','th','td','caption','colgroup','col',
        'div','span','figure','figcaption','video','source','iframe',
        'sub','sup','small','abbr','mark','details','summary',
    ];

    // Atributos permitidos por tag
    $allowedAttrs = [
        'href','src','alt','title','class','style','width','height',
        'target','rel','colspan','rowspan','id','name',
        'type','controls','frameborder','allowfullscreen',
        'data-*','aria-*','loading','decoding',
    ];

    // Paso 1: Eliminar tags <script>, <style>, <object>, <embed>, <applet>, <form>, <input>, <button>, <select>, <textarea>
    $html = preg_replace('#<(script|style|object|embed|applet|form|input|button|select|textarea|meta|link|base)\b[^>]*>.*?</\1>#is', '', $html);
    $html = preg_replace('#<(script|style|object|embed|applet|form|input|button|select|textarea|meta|link|base)\b[^>]*/?\s*>#is', '', $html);

    // Paso 2: Eliminar event handlers (on*)
    $html = preg_replace('#\s+on[a-z]+\s*=\s*["\'][^"\']*["\']#is', '', $html);
    $html = preg_replace('#\s+on[a-z]+\s*=\s*\S+#is', '', $html);

    // Paso 3: Eliminar javascript: y data: en href/src (excepto data:image)
    $html = preg_replace('#(href|src)\s*=\s*["\']?\s*javascript\s*:#is', '$1="', $html);
    $html = preg_replace('#(href)\s*=\s*["\']?\s*data\s*:#is', '$1="', $html);

    // Paso 4: Eliminar expression() y url() peligrosos en atributos style
    $html = preg_replace('#style\s*=\s*["\'][^"\']*expression\s*\([^"\']*["\']#is', '', $html);
    $html = preg_replace('#style\s*=\s*["\'][^"\']*javascript\s*:[^"\']*["\']#is', '', $html);

    return $html;
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
