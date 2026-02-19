<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\Auth;
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

        // Validar campos
        if (empty($email) || empty($password)) {
            $this->redirect('/admin/login', [
                'error' => 'Ingresa tu email y contraseña',
                'old'   => ['email' => $email],
            ]);
            return;
        }

        // Intentar autenticar
        if (Auth::attempt($email, $password)) {
            Logger::log('auth', 'login', 'usuario', Auth::id(), 'Inicio de sesion');
            $this->redirect('/admin/dashboard', [
                'success' => 'Bienvenido, ' . Auth::user()['nombre'],
            ]);
        } else {
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
