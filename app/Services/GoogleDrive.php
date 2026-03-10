<?php
namespace App\Services;

/**
 * Servicio de Google Drive via API REST v3
 * Autenticación OAuth 2.0 con refresh token (cuenta personal Gmail)
 * Sin cURL ni Composer — usa file_get_contents + stream context
 */
class GoogleDrive
{
    private const TOKEN_URI   = 'https://oauth2.googleapis.com/token';
    private const API_BASE    = 'https://www.googleapis.com/drive/v3';
    private const UPLOAD_BASE = 'https://www.googleapis.com/upload/drive/v3/files';
    private const CHUNK_SIZE  = 5 * 1024 * 1024; // 5MB

    /** @var string Último error para diagnóstico */
    private static string $lastError = '';

    // ─── Público ─────────────────────────────────────────────────

    /**
     * ¿Está habilitado y configurado?
     */
    public static function isEnabled(): bool
    {
        return defined('GDRIVE_ENABLED') && GDRIVE_ENABLED === true;
    }

    /**
     * Verificar conexión con Google Drive
     * @return array{ok: bool, message: string, email?: string}
     */
    public static function verificarConexion(): array
    {
        try {
            if (!self::isEnabled()) {
                return ['ok' => false, 'message' => 'Google Drive no está habilitado'];
            }

            if (!defined('GDRIVE_CLIENT_ID') || !defined('GDRIVE_CLIENT_SECRET') || !defined('GDRIVE_REFRESH_TOKEN')) {
                return ['ok' => false, 'message' => 'Faltan credenciales OAuth (CLIENT_ID, CLIENT_SECRET o REFRESH_TOKEN)'];
            }

            self::$lastError = '';
            $token = self::getAccessToken();
            if (!$token) {
                $msg = self::$lastError ?: 'No se pudo obtener token de acceso';
                return ['ok' => false, 'message' => $msg];
            }

            $resp = self::httpRequest(
                self::API_BASE . '/about?fields=user',
                'GET',
                null,
                ['Authorization: Bearer ' . $token]
            );

            if ($resp['httpCode'] !== 200) {
                return ['ok' => false, 'message' => 'Error API: HTTP ' . $resp['httpCode']];
            }

            $data = json_decode($resp['body'], true);
            $email = $data['user']['emailAddress'] ?? 'desconocido';

            return ['ok' => true, 'message' => 'Conectado', 'email' => $email];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Subir archivo a Google Drive
     * @return array{ok: bool, message: string, fileId?: string, webViewLink?: string}
     */
    public static function subirArchivo(string $filepath, ?string $filename = null): array
    {
        try {
            if (!file_exists($filepath) || !is_readable($filepath)) {
                return ['ok' => false, 'message' => 'Archivo no encontrado o no legible'];
            }

            $token = self::getAccessToken();
            if (!$token) {
                return ['ok' => false, 'message' => 'No se pudo obtener token de acceso'];
            }

            $filename = $filename ?? basename($filepath);
            $fileSize = filesize($filepath);
            $mimeType = self::getMimeType($filepath);
            $folderId = defined('GDRIVE_FOLDER_ID') ? GDRIVE_FOLDER_ID : '';

            if (empty($folderId)) {
                return ['ok' => false, 'message' => 'GDRIVE_FOLDER_ID no está configurado en config/backup.php'];
            }

            return self::resumableUpload($filepath, $filename, $fileSize, $mimeType, $folderId, $token);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Listar archivos de backup en la carpeta de Drive
     * @return array{ok: bool, files?: array, message?: string}
     */
    public static function listarArchivos(): array
    {
        try {
            $token = self::getAccessToken();
            if (!$token) {
                return ['ok' => false, 'message' => 'No se pudo obtener token de acceso'];
            }

            $folderId = defined('GDRIVE_FOLDER_ID') ? GDRIVE_FOLDER_ID : '';
            $query = urlencode("'{$folderId}' in parents and trashed = false");
            $fields = urlencode('files(id,name,size,createdTime,webViewLink)');

            $url = self::API_BASE . "/files?q={$query}&fields={$fields}&orderBy=createdTime+desc&pageSize=100";

            $resp = self::httpRequest($url, 'GET', null, [
                'Authorization: Bearer ' . $token,
            ]);

            if ($resp['httpCode'] !== 200) {
                $error = json_decode($resp['body'], true);
                $msg = $error['error']['message'] ?? 'HTTP ' . $resp['httpCode'];
                return ['ok' => false, 'message' => 'Error al listar: ' . $msg];
            }

            $data = json_decode($resp['body'], true);
            $files = [];

            foreach ($data['files'] ?? [] as $f) {
                $files[] = [
                    'id'          => $f['id'],
                    'name'        => $f['name'],
                    'size'        => (int) ($f['size'] ?? 0),
                    'createdTime' => $f['createdTime'] ?? '',
                    'webViewLink' => $f['webViewLink'] ?? '',
                ];
            }

            return ['ok' => true, 'files' => $files];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Eliminar archivo de Google Drive por su ID
     * @return array{ok: bool, message: string}
     */
    public static function eliminarArchivo(string $fileId): array
    {
        try {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $fileId)) {
                return ['ok' => false, 'message' => 'ID de archivo no válido'];
            }

            $token = self::getAccessToken();
            if (!$token) {
                return ['ok' => false, 'message' => 'No se pudo obtener token de acceso'];
            }

            $resp = self::httpRequest(
                self::API_BASE . '/files/' . $fileId,
                'DELETE',
                null,
                ['Authorization: Bearer ' . $token]
            );

            if ($resp['httpCode'] === 204) {
                return ['ok' => true, 'message' => 'Archivo eliminado'];
            }

            $error = json_decode($resp['body'], true);
            $msg = $error['error']['message'] ?? 'HTTP ' . $resp['httpCode'];
            return ['ok' => false, 'message' => 'Error al eliminar: ' . $msg];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Eliminar backups antiguos en Drive (> días)
     */
    public static function cleanOldDriveBackups(int $days = 30): int
    {
        $result = self::listarArchivos();
        if (!$result['ok'] || empty($result['files'])) {
            return 0;
        }

        $cutoff = time() - ($days * 86400);
        $deleted = 0;

        foreach ($result['files'] as $file) {
            if (empty($file['createdTime'])) continue;

            $fileTime = strtotime($file['createdTime']);
            if ($fileTime && $fileTime < $cutoff) {
                $del = self::eliminarArchivo($file['id']);
                if ($del['ok']) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    // ─── Upload resumable ──────────────────────────────────────

    /**
     * @return array{ok: bool, message: string, fileId?: string, webViewLink?: string}
     */
    private static function resumableUpload(
        string $filepath,
        string $filename,
        int $fileSize,
        string $mimeType,
        string $folderId,
        string $token
    ): array {
        // Paso 1: Iniciar sesión resumable (metadata con parents)
        $metadata = json_encode([
            'name'    => $filename,
            'parents' => [$folderId],
        ]);

        $resp = self::httpRequest(
            self::UPLOAD_BASE . '?uploadType=resumable&fields=id,name,webViewLink',
            'POST',
            $metadata,
            [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json; charset=UTF-8',
                'X-Upload-Content-Type: ' . $mimeType,
                'X-Upload-Content-Length: ' . $fileSize,
            ],
            30
        );

        if ($resp['httpCode'] !== 200) {
            $error = json_decode($resp['body'], true);
            $msg = $error['error']['message'] ?? 'HTTP ' . $resp['httpCode'];
            return ['ok' => false, 'message' => 'Error al iniciar upload: ' . $msg];
        }

        // Extraer URI de upload de los headers
        $uploadUri = '';
        foreach ($resp['headers'] as $header) {
            if (stripos($header, 'Location:') === 0) {
                $uploadUri = trim(substr($header, 9));
                break;
            }
        }

        if (!$uploadUri) {
            return ['ok' => false, 'message' => 'No se obtuvo URI de upload resumable'];
        }

        // Paso 2: Subir en chunks
        $handle = fopen($filepath, 'rb');
        if (!$handle) {
            return ['ok' => false, 'message' => 'No se pudo abrir el archivo'];
        }

        $offset = 0;
        $lastResponse = null;

        while ($offset < $fileSize) {
            $chunkData = fread($handle, self::CHUNK_SIZE);
            $chunkLen = strlen($chunkData);
            $rangeEnd = $offset + $chunkLen - 1;

            $chunkResp = self::httpRequest(
                $uploadUri,
                'PUT',
                $chunkData,
                [
                    'Content-Length: ' . $chunkLen,
                    'Content-Range: bytes ' . $offset . '-' . $rangeEnd . '/' . $fileSize,
                ],
                120
            );

            if ($chunkResp['httpCode'] === 200 || $chunkResp['httpCode'] === 201) {
                $lastResponse = $chunkResp;
                break;
            }

            if ($chunkResp['httpCode'] !== 308) {
                fclose($handle);
                $error = json_decode($chunkResp['body'], true);
                $msg = $error['error']['message'] ?? 'HTTP ' . $chunkResp['httpCode'];
                return ['ok' => false, 'message' => 'Error en chunk (offset ' . $offset . '): ' . $msg];
            }

            $offset += $chunkLen;
        }

        fclose($handle);

        if (!$lastResponse || ($lastResponse['httpCode'] !== 200 && $lastResponse['httpCode'] !== 201)) {
            return ['ok' => false, 'message' => 'Upload resumable incompleto'];
        }

        $data = json_decode($lastResponse['body'], true);
        return [
            'ok'          => true,
            'message'     => 'Archivo subido',
            'fileId'      => $data['id'] ?? '',
            'webViewLink' => $data['webViewLink'] ?? '',
        ];
    }

    // ─── OAuth 2.0 con Refresh Token ────────────────────────────

    /**
     * Obtener access token usando refresh token (cacheado o nuevo)
     */
    private static function getAccessToken(): string|false
    {
        $cached = self::getCachedToken();
        if ($cached) return $cached;

        $resp = self::httpRequest(
            self::TOKEN_URI,
            'POST',
            http_build_query([
                'client_id'     => GDRIVE_CLIENT_ID,
                'client_secret' => GDRIVE_CLIENT_SECRET,
                'refresh_token' => GDRIVE_REFRESH_TOKEN,
                'grant_type'    => 'refresh_token',
            ]),
            ['Content-Type: application/x-www-form-urlencoded'],
            15
        );

        if ($resp['httpCode'] !== 200) {
            $body = json_decode($resp['body'], true);
            $errorMsg = $body['error_description'] ?? $body['error'] ?? 'HTTP ' . $resp['httpCode'];
            self::$lastError = 'Token refresh falló: ' . $errorMsg;
            return false;
        }

        $tokenData = json_decode($resp['body'], true);

        if (empty($tokenData['access_token'])) {
            self::$lastError = 'Respuesta sin access_token';
            return false;
        }

        self::cacheToken($tokenData['access_token'], $tokenData['expires_in'] ?? 3600);

        return $tokenData['access_token'];
    }

    /**
     * Guardar token en cache
     */
    private static function cacheToken(string $accessToken, int $expiresIn): void
    {
        $cacheDir = BASE_PATH . '/storage/cache';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir . '/gdrive_token.json';
        $data = json_encode([
            'access_token' => $accessToken,
            'expires_at'   => time() + $expiresIn,
        ]);

        @file_put_contents($cacheFile, $data);
    }

    /**
     * Leer token del cache
     */
    private static function getCachedToken(): string|false
    {
        $cacheFile = BASE_PATH . '/storage/cache/gdrive_token.json';

        if (!file_exists($cacheFile)) return false;

        $data = json_decode(file_get_contents($cacheFile), true);

        if (!$data || empty($data['access_token']) || empty($data['expires_at'])) {
            return false;
        }

        if ($data['expires_at'] <= time() + 60) {
            return false;
        }

        return $data['access_token'];
    }

    // ─── HTTP ───────────────────────────────────────────────────

    /**
     * Request HTTP genérico (file_get_contents + stream context)
     * @return array{httpCode: int, body: string, headers: array}
     */
    private static function httpRequest(
        string $url,
        string $method = 'GET',
        ?string $body = null,
        array $headers = [],
        int $timeout = 30
    ): array {
        $opts = [
            'http' => [
                'method'          => $method,
                'timeout'         => $timeout,
                'ignore_errors'   => true,
                'follow_location' => 0,
            ],
            'ssl' => [
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ],
        ];

        if (!empty($headers)) {
            $opts['http']['header'] = implode("\r\n", $headers);
        }

        if ($body !== null) {
            $opts['http']['content'] = $body;
        }

        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        $responseHeaders = $http_response_header ?? [];
        $httpCode = 0;

        foreach ($responseHeaders as $h) {
            if (preg_match('/^HTTP\/[\d.]+\s+(\d+)/', $h, $m)) {
                $httpCode = (int) $m[1];
            }
        }

        if ($result === false && $httpCode === 0) {
            throw new \RuntimeException('HTTP request failed: ' . $url);
        }

        return [
            'httpCode' => $httpCode,
            'body'     => $result !== false ? $result : '',
            'headers'  => $responseHeaders,
        ];
    }

    /**
     * MIME type según extensión
     */
    private static function getMimeType(string $filepath): string
    {
        $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        return match ($ext) {
            'sql'  => 'application/sql',
            'zip'  => 'application/zip',
            'gz'   => 'application/gzip',
            default => 'application/octet-stream',
        };
    }
}
