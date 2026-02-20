<?php
namespace App\Services;

use App\Core\Database;

/**
 * Servicio de autenticación
 * Login, logout, verificación de sesión
 */
class Auth
{
    /**
     * Intentar login con email y password
     */
    public static function attempt(string $email, string $password): bool
    {
        $db = Database::getInstance();

        $user = $db->fetch(
            "SELECT * FROM admin_usuarios WHERE email = ? AND activo = 1",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Prevenir session fixation
        session_regenerate_id(true);

        // Guardar datos en sesión
        $_SESSION['admin'] = [
            'id'     => (int) $user['id'],
            'nombre' => $user['nombre'],
            'email'  => $user['email'],
            'rol'    => $user['rol'],
            'avatar' => $user['avatar'],
        ];
        $_SESSION['admin_expires'] = time() + SESSION_LIFETIME;

        // Actualizar último login
        $db->update(
            'admin_usuarios',
            ['last_login' => date('Y-m-d H:i:s')],
            'id = ?',
            [$user['id']]
        );

        // Registrar sesión
        $db->insert('sesiones_admin', [
            'usuario_id' => $user['id'],
            'token'      => session_id(),
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'expira'     => date('Y-m-d H:i:s', $_SESSION['admin_expires']),
        ]);

        return true;
    }

    /**
     * Cerrar sesión
     */
    public static function logout(): void
    {
        if (isset($_SESSION['admin']['id'])) {
            try {
                Database::getInstance()->delete(
                    'sesiones_admin',
                    'token = ?',
                    [session_id()]
                );
            } catch (\Throwable $e) {
                // Ignorar errores de BD al cerrar sesión
            }
        }

        unset($_SESSION['admin'], $_SESSION['admin_expires']);
        session_destroy();
    }

    /**
     * Obtener usuario actual o null
     */
    public static function user(): ?array
    {
        return $_SESSION['admin'] ?? null;
    }

    /**
     * Verificar si hay sesión activa
     */
    public static function check(): bool
    {
        return !empty($_SESSION['admin']['id']);
    }

    /**
     * Obtener rol del usuario actual
     */
    public static function role(): ?string
    {
        return $_SESSION['admin']['rol'] ?? null;
    }

    /**
     * Obtener ID del usuario actual
     */
    public static function id(): ?int
    {
        return isset($_SESSION['admin']['id']) ? (int) $_SESSION['admin']['id'] : null;
    }
}
