<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\FileManager;
use App\Services\Notification;

/**
 * CRUD completo de comercios
 */
class ComercioAdminController extends Controller
{
    public function index(): void
    {
        $page       = max(1, (int) $this->request->get('page', 1));
        $perPage    = ADMIN_PER_PAGE;
        $buscar     = trim($this->request->get('q', ''));
        $catFilter  = (int) $this->request->get('categoria', 0);
        $planFilter = $this->request->get('plan', '');
        $estadoFilter = $this->request->get('estado', '');

        $where  = '1=1';
        $params = [];

        if ($buscar !== '') {
            $where .= ' AND c.nombre LIKE ?';
            $params[] = "%{$buscar}%";
        }
        if ($catFilter > 0) {
            $where .= ' AND c.id IN (SELECT comercio_id FROM comercio_categoria WHERE categoria_id = ?)';
            $params[] = $catFilter;
        }
        if (in_array($planFilter, ['basico', 'premium', 'sponsor'], true)) {
            $where .= ' AND c.plan = ?';
            $params[] = $planFilter;
        }
        if ($estadoFilter === '1') {
            $where .= ' AND c.activo = 1';
        } elseif ($estadoFilter === '0') {
            $where .= ' AND c.activo = 0';
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as total FROM comercios c WHERE {$where}",
            $params
        )['total'] ?? 0;

        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $comercios = $this->db->fetchAll(
            "SELECT c.*,
                    GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') as categorias_nombres
             FROM comercios c
             LEFT JOIN comercio_categoria cc ON c.id = cc.comercio_id
             LEFT JOIN categorias cat ON cc.categoria_id = cat.id
             WHERE {$where}
             GROUP BY c.id
             ORDER BY c.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $categorias = $this->db->fetchAll("SELECT id, nombre, icono FROM categorias WHERE activo = 1 ORDER BY orden");

        $this->render('admin/comercios/index', [
            'title'        => 'Comercios — ' . SITE_NAME,
            'comercios'    => $comercios,
            'categorias'   => $categorias,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
            'total'        => $total,
            'filters'      => [
                'q'         => $buscar,
                'categoria' => $catFilter,
                'plan'      => $planFilter,
                'estado'    => $estadoFilter,
            ],
        ]);
    }

    public function create(): void
    {
        $categorias = $this->db->fetchAll("SELECT id, nombre, icono FROM categorias WHERE activo = 1 ORDER BY orden");
        $fechas = $this->db->fetchAll("SELECT id, nombre, icono, tipo FROM fechas_especiales WHERE activo = 1 ORDER BY tipo, nombre");

        $this->render('admin/comercios/form', [
            'title'       => 'Nuevo Comercio — ' . SITE_NAME,
            'categorias'  => $categorias,
            'fechas'      => $fechas,
        ]);
    }

    public function store(): void
    {
        $v = $this->validate($_POST, [
            'nombre'      => 'required|string|min:3|max:150',
            'slug'        => 'required|slug|unique:comercios,slug',
            'descripcion' => 'string|max:5000',
            'telefono'    => 'string|max:20',
            'whatsapp'    => 'string|max:20',
            'email'       => 'email|max:150',
            'sitio_web'   => 'url|max:255',
            'direccion'   => 'string|max:255',
            'plan'        => 'required|in:freemium,basico,premium,sponsor,banner',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = $v->validated();
        $data['descripcion']     = $_POST['descripcion'] ?? '';
        $data['telefono']        = $_POST['telefono'] ?? '';
        $data['whatsapp']        = $_POST['whatsapp'] ?? '';
        $data['email']           = $_POST['email'] ?? '';
        $data['sitio_web']       = $_POST['sitio_web'] ?? '';
        $data['direccion']       = $_POST['direccion'] ?? '';
        $data['lat']             = !empty($_POST['lat']) ? (float) $_POST['lat'] : null;
        $data['lng']             = !empty($_POST['lng']) ? (float) $_POST['lng'] : null;
        $data['activo']          = isset($_POST['activo']) ? 1 : 0;
        $data['destacado']       = isset($_POST['destacado']) ? 1 : 0;
        $data['seo_titulo']      = trim($_POST['seo_titulo'] ?? '');
        $data['seo_descripcion'] = trim($_POST['seo_descripcion'] ?? '');
        $data['seo_keywords']    = trim($_POST['seo_keywords'] ?? '');

        // ── Redes sociales ──
        $data['facebook']    = trim($_POST['facebook'] ?? '') ?: null;
        $data['instagram']   = trim($_POST['instagram'] ?? '') ?: null;
        $data['tiktok']      = trim($_POST['tiktok'] ?? '') ?: null;
        $data['youtube']     = trim($_POST['youtube'] ?? '') ?: null;
        $data['x_twitter']   = trim($_POST['x_twitter'] ?? '') ?: null;
        $data['linkedin']    = trim($_POST['linkedin'] ?? '') ?: null;
        $data['telegram']    = trim($_POST['telegram'] ?? '') ?: null;
        $data['pinterest']   = trim($_POST['pinterest'] ?? '') ?: null;

        // ── Datos de facturación (privados) ──
        $data['razon_social']         = trim($_POST['razon_social'] ?? '') ?: null;
        $data['rut_empresa']          = trim($_POST['rut_empresa'] ?? '') ?: null;
        $data['giro']                 = trim($_POST['giro'] ?? '') ?: null;
        $data['direccion_tributaria'] = trim($_POST['direccion_tributaria'] ?? '') ?: null;
        $data['comuna_tributaria']    = trim($_POST['comuna_tributaria'] ?? '') ?: null;
        $data['contacto_nombre']      = trim($_POST['contacto_nombre'] ?? '') ?: null;
        $data['contacto_rut']         = trim($_POST['contacto_rut'] ?? '') ?: null;
        $data['contacto_telefono']    = trim($_POST['contacto_telefono'] ?? '') ?: null;
        $data['contacto_email']       = trim($_POST['contacto_email'] ?? '') ?: null;
        $data['contrato_inicio']      = !empty($_POST['contrato_inicio']) ? $_POST['contrato_inicio'] : null;
        $data['contrato_monto']       = !empty($_POST['contrato_monto']) ? (int) $_POST['contrato_monto'] : null;
        $data['metodo_pago']          = trim($_POST['metodo_pago'] ?? '') ?: null;

        // Logo
        $logo = $this->request->file('logo');
        if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
            $data['logo'] = FileManager::subirImagen($logo, 'logos', 400);
        }

        // Portada
        $portada = $this->request->file('portada');
        if ($portada && $portada['error'] === UPLOAD_ERR_OK) {
            $data['portada'] = FileManager::subirImagen($portada, 'portadas', 1200);
        }

        $id = $this->db->insert('comercios', $data);

        // Categorías
        $this->syncCategorias($id, $_POST['categorias'] ?? [], $_POST['categoria_principal'] ?? 0);

        // Fechas especiales
        $this->syncFechas($id, $_POST['fechas'] ?? [], $_POST);

        \App\Models\Comercio::recalcularCalidad($id);

        $this->log('comercios', 'crear', 'comercio', $id, "Comercio creado: {$data['nombre']}");

        // Notificaciones
        $comercioCreado = $this->db->fetch("SELECT * FROM comercios WHERE id = ?", [$id]);
        if ($comercioCreado) {
            Notification::nuevoComercio($comercioCreado);
            Notification::bienvenidaComercio($comercioCreado);
        }

        $this->redirect('/admin/comercios', ['success' => 'Comercio creado correctamente']);
    }

    public function edit(string $id): void
    {
        $id = (int) $id;
        $comercio = $this->db->fetch("SELECT * FROM comercios WHERE id = ?", [$id]);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $categorias = $this->db->fetchAll("SELECT id, nombre, icono FROM categorias WHERE activo = 1 ORDER BY orden");
        $fechas = $this->db->fetchAll("SELECT id, nombre, icono, tipo FROM fechas_especiales WHERE activo = 1 ORDER BY tipo, nombre");

        // Categorías del comercio
        $comercioCats = $this->db->fetchAll(
            "SELECT categoria_id, es_principal FROM comercio_categoria WHERE comercio_id = ?",
            [$id]
        );
        $catIds = array_column($comercioCats, 'categoria_id');
        $catPrincipal = 0;
        foreach ($comercioCats as $cc) {
            if ($cc['es_principal']) {
                $catPrincipal = (int) $cc['categoria_id'];
            }
        }

        // Fechas del comercio
        $comercioFechas = $this->db->fetchAll(
            "SELECT fecha_id, oferta_especial, precio_desde, precio_hasta FROM comercio_fecha WHERE comercio_id = ?",
            [$id]
        );
        $fechaIds = array_column($comercioFechas, 'fecha_id');
        $fechaData = [];
        foreach ($comercioFechas as $cf) {
            $fechaData[$cf['fecha_id']] = $cf;
        }

        $this->render('admin/comercios/form', [
            'title'             => 'Editar Comercio — ' . SITE_NAME,
            'comercio'          => $comercio,
            'categorias'        => $categorias,
            'fechas'            => $fechas,
            'catIds'            => $catIds,
            'catPrincipal'      => $catPrincipal,
            'fechaIds'          => $fechaIds,
            'fechaData'         => $fechaData,
        ]);
    }

    public function update(string $id): void
    {
        $id = (int) $id;
        $comercio = $this->db->fetch("SELECT * FROM comercios WHERE id = ?", [$id]);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $v = $this->validate($_POST, [
            'nombre'      => 'required|string|min:3|max:150',
            'slug'        => "required|slug|unique:comercios,slug,{$id}",
            'plan'        => 'required|in:freemium,basico,premium,sponsor,banner',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'nombre'          => trim($_POST['nombre']),
            'slug'            => trim($_POST['slug']),
            'descripcion'     => $_POST['descripcion'] ?? '',
            'telefono'        => $_POST['telefono'] ?? '',
            'whatsapp'        => $_POST['whatsapp'] ?? '',
            'email'           => $_POST['email'] ?? '',
            'sitio_web'       => $_POST['sitio_web'] ?? '',
            'direccion'       => $_POST['direccion'] ?? '',
            'lat'             => !empty($_POST['lat']) ? (float) $_POST['lat'] : null,
            'lng'             => !empty($_POST['lng']) ? (float) $_POST['lng'] : null,
            'plan'            => $_POST['plan'],
            'activo'          => isset($_POST['activo']) ? 1 : 0,
            'destacado'       => isset($_POST['destacado']) ? 1 : 0,
            'seo_titulo'      => trim($_POST['seo_titulo'] ?? ''),
            'seo_descripcion' => trim($_POST['seo_descripcion'] ?? ''),
            'seo_keywords'    => trim($_POST['seo_keywords'] ?? ''),
        ];

        // ── Redes sociales ──
        $data['facebook']    = trim($_POST['facebook'] ?? '') ?: null;
        $data['instagram']   = trim($_POST['instagram'] ?? '') ?: null;
        $data['tiktok']      = trim($_POST['tiktok'] ?? '') ?: null;
        $data['youtube']     = trim($_POST['youtube'] ?? '') ?: null;
        $data['x_twitter']   = trim($_POST['x_twitter'] ?? '') ?: null;
        $data['linkedin']    = trim($_POST['linkedin'] ?? '') ?: null;
        $data['telegram']    = trim($_POST['telegram'] ?? '') ?: null;
        $data['pinterest']   = trim($_POST['pinterest'] ?? '') ?: null;

        // ── Datos de facturación (privados) ──
        $data['razon_social']         = trim($_POST['razon_social'] ?? '') ?: null;
        $data['rut_empresa']          = trim($_POST['rut_empresa'] ?? '') ?: null;
        $data['giro']                 = trim($_POST['giro'] ?? '') ?: null;
        $data['direccion_tributaria'] = trim($_POST['direccion_tributaria'] ?? '') ?: null;
        $data['comuna_tributaria']    = trim($_POST['comuna_tributaria'] ?? '') ?: null;
        $data['contacto_nombre']      = trim($_POST['contacto_nombre'] ?? '') ?: null;
        $data['contacto_rut']         = trim($_POST['contacto_rut'] ?? '') ?: null;
        $data['contacto_telefono']    = trim($_POST['contacto_telefono'] ?? '') ?: null;
        $data['contacto_email']       = trim($_POST['contacto_email'] ?? '') ?: null;
        $data['contrato_inicio']      = !empty($_POST['contrato_inicio']) ? $_POST['contrato_inicio'] : null;
        $data['contrato_monto']       = !empty($_POST['contrato_monto']) ? (int) $_POST['contrato_monto'] : null;
        $data['metodo_pago']          = trim($_POST['metodo_pago'] ?? '') ?: null;

        // Logo
        $logo = $this->request->file('logo');
        if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
            if (!empty($comercio['logo'])) {
                FileManager::eliminarImagen('logos', $comercio['logo']);
            }
            $data['logo'] = FileManager::subirImagen($logo, 'logos', 400);
        }

        // Portada
        $portada = $this->request->file('portada');
        if ($portada && $portada['error'] === UPLOAD_ERR_OK) {
            if (!empty($comercio['portada'])) {
                FileManager::eliminarImagen('portadas', $comercio['portada']);
            }
            $data['portada'] = FileManager::subirImagen($portada, 'portadas', 1200);
        }

        $this->db->update('comercios', $data, 'id = ?', [$id]);

        // Categorías
        $this->syncCategorias($id, $_POST['categorias'] ?? [], $_POST['categoria_principal'] ?? 0);

        // Fechas especiales
        $this->syncFechas($id, $_POST['fechas'] ?? [], $_POST);

        \App\Models\Comercio::recalcularCalidad($id);

        $this->log('comercios', 'editar', 'comercio', $id, "Comercio editado: {$data['nombre']}");
        $this->redirect('/admin/comercios', ['success' => 'Comercio actualizado correctamente']);
    }

    public function toggleActive(string $id): void
    {
        $id = (int) $id;
        $comercio = $this->db->fetch("SELECT id, nombre, activo FROM comercios WHERE id = ?", [$id]);
        if (!$comercio) {
            $this->json(['ok' => false, 'error' => 'No encontrado'], 404);
            return;
        }

        $newState = $comercio['activo'] ? 0 : 1;
        $this->db->update('comercios', ['activo' => $newState], 'id = ?', [$id]);

        $accion = $newState ? 'activar' : 'desactivar';
        $this->log('comercios', $accion, 'comercio', $id, "{$comercio['nombre']}");
        $this->json(['ok' => true, 'activo' => $newState, 'csrf' => $_SESSION['csrf_token']]);
    }

    public function delete(string $id): void
    {
        $id = (int) $id;
        $comercio = $this->db->fetch("SELECT * FROM comercios WHERE id = ?", [$id]);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        // Eliminar imágenes
        if (!empty($comercio['logo'])) {
            FileManager::eliminarImagen('logos', $comercio['logo']);
        }
        if (!empty($comercio['portada'])) {
            FileManager::eliminarImagen('portadas', $comercio['portada']);
        }

        // Eliminar fotos de galería
        $fotos = $this->db->fetchAll("SELECT ruta FROM comercio_fotos WHERE comercio_id = ?", [$id]);
        foreach ($fotos as $foto) {
            FileManager::eliminarImagen('galeria', $foto['ruta']);
        }

        $this->db->delete('comercios', 'id = ?', [$id]);
        $this->log('comercios', 'eliminar', 'comercio', $id, "Comercio eliminado: {$comercio['nombre']}");
        $this->redirect('/admin/comercios', ['success' => 'Comercio eliminado correctamente']);
    }

    public function gallery(string $id): void
    {
        $id = (int) $id;
        $comercio = $this->db->fetch("SELECT id, nombre, slug FROM comercios WHERE id = ?", [$id]);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $fotos = $this->db->fetchAll(
            "SELECT * FROM comercio_fotos WHERE comercio_id = ? ORDER BY orden ASC",
            [$id]
        );

        $this->render('admin/comercios/galeria', [
            'title'    => 'Galería — ' . e($comercio['nombre']),
            'comercio' => $comercio,
            'fotos'    => $fotos,
        ]);
    }

    public function storePhoto(string $id): void
    {
        $id = (int) $id;
        $comercio = $this->db->fetch("SELECT id, nombre FROM comercios WHERE id = ?", [$id]);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $foto = $this->request->file('foto');
        if (!$foto || $foto['error'] !== UPLOAD_ERR_OK) {
            $this->redirect("/admin/comercios/{$id}/galeria", ['error' => 'Selecciona una imagen']);
            return;
        }

        $fileName = FileManager::subirImagen($foto, 'galeria', 1200);
        if (!$fileName) {
            $this->redirect("/admin/comercios/{$id}/galeria", ['error' => 'Error al subir la imagen']);
            return;
        }

        $maxOrden = $this->db->fetch(
            "SELECT MAX(orden) as m FROM comercio_fotos WHERE comercio_id = ?",
            [$id]
        )['m'] ?? 0;

        $this->db->insert('comercio_fotos', [
            'comercio_id' => $id,
            'ruta'        => $fileName,
            'ruta_thumb'  => $fileName,
            'titulo'      => trim($_POST['titulo'] ?? ''),
            'orden'       => $maxOrden + 1,
        ]);

        $this->log('comercios', 'foto_agregar', 'comercio', $id, "Foto agregada a {$comercio['nombre']}");
        $this->redirect("/admin/comercios/{$id}/galeria", ['success' => 'Foto agregada']);
    }

    public function deletePhoto(string $id): void
    {
        $id = (int) $id;
        $fotoId = (int) ($_POST['foto_id'] ?? 0);

        $foto = $this->db->fetch(
            "SELECT * FROM comercio_fotos WHERE id = ? AND comercio_id = ?",
            [$fotoId, $id]
        );

        if (!$foto) {
            $this->redirect("/admin/comercios/{$id}/galeria", ['error' => 'Foto no encontrada']);
            return;
        }

        FileManager::eliminarImagen('galeria', $foto['ruta']);
        $this->db->delete('comercio_fotos', 'id = ?', [$fotoId]);

        $this->log('comercios', 'foto_eliminar', 'comercio', $id, "Foto eliminada");
        $this->redirect("/admin/comercios/{$id}/galeria", ['success' => 'Foto eliminada']);
    }

    public function horarios(string $id): void
    {
        $id = (int) $id;
        $comercio = $this->db->fetch("SELECT id, nombre, slug FROM comercios WHERE id = ?", [$id]);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $horarios = $this->db->fetchAll(
            "SELECT * FROM comercio_horarios WHERE comercio_id = ? ORDER BY dia ASC",
            [$id]
        );

        // Indexar por día
        $horariosPorDia = [];
        foreach ($horarios as $h) {
            $horariosPorDia[(int) $h['dia']] = $h;
        }

        $this->render('admin/comercios/horarios', [
            'title'     => 'Horarios — ' . e($comercio['nombre']),
            'comercio'  => $comercio,
            'horarios'  => $horariosPorDia,
        ]);
    }

    public function updateHorarios(string $id): void
    {
        $id = (int) $id;
        $comercio = $this->db->fetch("SELECT id, nombre FROM comercios WHERE id = ?", [$id]);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        // Eliminar horarios anteriores y reinsertar
        $this->db->delete('comercio_horarios', 'comercio_id = ?', [$id]);

        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        for ($dia = 0; $dia <= 6; $dia++) {
            $cerrado = isset($_POST['cerrado'][$dia]) ? 1 : 0;
            $apertura = $_POST['hora_apertura'][$dia] ?? null;
            $cierre = $_POST['hora_cierre'][$dia] ?? null;

            $this->db->insert('comercio_horarios', [
                'comercio_id'   => $id,
                'dia'           => $dia,
                'hora_apertura' => $cerrado ? null : ($apertura ?: null),
                'hora_cierre'   => $cerrado ? null : ($cierre ?: null),
                'cerrado'       => $cerrado,
            ]);
        }

        $this->log('comercios', 'horarios', 'comercio', $id, "Horarios actualizados: {$comercio['nombre']}");
        $this->redirect("/admin/comercios/{$id}/horarios", ['success' => 'Horarios actualizados']);
    }

    // ── Helpers privados ─────────────────────────────────────

    private function syncCategorias(int $comercioId, array $catIds, int|string $principal = 0): void
    {
        $this->db->delete('comercio_categoria', 'comercio_id = ?', [$comercioId]);
        $principal = (int) $principal;

        foreach ($catIds as $catId) {
            $catId = (int) $catId;
            if ($catId <= 0) continue;
            $this->db->insert('comercio_categoria', [
                'comercio_id'  => $comercioId,
                'categoria_id' => $catId,
                'es_principal' => ($catId === $principal) ? 1 : 0,
            ]);
        }
    }

    private function syncFechas(int $comercioId, array $fechaIds, array $postData): void
    {
        $this->db->delete('comercio_fecha', 'comercio_id = ?', [$comercioId]);

        foreach ($fechaIds as $fechaId) {
            $fechaId = (int) $fechaId;
            if ($fechaId <= 0) continue;
            $this->db->insert('comercio_fecha', [
                'comercio_id'    => $comercioId,
                'fecha_id'       => $fechaId,
                'oferta_especial'=> trim($postData["fecha_oferta_{$fechaId}"] ?? ''),
                'precio_desde'   => !empty($postData["fecha_precio_desde_{$fechaId}"]) ? (float) $postData["fecha_precio_desde_{$fechaId}"] : null,
                'precio_hasta'   => !empty($postData["fecha_precio_hasta_{$fechaId}"]) ? (float) $postData["fecha_precio_hasta_{$fechaId}"] : null,
                'activo'         => 1,
            ]);
        }
    }
}
