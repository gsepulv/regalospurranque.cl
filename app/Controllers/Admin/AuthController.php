<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\Auth;
use App\Services\Captcha;
use App\Services\Logger;

/**
 * Controlador de autenticacion admin
 * Login y logout
 */
class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function loginForm(): void
    {
        // Si ya tiene sesion, ir al dashboard
        if (Auth::check()) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $this->render('admin/login', [
            'title' => 'Iniciar sesion — ' . SITE_NAME,
        ]);
    }

    /**
     * Procesar login
     */
    public function login(): void
    {
        $email    = trim($this->request->post('email', ''));
        $password = $this->request->post('password', '');
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Validar campos
        if (empty($email) || empty($password)) {
            $this->redirect('/admin/login', [
                'error' => 'Ingresa tu email y contraseña',
                'old'   => ['email' => $email],
            ]);
            return;
        }

        // Protección fuerza bruta: máx 5 intentos fallidos en 15 min
        try {
            $intentos = $this->db->fetch(
                "SELECT COUNT(*) as total FROM login_intentos
                 WHERE ip = ? AND exitoso = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
                [$ip]
            );
            if ($intentos && (int)$intentos['total'] >= 5) {
                $this->redirect('/admin/login', [
                    'error' => 'Demasiados intentos fallidos. Intenta en 15 minutos.',
                    'old'   => ['email' => $email],
                ]);
                return;
            }
        } catch (\Throwable $e) {
            // Tabla puede no existir aún, continuar sin bloqueo
        }

        // Validar Turnstile
        if (!Captcha::verify($this->request->post('cf-turnstile-response'))) {
            $this->redirect('/admin/login', [
                'error' => 'Verificación anti-bot fallida. Intenta nuevamente.',
                'old'   => ['email' => $email],
            ]);
            return;
        }

        // Intentar autenticar
        if (Auth::attempt($email, $password)) {
            // Registrar intento exitoso
            try {
                $this->db->insert('login_intentos', [
                    'ip' => $ip, 'email' => $email, 'exitoso' => 1,
                ]);
            } catch (\Throwable $e) {}

            Logger::log('auth', 'login', 'usuario', Auth::id(), 'Inicio de sesion');
            $this->redirect('/admin/dashboard', [
                'success' => 'Bienvenido, ' . Auth::user()['nombre'],
            ]);
        } else {
            // Registrar intento fallido
            try {
                $this->db->insert('login_intentos', [
                    'ip' => $ip, 'email' => $email, 'exitoso' => 0,
                ]);
            } catch (\Throwable $e) {}

            Logger::log('auth', 'login_fallido', 'usuario', 0, "Intento fallido: {$email} desde {$ip}");
            $this->redirect('/admin/login', [
                'error' => 'Credenciales incorrectas',
                'old'   => ['email' => $email],
            ]);
        }
    }

    /**
     * Cerrar sesion
     */
    public function logout(): void
    {
        if (Auth::check()) {
            Logger::log('auth', 'logout', 'usuario', Auth::id(), 'Cierre de sesion');
        }
        Auth::logout();
        // Iniciar nueva sesion para flash message
        session_start();
        $this->redirect('/admin/login', [
            'success' => 'Has cerrado sesion correctamente',
        ]);
    }
}
