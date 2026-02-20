<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminUsuario;
use App\Models\Sitio;
use App\Services\FileManager;

/**
 * CRUD de sitios (solo superadmin)
 */
class SitioAdminController extends Controller
{
    /**
     * GET /admin/sitios — Listado de sitios
     */
    public function index(): void
    {
        $sitios = Sitio::getAll();

        // Estadísticas por sitio
        foreach ($sitios as &$sitio) {
            $sid = (int) $sitio['id'];
            $sitio['total_comercios'] = Sitio::countComerciosBySite($sid);
            $sitio['total_categorias'] = Sitio::countCategoriasBySite($sid);
            $sitio['total_usuarios'] = AdminUsuario::countBySite($sid);
        }
        unset($sitio);

        $this->render('admin/sitios/index', [
            'title'  => 'Sitios — ' . SITE_NAME,
            'sitios' => $sitios,
        ]);
    }

    /**
     * GET /admin/sitios/crear — Formulario de creación
     */
    public function create(): void
    {
        $this->render('admin/sitios/form', [
            'title' => 'Nuevo Sitio — ' . SITE_NAME,
        ]);
    }

    /**
     * POST /admin/sitios/store — Crear sitio
     */
    public function store(): void
    {
        $v = $this->validate($_POST, [
            'nombre'  => 'required|string|min:3|max:150',
            'slug'    => 'required|slug|unique:sitios,slug',
            'dominio' => 'string|max:255',
            'ciudad'  => 'required|string|max:100',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'nombre'           => trim($_POST['nombre']),
            'slug'             => trim($_POST['slug']),
            'dominio'          => trim($_POST['dominio'] ?? '') ?: null,
            'descripcion'      => trim($_POST['descripcion'] ?? ''),
            'ciudad'           => trim($_POST['ciudad']),
            'lat'              => !empty($_POST['lat']) ? (float) $_POST['lat'] : null,
            'lng'              => !empty($_POST['lng']) ? (float) $_POST['lng'] : null,
            'zoom'             => (int) ($_POST['zoom'] ?? 15),
            'color_primario'   => $_POST['color_primario'] ?? '#2563eb',
            'color_secundario' => $_POST['color_secundario'] ?? '#1e40af',
            'email_contacto'   => trim($_POST['email_contacto'] ?? ''),
            'telefono'         => trim($_POST['telefono'] ?? ''),
            'activo'           => isset($_POST['activo']) ? 1 : 0,
        ];

        // Logo
        $logo = $this->request->file('logo');
        if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
            $data['logo'] = FileManager::subirImagen($logo, 'config', 400);
        }

        $id = Sitio::create($data);
        $this->log('sitios', 'crear', 'sitio', $id, "Sitio creado: {$data['nombre']}");
        $this->redirect('/admin/sitios', ['success' => 'Sitio creado correctamente']);
    }

    /**
     * GET /admin/sitios/editar/{id} — Formulario de edición
     */
    public function edit(string $id): void
    {
        $id = (int) $id;
        $sitio = Sitio::find($id);

        if (!$sitio) {
            $this->redirect('/admin/sitios', ['error' => 'Sitio no encontrado']);
            return;
        }

        $this->render('admin/sitios/form', [
            'title' => 'Editar Sitio — ' . SITE_NAME,
            'sitio' => $sitio,
        ]);
    }

    /**
     * POST /admin/sitios/update/{id} — Actualizar sitio
     */
    public function update(string $id): void
    {
        $id = (int) $id;
        $sitio = Sitio::find($id);

        if (!$sitio) {
            $this->redirect('/admin/sitios', ['error' => 'Sitio no encontrado']);
            return;
        }

        $v = $this->validate($_POST, [
            'nombre'  => 'required|string|min:3|max:150',
            'slug'    => "required|slug|unique:sitios,slug,{$id}",
            'ciudad'  => 'required|string|max:100',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'nombre'           => trim($_POST['nombre']),
            'slug'             => trim($_POST['slug']),
            'dominio'          => trim($_POST['dominio'] ?? '') ?: null,
            'descripcion'      => trim($_POST['descripcion'] ?? ''),
            'ciudad'           => trim($_POST['ciudad']),
            'lat'              => !empty($_POST['lat']) ? (float) $_POST['lat'] : null,
            'lng'              => !empty($_POST['lng']) ? (float) $_POST['lng'] : null,
            'zoom'             => (int) ($_POST['zoom'] ?? 15),
            'color_primario'   => $_POST['color_primario'] ?? '#2563eb',
            'color_secundario' => $_POST['color_secundario'] ?? '#1e40af',
            'email_contacto'   => trim($_POST['email_contacto'] ?? ''),
            'telefono'         => trim($_POST['telefono'] ?? ''),
            'activo'           => isset($_POST['activo']) ? 1 : 0,
        ];

        // Logo
        $logo = $this->request->file('logo');
        if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
            if (!empty($sitio['logo'])) {
                FileManager::eliminarImagen('config', $sitio['logo']);
            }
            $data['logo'] = FileManager::subirImagen($logo, 'config', 400);
        }

        Sitio::updateById($id, $data);
        $this->log('sitios', 'editar', 'sitio', $id, "Sitio editado: {$data['nombre']}");
        $this->redirect('/admin/sitios', ['success' => 'Sitio actualizado correctamente']);
    }

    /**
     * POST /admin/sitios/toggle/{id} — Activar/desactivar
     */
    public function toggleActive(string $id): void
    {
        $id = (int) $id;

        // No permitir desactivar el sitio principal
        if ($id === 1) {
            $this->json(['ok' => false, 'error' => 'No se puede desactivar el sitio principal'], 403);
            return;
        }

        $sitio = Sitio::find($id);
        if (!$sitio) {
            $this->json(['ok' => false, 'error' => 'No encontrado'], 404);
            return;
        }

        $newState = $sitio['activo'] ? 0 : 1;
        Sitio::updateById($id, ['activo' => $newState]);

        $accion = $newState ? 'activar' : 'desactivar';
        $this->log('sitios', $accion, 'sitio', $id, $sitio['nombre']);
        $this->json(['ok' => true, 'activo' => $newState, 'csrf' => $_SESSION['csrf_token']]);
    }

    /**
     * POST /admin/sitios/cambiar — Cambiar sitio activo en sesión (superadmin)
     */
    public function switchSite(): void
    {
        $siteId = (int) $this->request->post('site_id', 0);

        $sitio = Sitio::find($siteId);
        if (!$sitio || !$sitio['activo']) {
            $this->back(['error' => 'Sitio no encontrado']);
            return;
        }

        $_SESSION['admin_site_id'] = $siteId;
        $this->back(['success' => "Cambiado a: {$sitio['nombre']}"]);
    }
}
