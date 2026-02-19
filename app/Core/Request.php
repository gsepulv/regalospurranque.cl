<?php
namespace App\Core;

/**
 * Encapsula datos del request HTTP
 * Acceso limpio a $_GET, $_POST, $_FILES, $_SERVER
 */
class Request
{
    /**
     * Método HTTP (GET, POST, PUT, DELETE)
     */
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * URI limpia (sin query string, sin trailing slash)
     * Compatible con: mod_rewrite, FallbackResource, ErrorDocument,
     * auto_prepend_file, PATH_INFO y PHP built-in server
     */
    public function uri(): string
    {
        // 1. Intentar REDIRECT_URL (ErrorDocument preserva la URI original aquí)
        $uri = $_SERVER['REDIRECT_URL'] ?? '';

        // 2. PATH_INFO (cuando la URL es /index.php/categorias)
        if (empty($uri) && !empty($_SERVER['PATH_INFO'])) {
            $uri = $_SERVER['PATH_INFO'];
        }

        // 3. REQUEST_URI (caso normal)
        if (empty($uri)) {
            $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        }

        // Limpiar /index.php de la URI
        $uri = str_replace('/index.php', '', $uri);

        // Remover trailing slash excepto para /
        $uri = rtrim($uri, '/');

        return $uri ?: '/';
    }

    /**
     * Obtener parámetro GET
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Obtener parámetro POST
     */
    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Obtener todos los datos (GET + POST)
     */
    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Obtener solo los campos indicados
     */
    public function only(array $keys): array
    {
        $data = $this->all();
        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * Obtener archivo subido
     */
    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Verificar si existe un campo
     */
    public function has(string $key): bool
    {
        return isset($_GET[$key]) || isset($_POST[$key]);
    }

    /**
     * IP del cliente
     */
    public function ip(): string
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * User agent
     */
    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Es request AJAX?
     */
    public function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    /**
     * Obtener header HTTP
     */
    public function header(string $name): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? null;
    }

    /**
     * Query string completo
     */
    public function queryString(): string
    {
        return $_SERVER['QUERY_STRING'] ?? '';
    }

    /**
     * Referrer
     */
    public function referrer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }
}
