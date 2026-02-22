<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Categoria;
use App\Services\FileManager;

/**
 * CRUD de categorías
 */
class CategoriaAdminController extends Controller
{
    public function index(): void
    {
        $categorias = Categoria::getWithComerciosCount();

        $this->render('admin/categorias/index', [
            'title'      => 'Categorías — ' . SITE_NAME,
            'categorias' => $categorias,
        ]);
    }

    public function create(): void
    {
        $maxOrden = Categoria::getMaxOrden();

        $this->render('admin/categorias/form', [
            'title'    => 'Nueva Categoría — ' . SITE_NAME,
            'maxOrden' => $maxOrden + 1,
        ]);
    }

    public function store(): void
    {
        $v = $this->validate($_POST, [
            'nombre'      => 'required|string|min:3|max:100',
            'slug'        => 'required|slug|unique:categorias,slug',
            'color'       => 'required|string|max:7',
            'descripcion' => 'required|string|min:10|max:500',
            'icono'       => 'required|string|min:1|max:50',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'nombre'      => trim($_POST['nombre']),
            'slug'        => trim($_POST['slug']),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'icono'       => trim($_POST['icono'] ?? ''),
            'color'       => trim($_POST['color']),
            'orden'       => (int) ($_POST['orden'] ?? 0),
            'activo'      => isset($_POST['activo']) ? 1 : 0,
        ];

        $imagen = $this->request->file('imagen');
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            $data['imagen'] = FileManager::subirImagen($imagen, 'categorias', 800);
        }

        $id = Categoria::create($data);
        $this->log('categorias', 'crear', 'categoria', $id, "Categoría creada: {$data['nombre']}");
        $this->redirect('/admin/categorias', ['success' => 'Categoría creada correctamente']);
    }

    public function edit(string $id): void
    {
        $id = (int) $id;
        $categoria = Categoria::find($id);
        if (!$categoria) {
            $this->redirect('/admin/categorias', ['error' => 'Categoría no encontrada']);
            return;
        }

        $this->render('admin/categorias/form', [
            'title'     => 'Editar Categoría — ' . SITE_NAME,
            'categoria' => $categoria,
        ]);
    }

    public function update(string $id): void
    {
        $id = (int) $id;
        $categoria = Categoria::find($id);
        if (!$categoria) {
            $this->redirect('/admin/categorias', ['error' => 'Categoría no encontrada']);
            return;
        }

        $v = $this->validate($_POST, [
            'nombre'      => 'required|string|min:3|max:100',
            'slug'        => "required|slug|unique:categorias,slug,{$id}",
            'color'       => 'required|string|max:7',
            'descripcion' => 'required|string|min:10|max:500',
            'icono'       => 'required|string|min:1|max:50',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'nombre'      => trim($_POST['nombre']),
            'slug'        => trim($_POST['slug']),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
            'icono'       => trim($_POST['icono'] ?? ''),
            'color'       => trim($_POST['color']),
            'orden'       => (int) ($_POST['orden'] ?? 0),
            'activo'      => isset($_POST['activo']) ? 1 : 0,
        ];

        $imagen = $this->request->file('imagen');
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            if (!empty($categoria['imagen'])) {
                FileManager::eliminarImagen('categorias', $categoria['imagen']);
            }
            $data['imagen'] = FileManager::subirImagen($imagen, 'categorias', 800);
        }

        Categoria::updateById($id, $data);
        $this->log('categorias', 'editar', 'categoria', $id, "Categoría editada: {$data['nombre']}");
        $this->redirect('/admin/categorias', ['success' => 'Categoría actualizada correctamente']);
    }

    public function delete(string $id): void
    {
        $id = (int) $id;
        $categoria = Categoria::find($id);
        if (!$categoria) {
            $this->redirect('/admin/categorias', ['error' => 'Categoría no encontrada']);
            return;
        }

        // Verificar si tiene comercios vinculados
        $count = Categoria::countComerciosInCategoria($id);
        if ($count > 0) {
            $this->redirect('/admin/categorias', ['error' => "No se puede eliminar: tiene {$count} comercio(s) vinculado(s)"]);
            return;
        }

        if (!empty($categoria['imagen'])) {
            FileManager::eliminarImagen('categorias', $categoria['imagen']);
        }

        Categoria::deleteById($id);
        $this->log('categorias', 'eliminar', 'categoria', $id, "Categoría eliminada: {$categoria['nombre']}");
        $this->redirect('/admin/categorias', ['success' => 'Categoría eliminada correctamente']);
    }
}
