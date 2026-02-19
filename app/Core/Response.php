<?php
namespace App\Core;

/**
 * Helpers de respuesta HTTP
 * Redirect, JSON, errores, descargas
 */
class Response
{
    /**
     * Redireccionar a una URL
     */
    public static function redirect(string $url): void
    {
        if (!str_starts_with($url, 'http')) {
            $url = url($url);
        }
        header('Location: ' . $url);
        exit;
    }

    /**
     * Respuesta JSON
     */
    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Página de error
     */
    public static function error(int $code): void
    {
        http_response_code($code);
        $file = BASE_PATH . "/views/errors/{$code}.php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><title>Error {$code}</title>";
            echo "<style>body{font-family:system-ui;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#f8fafc;color:#1e293b;}";
            echo ".e{text-align:center}.e h1{font-size:5rem;margin:0;color:#e11d48}.e p{font-size:1.25rem;color:#64748b}</style></head>";
            echo "<body><div class='e'><h1>{$code}</h1><p>Ha ocurrido un error</p><a href='/'>Volver al inicio</a></div></body></html>";
        }
        exit;
    }

    /**
     * Forzar descarga de archivo
     */
    public static function download(string $path, ?string $name = null): void
    {
        if (!file_exists($path)) {
            self::error(404);
        }
        $name = $name ?? basename($path);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');
        readfile($path);
        exit;
    }
}
