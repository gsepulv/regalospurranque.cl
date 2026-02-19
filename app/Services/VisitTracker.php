<?php
namespace App\Services;

use App\Core\Database;

/**
 * Servicio de tracking de visitas
 * Registra visitas a paginas y comercios en visitas_log
 */
class VisitTracker
{
    /**
     * Registrar una visita
     */
    public static function track(?int $comercioId, string $pagina, string $tipo): void
    {
        try {
            $db = Database::getInstance();
            $db->insert('visitas_log', [
                'comercio_id' => $comercioId,
                'pagina'      => mb_substr($pagina, 0, 500),
                'tipo'        => mb_substr($tipo, 0, 50),
                'ip'          => self::getClientIp(),
                'user_agent'  => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
                'referrer'    => mb_substr($_SERVER['HTTP_REFERER'] ?? '', 0, 500),
            ]);
        } catch (\Throwable $e) {
            // Fallo silencioso: no romper la pagina si el tracking falla
            error_log('VisitTracker error: ' . $e->getMessage());
        }
    }

    /**
     * Obtener IP real del cliente (considerando proxies)
     */
    private static function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }

        return mb_substr($ip, 0, 45);
    }
}
