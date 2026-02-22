<?php
namespace App\Models;

use App\Core\Database;

class PoliticaAceptacion
{
    public const POLITICAS = ['terminos', 'privacidad', 'contenidos', 'derechos', 'cookies'];

    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $db->execute(
            "INSERT INTO politicas_aceptacion (usuario_id, email, politica, decision, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?)",
            [
                $data['usuario_id'],
                $data['email'],
                $data['politica'],
                $data['decision'],
                $data['ip_address'],
                $data['user_agent'],
            ]
        );
        return (int) $db->lastInsertId();
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
            'derechos'    => 'Derechos del Usuario',
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
