<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\Auth;
use App\Services\Logger;

/**
 * Controlador de perfil del usuario admin
 * Cambio de contraseña
 */
class PerfilController extends Controller
{
    /**
     * Mostrar formulario de cambio de contraseña
     */
    public function index(): void
    {
        $this->render('admin/perfil/index', [
            'title' => 'Mi Perfil — ' . SITE_NAME,
        ]);
    }

    /**
     * Procesar cambio de contraseña
     */
    public function updatePassword(): void
    {
        $currentPassword = $this->request->post('current_password', '');
        $newPassword     = $this->request->post('new_password', '');
        $confirmPassword = $this->request->post('confirm_password', '');

        // Validar campos requeridos
        $errors = [];

        if (empty($currentPassword)) {
            $errors['current_password'] = 'La contraseña actual es requerida';
        }
        if (empty($newPassword)) {
            $errors['new_password'] = 'La nueva contraseña es requerida';
        } elseif (mb_strlen($newPassword) < 8) {
            $errors['new_password'] = 'La nueva contraseña debe tener minimo 8 caracteres';
        }
        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'Las contraseñas no coinciden';
        }

        if (!empty($errors)) {
            $this->back(['errors' => $errors]);
            return;
        }

        // Verificar contraseña actual
        $user = $this->db->fetch(
            "SELECT password_hash FROM admin_usuarios WHERE id = ? AND activo = 1",
            [Auth::id()]
        );

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            $this->back(['errors' => ['current_password' => 'La contraseña actual es incorrecta']]);
            return;
        }

        // Actualizar contraseña
        $this->db->update(
            'admin_usuarios',
            ['password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)],
            'id = ?',
            [Auth::id()]
        );

        Logger::log('perfil', 'cambio_password', 'usuario', Auth::id(), 'Cambio de contraseña');

        $this->redirect('/admin/perfil', [
            'success' => 'Contraseña actualizada correctamente',
        ]);
    }
}
