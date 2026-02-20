<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\FechaEspecial;
use App\Services\FileManager;

/**
 * CRUD de fechas especiales
 */
class FechaAdminController extends Controller
{
    public function index(): void
    {
        $tipo = $this->request->get('tipo', '');
        $tipoFilter = in_array($tipo, ['personal', 'calendario', 'comercial'], true) ? $tipo : null;

        $fechas = FechaEspecial::getAdminFiltered($tipoFilter);

        $this->render('admin/fechas/index', [
            'title'     => 'Fechas Especiales — ' . SITE_NAME,
            'fechas'    => $fechas,
            'tipoActivo'=> $tipo,
        ]);
    }

    public function create(): void
    {
        $this->render('admin/fechas/form', [
            'title' => 'Nueva Fecha Especial — ' . SITE_NAME,
        ]);
    }

    public function store(): void
    {
        $v = $this->validate($_POST, [
            'nombre' => 'required|string|min:2|max:150',
            'slug'   => 'required|slug|unique:fechas_especiales,slug',
            'tipo'   => 'required|in:personal,calendario,comercial',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        // Sanitizar colores
        $color = null;
        if (!empty($_POST['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color'])) {
            $color = $_POST['color'];
        }
        $colorTexto = '#ffffff';
        if (!empty($_POST['color_texto']) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color_texto'])) {
            $colorTexto = $_POST['color_texto'];
        }

        $data = [
            'nombre'       => trim($_POST['nombre']),
            'slug'         => trim($_POST['slug']),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'tipo'         => $_POST['tipo'],
            'icono'        => trim($_POST['icono'] ?? ''),
            'color'        => $color,
            'color_texto'  => $colorTexto,
            'fecha_inicio' => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
            'fecha_fin'    => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null,
            'recurrente'   => isset($_POST['recurrente']) ? 1 : 0,
            'activo'       => isset($_POST['activo']) ? 1 : 0,
        ];

        $imagen = $this->request->file('imagen');
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            $data['imagen'] = FileManager::subirImagen($imagen, 'fechas', 800);
        }

        $id = FechaEspecial::create($data);
        $this->log('fechas', 'crear', 'fecha', $id, "Fecha creada: {$data['nombre']}");
        $this->redirect('/admin/fechas', ['success' => 'Fecha especial creada correctamente']);
    }

    public function edit(string $id): void
    {
        $id = (int) $id;
        $fecha = FechaEspecial::find($id);
        if (!$fecha) {
            $this->redirect('/admin/fechas', ['error' => 'Fecha no encontrada']);
            return;
        }

        $this->render('admin/fechas/form', [
            'title' => 'Editar Fecha Especial — ' . SITE_NAME,
            'fecha' => $fecha,
        ]);
    }

    public function update(string $id): void
    {
        $id = (int) $id;
        $fecha = FechaEspecial::find($id);
        if (!$fecha) {
            $this->redirect('/admin/fechas', ['error' => 'Fecha no encontrada']);
            return;
        }

        $v = $this->validate($_POST, [
            'nombre' => 'required|string|min:2|max:150',
            'slug'   => "required|slug|unique:fechas_especiales,slug,{$id}",
            'tipo'   => 'required|in:personal,calendario,comercial',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        // Sanitizar colores
        $color = null;
        if (!empty($_POST['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color'])) {
            $color = $_POST['color'];
        }
        $colorTexto = '#ffffff';
        if (!empty($_POST['color_texto']) && preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['color_texto'])) {
            $colorTexto = $_POST['color_texto'];
        }

        $data = [
            'nombre'       => trim($_POST['nombre']),
            'slug'         => trim($_POST['slug']),
            'descripcion'  => trim($_POST['descripcion'] ?? ''),
            'tipo'         => $_POST['tipo'],
            'icono'        => trim($_POST['icono'] ?? ''),
            'color'        => $color,
            'color_texto'  => $colorTexto,
            'fecha_inicio' => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
            'fecha_fin'    => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null,
            'recurrente'   => isset($_POST['recurrente']) ? 1 : 0,
            'activo'       => isset($_POST['activo']) ? 1 : 0,
        ];

        $imagen = $this->request->file('imagen');
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            if (!empty($fecha['imagen'])) {
                FileManager::eliminarImagen('fechas', $fecha['imagen']);
            }
            $data['imagen'] = FileManager::subirImagen($imagen, 'fechas', 800);
        }

        FechaEspecial::updateById($id, $data);
        $this->log('fechas', 'editar', 'fecha', $id, "Fecha editada: {$data['nombre']}");
        $this->redirect('/admin/fechas', ['success' => 'Fecha especial actualizada correctamente']);
    }

    public function delete(string $id): void
    {
        $id = (int) $id;
        $fecha = FechaEspecial::find($id);
        if (!$fecha) {
            $this->redirect('/admin/fechas', ['error' => 'Fecha no encontrada']);
            return;
        }

        $count = FechaEspecial::countComerciosInFecha($id);
        if ($count > 0) {
            $this->redirect('/admin/fechas', ['error' => "No se puede eliminar: tiene {$count} comercio(s) vinculado(s)"]);
            return;
        }

        if (!empty($fecha['imagen'])) {
            FileManager::eliminarImagen('fechas', $fecha['imagen']);
        }

        FechaEspecial::deleteById($id);
        $this->log('fechas', 'eliminar', 'fecha', $id, "Fecha eliminada: {$fecha['nombre']}");
        $this->redirect('/admin/fechas', ['success' => 'Fecha especial eliminada correctamente']);
    }
}
