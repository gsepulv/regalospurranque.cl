<?php
namespace App\Models;

use App\Core\Database;

class PoliticaAceptacion
{
    public const POLITICAS = ['terminos', 'privacidad', 'contenidos', 'derechos', 'cookies'];

    public static function create(array $data): int
    {
        return Database::getInstance()->insert('politicas_aceptacion', [
            'usuario_id' => $data['usuario_id'],
            'email'      => $data['email'],
            'politica'   => $data['politica'],
            'decision'   => $data['decision'],
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
        ]);
    }

    public static function registrarDecisiones(int $userId, string $email, array $decisiones, string $ip, ?string $userAgent): void
    {
        foreach (self::POLITICAS as $politica) {
            if (isset($decisiones[$politica])) {
                self::create([
                    'usuario_id' => $userId,
                    'email'      => $email,
                    'politica'   => $politica,
                    'decision'   => $decisiones[$politica],
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                ]);
            }
        }
    }

    public static function validarAceptaciones(array $decisiones): array
    {
        $errores = [];
        $nombres = [
            'terminos'    => 'Términos y Condiciones',
            'privacidad'  => 'Política de Privacidad',
            'contenidos'  => 'Política de Contenidos',
            'derechos'    => 'Ejercicio de Derechos',
            'cookies'     => 'Política de Cookies',
        ];

        foreach (self::POLITICAS as $politica) {
            if (empty($decisiones[$politica])) {
                $errores[] = "Debes indicar si aceptas o rechazas: {$nombres[$politica]}.";
            } elseif ($decisiones[$politica] === 'rechazo') {
                $errores[] = "Has rechazado: {$nombres[$politica]}. Debes aceptar todas las políticas para registrarte.";
            }
        }

        return $errores;
    }
}
