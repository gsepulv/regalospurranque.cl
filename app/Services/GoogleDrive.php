<?php
namespace App\Services;

/**
 * Servicio de Google Drive via API REST v3
 * Autenticación con Service Account (JWT + cURL), sin Composer
 */
class GoogleDrive
{
    private const TOKEN_URI   = 'https://oauth2.googleapis.com/token';
    private const API_BASE    = 'https://www.googleapis.com/drive/v3';
    private const UPLOAD_BASE = 'https://www.googleapis.com/upload/drive/v3/files';
    private const SCOPE       = 'https://www.googleapis.com/auth/drive.file';
    private const CHUNK_SIZE  = 5 * 1024 * 1024; // 5MB

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

            if (!defined('GDRIVE_CREDENTIALS_PATH') || !file_exists(GDRIVE_CREDENTIALS_PATH)) {
                return ['ok' => false, 'message' => 'Archivo de credenciales no encontrado'];
            }

            self::$lastError = '';
            $token = self::getAccessToken();
            if (!$token) {
                $msg = self::$lastError ?: 'No se pudo obtener token de acceso';
                error_log('[GoogleDrive] verificarConexion falló: ' . $msg);
                return ['ok' => false, 'message' => $msg];
            }

            $resp = self::curlRequest(
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

            // Siempre resumable upload (multipart no funciona con file_get_contents)
            return self::resumableUpload($filepath, $filename, $fileSize, $mimeType, $folderId, $token);
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Exception: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine()];
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

            $url = self::API_BASE . "/files?q={$query}&fields={$fields}&orderBy=createdTime+desc&pageSize=100&supportsAllDrives=true&includeItemsFromAllDrives=true";

            $resp = self::curlRequest($url, 'GET', null, [
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

            $resp = self::curlRequest(
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
        // Paso 1: Iniciar sesión resumable
        $metadata = json_encode([
            'name'    => $filename,
            'parents' => [$folderId],
        ]);

        $uploadUrl = self::UPLOAD_BASE . '?uploadType=resumable&supportsAllDrives=true&fields=id,name,webViewLink';
        $reqHeaders = [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json; charset=UTF-8',
            'X-Upload-Content-Type: ' . $mimeType,
            'X-Upload-Content-Length: ' . $fileSize,
        ];

        $resp = self::curlRequest($uploadUrl, 'POST', $metadata, $reqHeaders, 30);

        if ($resp['httpCode'] !== 200) {
            $error = json_decode($resp['body'], true);
            $msg = $error['error']['message'] ?? 'HTTP ' . $resp['httpCode'];
            // Debug: mostrar todo en el error para diagnosticar
            $debug = ' [DEBUG metadata=' . $metadata . ' | HTTP=' . $resp['httpCode'] . ' | body=' . substr($resp['body'], 0, 300) . ']';
            return ['ok' => false, 'message' => $msg . $debug];
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

            $chunkResp = self::curlRequest(
                $uploadUri,
                'PUT',
                $chunkData,
                [
                    'Content-Length: ' . $chunkLen,
                    'Content-Range: bytes ' . $offset . '-' . $rangeEnd . '/' . $fileSize,
                ],
                120
            );

            // 308 = chunk recibido, continuar
            // 200/201 = upload completo
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

    // ─── JWT / Auth ──────────────────────────────────────────────

    /** @var string Último error para diagnóstico */
    private static string $lastError = '';

    /**
     * Obtener access token (cacheado o nuevo)
     */
    private static function getAccessToken(): string|false
    {
        // Intentar cache
        $cached = self::getCachedToken();
        if ($cached) return $cached;

        // Generar nuevo
        $creds = self::readCredentials();
        if (!$creds) {
            self::$lastError = 'No se pudieron leer las credenciales (archivo inexistente, ilegible o JSON inválido)';
            return false;
        }

        $jwt = self::generateJWT($creds);
        if (!$jwt) {
            self::$lastError = 'No se pudo generar el JWT (error en openssl_sign)';
            return false;
        }

        $tokenData = self::exchangeJWTForToken($jwt);

        if (!$tokenData || empty($tokenData['access_token'])) {
            return false; // lastError ya fue seteado en exchangeJWTForToken
        }

        self::cacheToken($tokenData['access_token'], $tokenData['expires_in'] ?? 3600);

        return $tokenData['access_token'];
    }

    /**
     * Leer credenciales del JSON
     */
    private static function readCredentials(): array|false
    {
        $path = defined('GDRIVE_CREDENTIALS_PATH') ? GDRIVE_CREDENTIALS_PATH : '';

        if (!$path || !file_exists($path) || !is_readable($path)) {
            return false;
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);

        if (!$data || empty($data['private_key']) || empty($data['client_email'])) {
            return false;
        }

        return $data;
    }

    /**
     * Generar JWT firmado para Google OAuth
     */
    private static function generateJWT(array $credentials): string|false
    {
        $header = self::base64urlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $now = self::getAccurateTime();
        $claims = self::base64urlEncode(json_encode([
            'iss'   => $credentials['client_email'],
            'scope' => self::SCOPE,
            'aud'   => self::TOKEN_URI,
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $input = $header . '.' . $claims;

        $success = openssl_sign($input, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);

        if (!$success || empty($signature)) {
            $opensslError = openssl_error_string() ?: 'desconocido';
            self::$lastError = 'openssl_sign falló: ' . $opensslError;
            error_log('[GoogleDrive] openssl_sign error: ' . $opensslError);
            return false;
        }

        return $input . '.' . self::base64urlEncode($signature);
    }

    /**
     * Base64url encode (RFC 4648, sin padding)
     */
    private static function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Intercambiar JWT por access token
     */
    private static function exchangeJWTForToken(string $jwt): array|false
    {
        $resp = self::curlRequest(
            self::TOKEN_URI,
            'POST',
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            ['Content-Type: application/x-www-form-urlencoded'],
            15
        );

        if ($resp['httpCode'] !== 200) {
            $body = json_decode($resp['body'], true);
            $errorMsg = $body['error_description'] ?? $body['error'] ?? 'HTTP ' . $resp['httpCode'];
            self::$lastError = 'Token exchange falló: ' . $errorMsg;
            error_log('[GoogleDrive] Token exchange HTTP ' . $resp['httpCode'] . ': ' . $errorMsg);
            return false;
        }

        return json_decode($resp['body'], true);
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

        // Margen de 60 segundos
        if ($data['expires_at'] <= time() + 60) {
            return false;
        }

        return $data['access_token'];
    }

    // ─── cURL ────────────────────────────────────────────────────

    /**
     * Request HTTP genérico (file_get_contents + stream context)
     * Compatible con hosting sin extensión cURL
     * @return array{httpCode: int, body: string, headers: array}
     */
    private static function curlRequest(
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
     * Obtener timestamp preciso consultando el header Date de Google.
     * Corrige desfase de reloj en hosting compartido.
     */
    private static function getAccurateTime(): int
    {
        try {
            $resp = self::curlRequest('https://www.googleapis.com', 'HEAD', null, [], 5);
            foreach ($resp['headers'] as $header) {
                if (stripos($header, 'Date:') === 0) {
                    $ts = strtotime(trim(substr($header, 5)));
                    if ($ts > 0) return $ts;
                }
            }
        } catch (\Throwable $e) {
            // fallback a time() local
        }

        return time();
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
