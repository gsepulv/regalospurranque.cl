<?php
namespace App\Services;

/**
 * Servicio de validación Cloudflare Turnstile
 */
class Captcha
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * Verificar si Turnstile está habilitado
     */
    public static function isEnabled(): bool
    {
        return defined('TURNSTILE_ENABLED') && TURNSTILE_ENABLED === true;
    }

    /**
     * Obtener site key para el frontend
     */
    public static function siteKey(): string
    {
        return defined('TURNSTILE_SITE_KEY') ? TURNSTILE_SITE_KEY : '';
    }

    /**
     * Validar token de Turnstile server-side
     * Retorna true si es válido o si Turnstile está deshabilitado
     */
    public static function verify(?string $token): bool
    {
        if (!self::isEnabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $data = [
            'secret'   => TURNSTILE_SECRET_KEY,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ];

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 10,
            ],
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents(self::VERIFY_URL, false, $context);

        if ($response === false) {
            error_log('[Captcha] Error al verificar Turnstile: no se pudo contactar al servidor');
            return false;
        }

        $result = json_decode($response, true);

        return isset($result['success']) && $result['success'] === true;
    }

    /**
     * Renderizar el script de Turnstile para el layout
     */
    public static function script(): string
    {
        if (!self::isEnabled()) {
            return '';
        }

        return '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
    }

    /**
     * Renderizar widget de Turnstile
     */
    public static function widget(): string
    {
        if (!self::isEnabled()) {
            return '';
        }

        return '<div class="cf-turnstile" data-sitekey="' . e(self::siteKey()) . '"></div>';
    }
}
