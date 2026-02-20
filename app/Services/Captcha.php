<?php
namespace App\Services;

/**
 * Servicio de validación hCaptcha
 */
class Captcha
{
    private const VERIFY_URL = 'https://api.hcaptcha.com/siteverify';

    /**
     * Verificar si hCaptcha está habilitado
     */
    public static function isEnabled(): bool
    {
        return defined('HCAPTCHA_ENABLED') && HCAPTCHA_ENABLED === true;
    }

    /**
     * Obtener site key para el frontend
     */
    public static function siteKey(): string
    {
        return defined('HCAPTCHA_SITE_KEY') ? HCAPTCHA_SITE_KEY : '';
    }

    /**
     * Validar token de hCaptcha server-side
     * Retorna true si es válido o si hCaptcha está deshabilitado
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
            'secret'   => HCAPTCHA_SECRET_KEY,
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
            // Si falla la verificación externa, logear y permitir (fail-open)
            error_log('[Captcha] Error al verificar hCaptcha: no se pudo contactar al servidor');
            return true;
        }

        $result = json_decode($response, true);

        return isset($result['success']) && $result['success'] === true;
    }

    /**
     * Renderizar el script de hCaptcha para el head/footer
     */
    public static function script(): string
    {
        if (!self::isEnabled()) {
            return '';
        }

        return '<script src="https://js.hcaptcha.com/1/api.js" async defer></script>';
    }

    /**
     * Renderizar widget invisible de hCaptcha
     */
    public static function widget(string $callbackName = 'onCaptchaPass'): string
    {
        if (!self::isEnabled()) {
            return '';
        }

        return '<div class="h-captcha" data-sitekey="' . e(self::siteKey()) . '" data-size="invisible" data-callback="' . e($callbackName) . '"></div>';
    }
}
