<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminUsuario;
use App\Services\Auth;

/**
 * CRUD de usuarios admin
 * Solo accesible por rol 'admin'
 */
class UsuarioAdminController extends Controller
{
    public function index(): void
    {
        $usuarios = AdminUsuario::getAll();

        $this->render('admin/usuarios/index', [
            'title'    => 'Usuarios — ' . SITE_NAME,
            'usuarios' => $usuarios,
        ]);
    }

    public function create(): void
    {
        $this->render('admin/usuarios/form', [
            'title' => 'Nuevo Usuario — ' . SITE_NAME,
        ]);
    }

    public function store(): void
    {
        $v = $this->validate($_POST, [
            'nombre'   => 'required|string|min:3|max:100',
            'email'    => 'required|email|unique:admin_usuarios,email',
            'password' => 'required|string|min:6',
            'rol'      => 'required|in:admin,editor,comerciante',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'nombre'        => trim($_POST['nombre']),
            'email'         => trim($_POST['email']),
            'telefono'      => trim($_POST['telefono'] ?? ''),
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'rol'           => $_POST['rol'],
            'activo'        => isset($_POST['activo']) ? 1 : 0,
        ];

        $id = AdminUsuario::create($data);
        $this->log('usuarios', 'crear', 'usuario', $id, "Usuario creado: {$data['nombre']} ({$data['rol']})");
        $this->redirect('/admin/usuarios', ['success' => 'Usuario creado correctamente']);
    }

    public function edit(string $id): void
    {
        $id = (int) $id;
        $usuario = AdminUsuario::find($id);
        if (!$usuario) {
            $this->redirect('/admin/usuarios', ['error' => 'Usuario no encontrado']);
            return;
        }

        $this->render('admin/usuarios/form', [
            'title'   => 'Editar Usuario — ' . SITE_NAME,
            'usuario' => $usuario,
        ]);
    }

    public function update(string $id): void
    {
        $id = (int) $id;
        $usuario = AdminUsuario::find($id);
        if (!$usuario) {
            $this->redirect('/admin/usuarios', ['error' => 'Usuario no encontrado']);
            return;
        }

        $v = $this->validate($_POST, [
            'nombre' => 'required|string|min:3|max:100',
            'email'  => "required|email|unique:admin_usuarios,email,{$id}",
            'rol'    => 'required|in:admin,editor,comerciante',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'nombre'   => trim($_POST['nombre']),
            'email'    => trim($_POST['email']),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'rol'      => $_POST['rol'],
            'activo'   => isset($_POST['activo']) ? 1 : 0,
        ];

        // Nueva contraseña (opcional)
        if (!empty($_POST['password'])) {
            if (mb_strlen($_POST['password']) < 6) {
                $this->back(['errors' => ['password' => 'La contraseña debe tener al menos 6 caracteres'], 'old' => $_POST]);
                return;
            }
            $data['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        AdminUsuario::updateById($id, $data);
        $this->log('usuarios', 'editar', 'usuario', $id, "Usuario editado: {$data['nombre']}");
        $this->redirect('/admin/usuarios', ['success' => 'Usuario actualizado correctamente']);
    }

    public function toggleActive(string $id): void
    {
        $id = (int) $id;

        // No puede desactivarse a sí mismo
        if ($id === Auth::id()) {
            $this->json(['ok' => false, 'error' => 'No puedes desactivarte a ti mismo'], 400);
            return;
        }

        $usuario = AdminUsuario::find($id);
        if (!$usuario) {
            $this->json(['ok' => false, 'error' => 'No encontrado'], 404);
            return;
        }

        $newState = $usuario['activo'] ? 0 : 1;
        AdminUsuario::updateById($id, ['activo' => $newState]);

        $this->log('usuarios', $newState ? 'activar' : 'desactivar', 'usuario', $id, $usuario['nombre']);
        $this->json(['ok' => true, 'activo' => $newState, 'csrf' => $_SESSION['csrf_token']]);
    }

    public function delete(string $id): void
    {
        $id = (int) $id;

        // No puede eliminarse a sí mismo
        if ($id === Auth::id()) {
            $this->redirect('/admin/usuarios', ['error' => 'No puedes eliminarte a ti mismo']);
            return;
        }

        $usuario = AdminUsuario::find($id);
        if (!$usuario) {
            $this->redirect('/admin/usuarios', ['error' => 'Usuario no encontrado']);
            return;
        }

        AdminUsuario::deleteById($id);
        $this->log('usuarios', 'eliminar', 'usuario', $id, "Usuario eliminado: {$usuario['nombre']}");
        $this->redirect('/admin/usuarios', ['success' => 'Usuario eliminado correctamente']);
    }
}
