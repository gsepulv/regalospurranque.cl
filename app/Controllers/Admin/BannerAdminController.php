<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Banner;
use App\Models\Comercio;
use App\Services\FileManager;

/**
 * CRUD de banners publicitarios
 */
class BannerAdminController extends Controller
{
    public function index(): void
    {
        $tipo   = $this->request->get('tipo', '');
        $estado = $this->request->get('estado', '');

        $where  = '1=1';
        $params = [];

        if (in_array($tipo, ['hero', 'sidebar', 'entre_comercios', 'footer'], true)) {
            $where .= ' AND b.tipo = ?';
            $params[] = $tipo;
        }
        if ($estado === '1') {
            $where .= ' AND b.activo = 1';
        } elseif ($estado === '0') {
            $where .= ' AND b.activo = 0';
        }

        $banners = Banner::getAdminFiltered($where, $params);

        $this->render('admin/banners/index', [
            'title'   => 'Banners — ' . SITE_NAME,
            'banners' => $banners,
            'filters' => ['tipo' => $tipo, 'estado' => $estado],
        ]);
    }

    public function create(): void
    {
        $comercios = Comercio::getActiveForSelect();

        $this->render('admin/banners/form', [
            'title'     => 'Nuevo Banner — ' . SITE_NAME,
            'comercios' => $comercios,
        ]);
    }

    public function store(): void
    {
        $v = $this->validate($_POST, [
            'titulo' => 'required|string|min:3|max:100',
            'tipo'   => 'required|in:hero,sidebar,entre_comercios,footer',
            'url'    => 'url|min:10|max:255',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $imagen = $this->request->file('imagen');
        if (!$imagen || $imagen['error'] !== UPLOAD_ERR_OK) {
            $this->back(['errors' => ['imagen' => 'La imagen es obligatoria'], 'old' => $_POST]);
            return;
        }

        $fileName = FileManager::subirImagen($imagen, 'banners', 1920);
        if (!$fileName) {
            $this->back(['errors' => ['imagen' => 'Error al subir la imagen'], 'old' => $_POST]);
            return;
        }

        $data = [
            'titulo'       => trim($_POST['titulo'] ?? ''),
            'tipo'         => $_POST['tipo'],
            'imagen'       => $fileName,
            'url'          => trim($_POST['url'] ?? ''),
            'posicion'     => trim($_POST['posicion'] ?? ''),
            'comercio_id'  => !empty($_POST['comercio_id']) ? (int) $_POST['comercio_id'] : null,
            'activo'       => isset($_POST['activo']) ? 1 : 0,
            'fecha_inicio' => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
            'fecha_fin'    => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null,
            'orden'        => (int) ($_POST['orden'] ?? 0),
        ];

        $id = Banner::create($data);
        $this->log('banners', 'crear', 'banner', $id, "Banner creado: {$data['titulo']}");
        $this->redirect('/admin/banners', ['success' => 'Banner creado correctamente']);
    }

    public function edit(string $id): void
    {
        $id = (int) $id;
        $banner = Banner::find($id);
        if (!$banner) {
            $this->redirect('/admin/banners', ['error' => 'Banner no encontrado']);
            return;
        }

        $comercios = Comercio::getActiveForSelect();

        $this->render('admin/banners/form', [
            'title'     => 'Editar Banner — ' . SITE_NAME,
            'banner'    => $banner,
            'comercios' => $comercios,
        ]);
    }

    public function update(string $id): void
    {
        $id = (int) $id;
        $banner = Banner::find($id);
        if (!$banner) {
            $this->redirect('/admin/banners', ['error' => 'Banner no encontrado']);
            return;
        }

        $v = $this->validate($_POST, [
            'titulo' => 'required|string|min:3|max:100',
            'tipo'   => 'required|in:hero,sidebar,entre_comercios,footer',
            'url'    => 'url|min:10|max:255',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'titulo'       => trim($_POST['titulo'] ?? ''),
            'tipo'         => $_POST['tipo'],
            'url'          => trim($_POST['url'] ?? ''),
            'posicion'     => trim($_POST['posicion'] ?? ''),
            'comercio_id'  => !empty($_POST['comercio_id']) ? (int) $_POST['comercio_id'] : null,
            'activo'       => isset($_POST['activo']) ? 1 : 0,
            'fecha_inicio' => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
            'fecha_fin'    => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null,
            'orden'        => (int) ($_POST['orden'] ?? 0),
        ];

        $imagen = $this->request->file('imagen');
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            if (!empty($banner['imagen'])) {
                FileManager::eliminarImagen('banners', $banner['imagen']);
            }
            $data['imagen'] = FileManager::subirImagen($imagen, 'banners', 1920);
        }

        Banner::updateById($id, $data);
        $this->log('banners', 'editar', 'banner', $id, "Banner editado: {$data['titulo']}");
        $this->redirect('/admin/banners', ['success' => 'Banner actualizado correctamente']);
    }

    public function toggleActive(string $id): void
    {
        $id = (int) $id;
        $banner = Banner::find($id);
        if (!$banner) {
            $this->json(['ok' => false, 'error' => 'No encontrado'], 404);
            return;
        }

        $newState = $banner['activo'] ? 0 : 1;
        Banner::updateById($id, ['activo' => $newState]);

        $this->log('banners', $newState ? 'activar' : 'desactivar', 'banner', $id, $banner['titulo']);
        $this->json(['ok' => true, 'activo' => $newState, 'csrf' => $_SESSION['csrf_token']]);
    }

    public function delete(string $id): void
    {
        $id = (int) $id;
        $banner = Banner::find($id);
        if (!$banner) {
            $this->redirect('/admin/banners', ['error' => 'Banner no encontrado']);
            return;
        }

        if (!empty($banner['imagen'])) {
            FileManager::eliminarImagen('banners', $banner['imagen']);
        }

        Banner::deleteById($id);
        $this->log('banners', 'eliminar', 'banner', $id, "Banner eliminado: {$banner['titulo']}");
        $this->redirect('/admin/banners', ['success' => 'Banner eliminado correctamente']);
    }

    public function resetStats(string $id): void
    {
        $id = (int) $id;
        $banner = Banner::find($id);
        if (!$banner) {
            $this->redirect('/admin/banners', ['error' => 'Banner no encontrado']);
            return;
        }

        Banner::resetStats($id);
        $this->log('banners', 'reset_stats', 'banner', $id, "Stats reseteadas: {$banner['titulo']}");
        $this->redirect('/admin/banners', ['success' => 'Estadísticas reseteadas']);
    }
}
