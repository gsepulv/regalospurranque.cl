<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminUsuario;
use App\Models\Categoria;
use App\Models\Comercio;
use App\Models\FechaEspecial;
use App\Models\PlanConfig;
use App\Core\Database;
use App\Models\Producto;
use App\Models\ProductoFoto;
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
        $validacionFilter = $this->request->get('validacion', '');

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
        if ($validacionFilter === 'pendiente') {
            $where .= ' AND c.validado = 0';
        } elseif ($validacionFilter === 'validado') {
            $where .= ' AND c.validado = 1';
        }

        $total = Comercio::countAdminFiltered($where, $params);

        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $comercios = Comercio::getAdminFiltered($where, $params, $perPage, $offset);

        $categorias = Categoria::getActiveForSelect();

        // Contar pendientes de validación para badge
        $pendientesCount = Comercio::countAdminFiltered('c.validado = 0', []);

        $this->render('admin/comercios/index', [
            'title'           => 'Comercios — ' . SITE_NAME,
            'comercios'       => $comercios,
            'categorias'      => $categorias,
            'currentPage'     => $page,
            'totalPages'      => $totalPages,
            'total'           => $total,
            'pendientesCount' => $pendientesCount,
            'filters'         => [
                'q'          => $buscar,
                'categoria'  => $catFilter,
                'plan'       => $planFilter,
                'estado'     => $estadoFilter,
                'validacion' => $validacionFilter,
            ],
        ]);
    }

    public function create(): void
    {
        $categorias = Categoria::getActiveForSelect();
        $fechas = FechaEspecial::getActiveForSelect();

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
            'slug'        => 'required|slug|max:170|unique:comercios,slug',
            'descripcion' => 'required|string|min:20|max:5000',
            'telefono'    => 'required|string|min:9|max:20',
            'whatsapp'    => 'required|string|min:9|max:20',
            'email'       => 'required|email|max:150',
            'sitio_web'   => 'required|url|max:255',
            'direccion'   => 'required|string|min:5|max:255',
            'plan'        => 'required|in:freemium,basico,premium,sponsor,banner',
            // Redes sociales
            'facebook'            => 'url|max:300',
            'instagram'           => 'url|max:300',
            'tiktok'              => 'url|max:300',
            'youtube'             => 'url|max:300',
            'x_twitter'           => 'url|max:300',
            'linkedin'            => 'url|max:300',
            'telegram'            => 'url|max:300',
            'pinterest'           => 'url|max:300',
            // SEO
            'seo_titulo'          => 'string|max:160',
            'seo_descripcion'     => 'string|max:320',
            'seo_keywords'        => 'string|max:255',
            // Datos tributarios
            'razon_social'        => 'string|max:200',
            'rut_empresa'         => 'string|max:15',
            'giro'                => 'string|max:200',
            'direccion_tributaria' => 'string|max:300',
            'comuna_tributaria'   => 'string|max:100',
            // Contacto propietario
            'contacto_nombre'     => 'string|max:150',
            'contacto_rut'        => 'string|max:15',
            'contacto_telefono'   => 'string|max:20',
            'contacto_email'      => 'email|max:200',
            // Coordenadas
            'lat'                 => 'latitude',
            'lng'                 => 'longitude',
        ]);

        // Validar tamaño de archivos
        $fileErrors = [];
        $maxBytes = UPLOAD_MAX_SIZE;
        $maxMb = round($maxBytes / 1024 / 1024);
        foreach (['logo', 'portada'] as $campo) {
            if (!empty($_FILES[$campo]['tmp_name']) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                if ($_FILES[$campo]['size'] > $maxBytes) {
                    $fileErrors[$campo] = "La imagen no debe superar {$maxMb} MB";
                }
            }
        }

        if ($v->fails() || !empty($fileErrors)) {
            $this->back(['errors' => array_merge($v->errors(), $fileErrors), 'old' => $_POST]);
            return;
        }

        $data = $v->validated();
        $data['descripcion']     = $_POST['descripcion'] ?? '';
        $data['telefono']        = $_POST['telefono'] ?? '';
        $data['whatsapp']        = $_POST['whatsapp'] ?? '';
        $data['email']           = $_POST['email'] ?? '';
        $data['sitio_web']       = $_POST['sitio_web'] ?? '';
        $data['direccion']       = $_POST['direccion'] ?? '';
        $data['delivery_local']  = isset($_POST['delivery_local']) ? 1 : 0;
        $data['envios_chile']    = isset($_POST['envios_chile']) ? 1 : 0;
        $data['lat']             = !empty($_POST['lat']) ? (float) $_POST['lat'] : null;
        $data['lng']             = !empty($_POST['lng']) ? (float) $_POST['lng'] : null;
        $data['activo']          = isset($_POST['activo']) ? 1 : 0;
        $data['destacado']       = isset($_POST['destacado']) ? 1 : 0;
        $data['validado']        = isset($_POST['validado']) ? 1 : 0;
        $data['validado_fecha']  = isset($_POST['validado']) ? date('Y-m-d H:i:s') : null;
        $data['registrado_por']  = !empty($_POST['registrado_por']) ? (int) $_POST['registrado_por'] : null;
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
            $data['logo'] = FileManager::subirImagen($logo, 'logos', 800);
        }

        // Portada
        $portada = $this->request->file('portada');
        if ($portada && $portada['error'] === UPLOAD_ERR_OK) {
            $data['portada'] = FileManager::subirImagen($portada, 'portadas', 1200);
        }

        // Preparar datos de fechas
        $fechaIds = $_POST['fechas'] ?? [];
        $ofertas = [];
        foreach ($fechaIds as $fId) {
            $ofertas[$fId] = [
                'oferta_especial' => trim($_POST["fecha_oferta_{$fId}"] ?? ''),
                'precio_desde'    => !empty($_POST["fecha_precio_desde_{$fId}"]) ? (float) $_POST["fecha_precio_desde_{$fId}"] : null,
                'precio_hasta'    => !empty($_POST["fecha_precio_hasta_{$fId}"]) ? (float) $_POST["fecha_precio_hasta_{$fId}"] : null,
            ];
        }
        $catIds = $_POST['categorias'] ?? [];
        $catPrincipal = (int) ($_POST['categoria_principal'] ?? 0);

        $id = $this->db->transaction(function () use ($data, $catIds, $catPrincipal, $fechaIds, $ofertas) {
            $id = Comercio::create($data);
            Comercio::syncCategorias($id, $catIds, $catPrincipal);
            Comercio::syncFechas($id, $fechaIds, $ofertas);
            Comercio::recalcularCalidad($id);
            return $id;
        });

        $this->log('comercios', 'crear', 'comercio', $id, "Comercio creado: {$data['nombre']}");

        // Notificaciones
        $comercioCreado = Comercio::find($id);
        if ($comercioCreado) {
            Notification::nuevoComercio($comercioCreado);
            Notification::bienvenidaComercio($comercioCreado);
        }

        try { (new \App\Services\SitemapService())->generateAndSave(); } catch (\Throwable $e) {}
        $this->redirect('/admin/comercios', ['success' => 'Comercio creado correctamente']);
    }

    public function edit(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $categorias = Categoria::getActiveForSelect();
        $fechas = FechaEspecial::getActiveForSelect();

        // Categorías del comercio
        $comercioCats = Comercio::getCategoriaIds($id);
        $catIds = array_column($comercioCats, 'categoria_id');
        $catPrincipal = 0;
        foreach ($comercioCats as $cc) {
            if ($cc['es_principal']) {
                $catPrincipal = (int) $cc['categoria_id'];
            }
        }

        // Fechas del comercio
        $comercioFechas = Comercio::getFechaIds($id);
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
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $v = $this->validate($_POST, [
            'nombre'      => 'required|string|min:3|max:150',
            'slug'        => "required|slug|max:170|unique:comercios,slug,{$id}",
            'descripcion' => 'required|string|min:20|max:5000',
            'telefono'    => 'required|string|min:9|max:20',
            'whatsapp'    => 'required|string|min:9|max:20',
            'email'       => 'required|email|max:150',
            'sitio_web'   => 'required|url|max:255',
            'direccion'   => 'required|string|min:5|max:255',
            'plan'        => 'required|in:freemium,basico,premium,sponsor,banner',
            // Redes sociales
            'facebook'            => 'url|max:300',
            'instagram'           => 'url|max:300',
            'tiktok'              => 'url|max:300',
            'youtube'             => 'url|max:300',
            'x_twitter'           => 'url|max:300',
            'linkedin'            => 'url|max:300',
            'telegram'            => 'url|max:300',
            'pinterest'           => 'url|max:300',
            // SEO
            'seo_titulo'          => 'string|max:160',
            'seo_descripcion'     => 'string|max:320',
            'seo_keywords'        => 'string|max:255',
            // Datos tributarios
            'razon_social'        => 'string|max:200',
            'rut_empresa'         => 'string|max:15',
            'giro'                => 'string|max:200',
            'direccion_tributaria' => 'string|max:300',
            'comuna_tributaria'   => 'string|max:100',
            // Contacto propietario
            'contacto_nombre'     => 'string|max:150',
            'contacto_rut'        => 'string|max:15',
            'contacto_telefono'   => 'string|max:20',
            'contacto_email'      => 'email|max:200',
            // Coordenadas
            'lat'                 => 'latitude',
            'lng'                 => 'longitude',
        ]);

        // Validar tamaño de archivos
        $fileErrors = [];
        $maxBytes = UPLOAD_MAX_SIZE;
        $maxMb = round($maxBytes / 1024 / 1024);
        foreach (['logo', 'portada'] as $campo) {
            if (!empty($_FILES[$campo]['tmp_name']) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                if ($_FILES[$campo]['size'] > $maxBytes) {
                    $fileErrors[$campo] = "La imagen no debe superar {$maxMb} MB";
                }
            }
        }

        if ($v->fails() || !empty($fileErrors)) {
            $this->back(['errors' => array_merge($v->errors(), $fileErrors), 'old' => $_POST]);
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
            'validado'        => isset($_POST['validado']) ? 1 : 0,
            'validado_fecha'  => isset($_POST['validado']) ? date('Y-m-d H:i:s') : null,
            'registrado_por'  => !empty($_POST['registrado_por']) ? (int) $_POST['registrado_por'] : null,
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
            $data['logo'] = FileManager::subirImagen($logo, 'logos', 800);
        }

        // Portada
        $portada = $this->request->file('portada');
        if ($portada && $portada['error'] === UPLOAD_ERR_OK) {
            if (!empty($comercio['portada'])) {
                FileManager::eliminarImagen('portadas', $comercio['portada']);
            }
            $data['portada'] = FileManager::subirImagen($portada, 'portadas', 1200);
        }

        // Preparar datos de fechas
        $fechaIds = $_POST['fechas'] ?? [];
        $ofertas = [];
        foreach ($fechaIds as $fId) {
            $ofertas[$fId] = [
                'oferta_especial' => trim($_POST["fecha_oferta_{$fId}"] ?? ''),
                'precio_desde'    => !empty($_POST["fecha_precio_desde_{$fId}"]) ? (float) $_POST["fecha_precio_desde_{$fId}"] : null,
                'precio_hasta'    => !empty($_POST["fecha_precio_hasta_{$fId}"]) ? (float) $_POST["fecha_precio_hasta_{$fId}"] : null,
            ];
        }
        $catIds = $_POST['categorias'] ?? [];
        $catPrincipal = (int) ($_POST['categoria_principal'] ?? 0);
        $regPor = $data['registrado_por'] ?? $comercio['registrado_por'] ?? null;

        $this->db->transaction(function () use ($id, $data, $catIds, $catPrincipal, $fechaIds, $ofertas, $regPor) {
            Comercio::updateById($id, $data);
            Comercio::syncCategorias($id, $catIds, $catPrincipal);
            Comercio::syncFechas($id, $fechaIds, $ofertas);
            Comercio::recalcularCalidad($id);

            // Sincronizar estado del usuario comerciante asociado
            if (!empty($regPor)) {
                $activarUsuario = ($data['validado'] && $data['activo']) ? 1 : 0;
                AdminUsuario::updateById((int) $regPor, ['activo' => $activarUsuario]);
            }
        });

        $this->log('comercios', 'editar', 'comercio', $id, "Comercio editado: {$data['nombre']}");
        try { (new \App\Services\SitemapService())->generateAndSave(); } catch (\Throwable $e) {}
        $this->redirect('/admin/comercios', ['success' => 'Comercio actualizado correctamente']);
    }

    public function toggleActive(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->json(['ok' => false, 'error' => 'No encontrado'], 404);
            return;
        }

        $newState = $comercio['activo'] ? 0 : 1;
        Comercio::updateById($id, ['activo' => $newState]);

        // Sincronizar estado del usuario comerciante asociado
        if (!empty($comercio['registrado_por'])) {
            $activarUsuario = ($newState && $comercio['validado']) ? 1 : 0;
            AdminUsuario::updateById((int) $comercio['registrado_por'], ['activo' => $activarUsuario]);
        }

        $accion = $newState ? 'activar' : 'desactivar';
        $this->log('comercios', $accion, 'comercio', $id, "{$comercio['nombre']}");
        try { (new \App\Services\SitemapService())->generateAndSave(); } catch (\Throwable $e) {}
        $this->json(['ok' => true, 'activo' => $newState, 'csrf' => $_SESSION['csrf_token']]);
    }

    public function delete(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $db = \App\Core\Database::getInstance();

        // 1. Eliminar archivos físicos: logo, portada, galería
        if (!empty($comercio['logo'])) {
            FileManager::eliminarImagen('logos', $comercio['logo']);
        }
        if (!empty($comercio['portada'])) {
            FileManager::eliminarImagen('portadas', $comercio['portada']);
        }
        $fotos = Comercio::getFotos($id);
        foreach ($fotos as $foto) {
            FileManager::eliminarImagen('galeria', $foto['ruta']);
        }

        // 2. Eliminar TODAS las tablas hijas (explícito, sin depender de CASCADE)
        $db->delete('comercio_cambios_pendientes', 'comercio_id = ?', [$id]);
        $db->delete('comercio_horarios', 'comercio_id = ?', [$id]);
        $db->delete('comercio_fotos', 'comercio_id = ?', [$id]);
        $db->delete('comercio_categoria', 'comercio_id = ?', [$id]);
        $db->delete('comercio_fecha', 'comercio_id = ?', [$id]);

        // 3. Eliminar reseñas y sus reportes
        $resenas = $db->fetchAll("SELECT id FROM resenas WHERE comercio_id = ?", [$id]);
        if (!empty($resenas)) {
            $resenaIds = array_column($resenas, 'id');
            $placeholders = implode(',', array_fill(0, count($resenaIds), '?'));
            $db->execute("DELETE FROM resenas_reportes WHERE resena_id IN ({$placeholders})", $resenaIds);
            $db->delete('resenas', 'comercio_id = ?', [$id]);
        }

        // 4. Desvincular banners, visitas y shares (SET NULL)
        $db->execute("UPDATE banners SET comercio_id = NULL WHERE comercio_id = ?", [$id]);
        $db->execute("UPDATE visitas_log SET comercio_id = NULL WHERE comercio_id = ?", [$id]);
        $db->execute("UPDATE share_log SET comercio_id = NULL WHERE comercio_id = ?", [$id]);

        // 5. Eliminar usuario comerciante vinculado
        if (!empty($comercio['registrado_por'])) {
            $usuario = AdminUsuario::find((int) $comercio['registrado_por']);
            if ($usuario && $usuario['rol'] === 'comerciante') {
                AdminUsuario::deleteById((int) $comercio['registrado_por']);
            }
        }

        // 6. Eliminar el comercio
        Comercio::deleteById($id);

        $this->log('comercios', 'eliminar', 'comercio', $id, "Comercio eliminado: {$comercio['nombre']}");
        $this->redirect('/admin/comercios', ['success' => 'Comercio eliminado correctamente']);
    }

    public function gallery(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $fotos = Comercio::getFotos($id);

        $this->render('admin/comercios/galeria', [
            'title'    => 'Galería — ' . e($comercio['nombre']),
            'comercio' => $comercio,
            'fotos'    => $fotos,
        ]);
    }

    public function storePhoto(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
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

        Comercio::addFoto($id, [
            'ruta'       => $fileName,
            'ruta_thumb' => $fileName,
            'titulo'     => trim($_POST['titulo'] ?? ''),
        ]);

        $this->log('comercios', 'foto_agregar', 'comercio', $id, "Foto agregada a {$comercio['nombre']}");
        $this->redirect("/admin/comercios/{$id}/galeria", ['success' => 'Foto agregada']);
    }

    public function deletePhoto(string $id): void
    {
        $id = (int) $id;
        $fotoId = (int) ($_POST['foto_id'] ?? 0);

        $foto = Comercio::findFoto($fotoId, $id);

        if (!$foto) {
            $this->redirect("/admin/comercios/{$id}/galeria", ['error' => 'Foto no encontrada']);
            return;
        }

        FileManager::eliminarImagen('galeria', $foto['ruta']);
        Comercio::deleteFoto($fotoId);

        $this->log('comercios', 'foto_eliminar', 'comercio', $id, "Foto eliminada");
        $this->redirect("/admin/comercios/{$id}/galeria", ['success' => 'Foto eliminada']);
    }

    public function horarios(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $horariosPorDia = Comercio::getHorarios($id);

        $this->render('admin/comercios/horarios', [
            'title'     => 'Horarios — ' . e($comercio['nombre']),
            'comercio'  => $comercio,
            'horarios'  => $horariosPorDia,
        ]);
    }

    public function updateHorarios(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        // Construir array de horarios
        $horarios = [];
        for ($dia = 0; $dia <= 6; $dia++) {
            $cerrado = isset($_POST['cerrado'][$dia]) ? 1 : 0;
            $apertura = $_POST['hora_apertura'][$dia] ?? null;
            $cierre = $_POST['hora_cierre'][$dia] ?? null;

            $horarios[] = [
                'dia'           => $dia,
                'hora_apertura' => $cerrado ? null : ($apertura ?: null),
                'hora_cierre'   => $cerrado ? null : ($cierre ?: null),
                'cerrado'       => $cerrado,
            ];
        }
        Comercio::saveHorarios($id, $horarios);

        $this->log('comercios', 'horarios', 'comercio', $id, "Horarios actualizados: {$comercio['nombre']}");
        $this->redirect("/admin/comercios/{$id}/horarios", ['success' => 'Horarios actualizados']);
    }

    // ══════════════════════════════════════════════════════════
    // PRODUCTOS DEL COMERCIO
    // ══════════════════════════════════════════════════════════

    public function productos(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $plan = PlanConfig::findBySlug($comercio['plan'] ?? 'freemium');
        $maxProductos = $plan['max_productos'] ?? 5;

        $this->render('admin/comercios/productos', [
            'title'          => 'Productos — ' . e($comercio['nombre']),
            'comercio'       => $comercio,
            'productos'      => Producto::findByComercioId($id, false),
            'totalProductos' => Producto::countByComercioId($id),
            'maxProductos'   => $maxProductos,
            'plan'           => $plan,
        ]);
    }

    public function productoCrear(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $plan = PlanConfig::findBySlug($comercio['plan'] ?? 'freemium');
        $maxProductos = $plan['max_productos'] ?? 5;

        if ($maxProductos > 0 && Producto::countByComercioId($id) >= $maxProductos) {
            $this->redirect("/admin/comercios/{$id}/productos", ['error' => 'Este comercio alcanzó el límite de productos de su plan']);
            return;
        }

        $this->render('admin/comercios/producto-form', [
            'title'          => 'Agregar producto — ' . e($comercio['nombre']),
            'comercio'       => $comercio,
            'producto'       => null,
            'totalProductos' => Producto::countByComercioId($id),
            'maxProductos'   => $maxProductos,
            'plan'           => $plan,
        ]);
    }

    public function productoGuardar(string $id): void
    {
        $id = (int) $id;
        $comercio = Comercio::find($id);
        if (!$comercio) {
            $this->redirect('/admin/comercios', ['error' => 'Comercio no encontrado']);
            return;
        }

        $plan = PlanConfig::findBySlug($comercio['plan'] ?? 'freemium');
        $maxProductos = $plan['max_productos'] ?? 5;

        if ($maxProductos > 0 && Producto::countByComercioId($id) >= $maxProductos) {
            $this->redirect("/admin/comercios/{$id}/productos", ['error' => 'Límite de productos alcanzado']);
            return;
        }

        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $descripcion_detallada = trim($_POST['descripcion_detallada'] ?? '');
        $precio      = $_POST['precio'] ?? null;
        $activo      = isset($_POST['activo']) ? 1 : 0;
        $tipo        = $_POST['tipo'] ?? 'producto';
        $estado      = $_POST['estado'] ?? 'disponible';
        $stock       = $_POST['stock'] ?? null;
        $condicion   = $_POST['condicion'] ?? null;

        // Validar enums
        $tiposValidos = ['producto', 'servicio', 'inmueble'];
        $estadosValidos = ['disponible', 'vendido', 'reservado', 'agotado'];
        if (!in_array($tipo, $tiposValidos)) $tipo = 'producto';
        if (!in_array($estado, $estadosValidos)) $estado = 'disponible';
        if ($condicion !== null && !in_array($condicion, ['nuevo', 'usado', 'reacondicionado'])) $condicion = null;
        if ($condicion === '') $condicion = null;
        if ($stock !== null && $stock !== '') { $stock = (int) $stock; if ($stock < 0) $stock = 0; } else { $stock = null; }
        if (mb_strlen($descripcion_detallada) > 2000) $descripcion_detallada = mb_substr($descripcion_detallada, 0, 2000);

        if (empty($nombre) || mb_strlen($nombre) > 150) {
            $this->redirect("/admin/comercios/{$id}/productos/crear", ['error' => 'El nombre es obligatorio (máx 150 caracteres)']);
            return;
        }

        if ($precio !== null && $precio !== '') {
            $precio = (int) $precio;
            if ($precio < 0) $precio = 0;
        } else {
            $precio = null;
        }


        // Campos especificos por tipo
        $modalidad = $_POST['modalidad'] ?? null;
        $horario_atencion = trim($_POST['horario_atencion'] ?? '');
        $tipo_propiedad_val = $_POST['tipo_propiedad'] ?? null;
        $operacion = $_POST['operacion'] ?? null;
        $superficie_terreno = $_POST['superficie_terreno'] ?? null;
        $superficie_construida = $_POST['superficie_construida'] ?? null;
        $dormitorios = $_POST['dormitorios'] ?? null;
        $banos_val = $_POST['banos'] ?? null;
        $estacionamientos_val = $_POST['estacionamientos'] ?? null;
        $bodegas_val = $_POST['bodegas'] ?? null;
        $direccion_propiedad = trim($_POST['direccion_propiedad'] ?? '');
        $comuna_propiedad = trim($_POST['comuna_propiedad'] ?? '');
        $disponible_desde = $_POST['disponible_desde'] ?? null;
        $ano_construccion = $_POST['ano_construccion'] ?? null;
        $amoblado = isset($_POST['amoblado']) ? 1 : null;
        $acepta_mascotas = isset($_POST['acepta_mascotas']) ? 1 : null;
        $tiene_lenera = isset($_POST['tiene_lenera']) ? 1 : null;
        $tiene_areas_verdes = isset($_POST['tiene_areas_verdes']) ? 1 : null;
        $tiene_calefaccion = isset($_POST['tiene_calefaccion']) ? 1 : null;
        $tipo_calefaccion_val = $_POST['tipo_calefaccion'] ?? null;
        $es_rural = isset($_POST['es_rural']) ? (int)$_POST['es_rural'] : null;
        $agua_potable = isset($_POST['agua_potable']) ? 1 : null;
        $alcantarillado_val = isset($_POST['alcantarillado']) ? 1 : null;
        $luz_electrica = isset($_POST['luz_electrica']) ? 1 : null;
        $gastos_comunes = $_POST['gastos_comunes'] ?? null;

        // Validar enums
        if ($modalidad && !in_array($modalidad, ['presencial','domicilio','online','mixto'])) $modalidad = null;
        if ($horario_atencion && mb_strlen($horario_atencion) > 100) $horario_atencion = mb_substr($horario_atencion, 0, 100);
        $tpValid = ['casa','departamento','local_comercial','oficina','bodega','terreno','estacionamiento','habitacion','parcela','galpon','sitio'];
        if ($tipo_propiedad_val && !in_array($tipo_propiedad_val, $tpValid)) $tipo_propiedad_val = null;
        if ($operacion && !in_array($operacion, ['arriendo','venta','permuta','arriendo_con_opcion_compra','cesion_derechos'])) $operacion = null;
        if ($superficie_terreno !== null && $superficie_terreno !== '') $superficie_terreno = (float)$superficie_terreno; else $superficie_terreno = null;
        if ($superficie_construida !== null && $superficie_construida !== '') $superficie_construida = (float)$superficie_construida; else $superficie_construida = null;
        if ($dormitorios !== null && $dormitorios !== '') $dormitorios = (int)$dormitorios; else $dormitorios = null;
        if ($banos_val !== null && $banos_val !== '') $banos_val = (int)$banos_val; else $banos_val = null;
        if ($estacionamientos_val !== null && $estacionamientos_val !== '') $estacionamientos_val = (int)$estacionamientos_val; else $estacionamientos_val = null;
        if ($bodegas_val !== null && $bodegas_val !== '') $bodegas_val = (int)$bodegas_val; else $bodegas_val = null;
        if ($disponible_desde && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $disponible_desde)) $disponible_desde = null;
        if ($ano_construccion !== null && $ano_construccion !== '') { $ano_construccion = (int)$ano_construccion; if ($ano_construccion < 1900 || $ano_construccion > 2026) $ano_construccion = null; } else $ano_construccion = null;
        if ($gastos_comunes !== null && $gastos_comunes !== '') $gastos_comunes = (int)$gastos_comunes; else $gastos_comunes = null;
        if (!$tiene_calefaccion) $tipo_calefaccion_val = null;

        // Nullificar campos de otros tipos
        if ($tipo !== 'servicio') { $modalidad = null; $horario_atencion = ''; }
        if ($tipo !== 'inmueble') {
            $tipo_propiedad_val = null; $operacion = null; $superficie_terreno = null; $superficie_construida = null;
            $dormitorios = null; $banos_val = null; $estacionamientos_val = null; $bodegas_val = null;
            $direccion_propiedad = ''; $comuna_propiedad = ''; $disponible_desde = null; $ano_construccion = null;
            $amoblado = null; $acepta_mascotas = null; $tiene_lenera = null; $tiene_areas_verdes = null;
            $tiene_calefaccion = null; $tipo_calefaccion_val = null; $es_rural = null; $agua_potable = null;
            $alcantarillado_val = null; $luz_electrica = null; $gastos_comunes = null;
        }
        // operacion is set by the form for inmueble type

        $imagenNombre = null;
        $foto = $this->request->file('imagen');
        if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
            if ($foto['size'] > 2 * 1024 * 1024) {
                $this->redirect("/admin/comercios/{$id}/productos/crear", ['error' => 'La imagen no puede superar 2 MB']);
                return;
            }
            $imagenNombre = FileManager::subirImagen($foto, 'productos/' . $id, 800);
            if (!$imagenNombre) {
                $this->redirect("/admin/comercios/{$id}/productos/crear", ['error' => 'Error al subir imagen. Solo JPG, PNG o WebP']);
                return;
            }
        }

        $data = [
            'comercio_id' => $id,
            'tipo'        => $tipo,
            'nombre'      => $nombre,
            'descripcion' => $descripcion ?: null,
            'descripcion_detallada' => $descripcion_detallada ?: null,
            'precio'      => $precio,
            'stock'       => $stock,
            'condicion'   => $condicion,
            'activo'      => $activo,
            'estado'      => $estado,
            'orden'       => Producto::countByComercioId($id),
            'modalidad'             => $modalidad,
            'horario_atencion'      => $horario_atencion ?: null,
            'tipo_propiedad'        => $tipo_propiedad_val,
            'operacion'             => $operacion,
            'superficie_terreno'    => $superficie_terreno,
            'superficie_construida' => $superficie_construida,
            'dormitorios'           => $dormitorios,
            'banos'                 => $banos_val,
            'estacionamientos'      => $estacionamientos_val,
            'bodegas'               => $bodegas_val,
            'direccion_propiedad'   => $direccion_propiedad ?: null,
            'comuna_propiedad'      => $comuna_propiedad ?: null,
            'disponible_desde'      => $disponible_desde,
            'ano_construccion'      => $ano_construccion,
            'amoblado'              => $amoblado,
            'acepta_mascotas'       => $acepta_mascotas,
            'tiene_lenera'          => $tiene_lenera,
            'tiene_areas_verdes'    => $tiene_areas_verdes,
            'tiene_calefaccion'     => $tiene_calefaccion,
            'tipo_calefaccion'      => $tipo_calefaccion_val,
            'es_rural'              => $es_rural,
            'agua_potable'          => $agua_potable,
            'alcantarillado'        => $alcantarillado_val,
            'luz_electrica'         => $luz_electrica,
            'gastos_comunes'        => $gastos_comunes,
        ];
        if ($imagenNombre) {
            $data['imagen'] = $imagenNombre;
        }

        // Imagen 2
        $foto2 = $this->request->file('imagen2');
        if ($foto2 && $foto2['error'] === UPLOAD_ERR_OK) {
            if ($foto2['size'] <= 2 * 1024 * 1024) {
                $img2 = FileManager::subirImagen($foto2, 'productos/' . $id, 800);
                if ($img2) $data['imagen2'] = $img2;
            }
        }

        Producto::create($data);

        $this->log('productos', 'crear', 'comercio', $id, "Producto '{$nombre}' creado para {$comercio['nombre']}");
        $this->redirect("/admin/comercios/{$id}/productos", ['success' => "Producto '{$nombre}' creado"]);
    }

    public function productoEditar(string $id, string $pid): void
    {
        $id = (int) $id;
        $pid = (int) $pid;
        $comercio = Comercio::find($id);
        $producto = Producto::findById($pid);

        if (!$comercio || !$producto || $producto['comercio_id'] != $id) {
            $this->redirect('/admin/comercios', ['error' => 'Producto no encontrado']);
            return;
        }

        $plan = PlanConfig::findBySlug($comercio['plan'] ?? 'freemium');

        $this->render('admin/comercios/producto-form', [
            'title'          => 'Editar producto — ' . e($producto['nombre']),
            'comercio'       => $comercio,
            'producto'       => $producto,
            'totalProductos' => Producto::countByComercioId($id),
            'maxProductos'   => $plan['max_productos'] ?? 5,
            'plan'           => $plan,
        ]);
    }

    public function productoActualizar(string $id, string $pid): void
    {
        $id = (int) $id;
        $pid = (int) $pid;
        $comercio = Comercio::find($id);
        $producto = Producto::findById($pid);

        if (!$comercio || !$producto || $producto['comercio_id'] != $id) {
            $this->redirect('/admin/comercios', ['error' => 'Producto no encontrado']);
            return;
        }

        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $descripcion_detallada = trim($_POST['descripcion_detallada'] ?? '');
        $precio      = $_POST['precio'] ?? null;
        $activo      = isset($_POST['activo']) ? 1 : 0;
        $tipo        = $_POST['tipo'] ?? 'producto';
        $estado      = $_POST['estado'] ?? 'disponible';
        $stock       = $_POST['stock'] ?? null;
        $condicion   = $_POST['condicion'] ?? null;

        // Validar enums
        $tiposValidos = ['producto', 'servicio', 'inmueble'];
        $estadosValidos = ['disponible', 'vendido', 'reservado', 'agotado'];
        if (!in_array($tipo, $tiposValidos)) $tipo = 'producto';
        if (!in_array($estado, $estadosValidos)) $estado = 'disponible';
        if ($condicion !== null && !in_array($condicion, ['nuevo', 'usado', 'reacondicionado'])) $condicion = null;
        if ($condicion === '') $condicion = null;
        if ($stock !== null && $stock !== '') { $stock = (int) $stock; if ($stock < 0) $stock = 0; } else { $stock = null; }
        if (mb_strlen($descripcion_detallada) > 2000) $descripcion_detallada = mb_substr($descripcion_detallada, 0, 2000);

        if (empty($nombre) || mb_strlen($nombre) > 150) {
            $this->redirect("/admin/comercios/{$id}/productos/editar/{$pid}", ['error' => 'El nombre es obligatorio (máx 150 caracteres)']);
            return;
        }

        if ($precio !== null && $precio !== '') {
            $precio = (int) $precio;
            if ($precio < 0) $precio = 0;
        } else {
            $precio = null;
        }

        $data = [
            'tipo'        => $tipo,
            'nombre'      => $nombre,
            'descripcion' => $descripcion ?: null,
            'descripcion_detallada' => $descripcion_detallada ?: null,
            'precio'      => $precio,
            'stock'       => $stock,
            'condicion'   => $condicion,
            'activo'      => $activo,
            'estado'      => $estado,
            'modalidad'             => $modalidad,
            'horario_atencion'      => $horario_atencion ?: null,
            'tipo_propiedad'        => $tipo_propiedad_val,
            'operacion'             => $operacion,
            'superficie_terreno'    => $superficie_terreno,
            'superficie_construida' => $superficie_construida,
            'dormitorios'           => $dormitorios,
            'banos'                 => $banos_val,
            'estacionamientos'      => $estacionamientos_val,
            'bodegas'               => $bodegas_val,
            'direccion_propiedad'   => $direccion_propiedad ?: null,
            'comuna_propiedad'      => $comuna_propiedad ?: null,
            'disponible_desde'      => $disponible_desde,
            'ano_construccion'      => $ano_construccion,
            'amoblado'              => $amoblado,
            'acepta_mascotas'       => $acepta_mascotas,
            'tiene_lenera'          => $tiene_lenera,
            'tiene_areas_verdes'    => $tiene_areas_verdes,
            'tiene_calefaccion'     => $tiene_calefaccion,
            'tipo_calefaccion'      => $tipo_calefaccion_val,
            'es_rural'              => $es_rural,
            'agua_potable'          => $agua_potable,
            'alcantarillado'        => $alcantarillado_val,
            'luz_electrica'         => $luz_electrica,
            'gastos_comunes'        => $gastos_comunes,
        ];

        // Eliminar imagen2 si se marco checkbox
        if (!empty($_POST['eliminar_imagen2']) && !empty($producto['imagen2'])) {
            FileManager::eliminarImagen('productos/' . $id, $producto['imagen2']);
            $data['imagen2'] = null;
        }

        // Imagen 2
        $foto2 = $this->request->file('imagen2');
        if ($foto2 && $foto2['error'] === UPLOAD_ERR_OK) {
            if ($foto2['size'] <= 2 * 1024 * 1024) {
                $img2 = FileManager::subirImagen($foto2, 'productos/' . $id, 800);
                if ($img2) {
                    if (!empty($producto['imagen2'])) FileManager::eliminarImagen('productos/' . $id, $producto['imagen2']);
                    $data['imagen2'] = $img2;
                }
            }
        }

        // Eliminar imagen si se marcó checkbox
        if (!empty($_POST['eliminar_imagen']) && !empty($producto['imagen'])) {
            FileManager::eliminarImagen('productos/' . $id, $producto['imagen']);
            $data['imagen'] = null;
        }

        $foto = $this->request->file('imagen');
        if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
            if ($foto['size'] > 2 * 1024 * 1024) {
                $this->redirect("/admin/comercios/{$id}/productos/editar/{$pid}", ['error' => 'La imagen no puede superar 2 MB']);
                return;
            }
            $imagenNombre = FileManager::subirImagen($foto, 'productos/' . $id, 800);
            if ($imagenNombre) {
                if (!empty($producto['imagen'])) {
                    FileManager::eliminarImagen('productos/' . $id, $producto['imagen']);
                }
                $data['imagen'] = $imagenNombre;
            }
        }

        Producto::update($pid, $data);

        $this->log('productos', 'editar', 'comercio', $id, "Producto '{$nombre}' actualizado en {$comercio['nombre']}");
        $this->redirect("/admin/comercios/{$id}/productos", ['success' => "Producto '{$nombre}' actualizado"]);
    }

    public function productoEliminar(string $id, string $pid): void
    {
        $id = (int) $id;
        $pid = (int) $pid;
        $comercio = Comercio::find($id);
        $producto = Producto::findById($pid);

        if (!$comercio || !$producto || $producto['comercio_id'] != $id) {
            $this->redirect("/admin/comercios/{$id}/productos", ['error' => 'Producto no encontrado']);
            return;
        }

        if (!empty($producto['imagen'])) {
            FileManager::eliminarImagen('productos/' . $id, $producto['imagen']);
        }
        if (!empty($producto['imagen2'])) {
            FileManager::eliminarImagen('productos/' . $id, $producto['imagen2']);
        }

        Producto::delete($pid);

        $this->log('productos', 'eliminar', 'comercio', $id, "Producto '{$producto['nombre']}' eliminado de {$comercio['nombre']}");
        $this->redirect("/admin/comercios/{$id}/productos", ['success' => "Producto '{$producto['nombre']}' eliminado"]);
    }

    /**
     * Eliminar foto de producto (AJAX)
     */
    public function productoFotoEliminar(string $id, string $pid, string $fid): void
    {
        $id = (int) $id; $pid = (int) $pid; $fid = (int) $fid;
        $producto = Producto::findById($pid);
        $foto = ProductoFoto::findById($fid);
        if (!$producto || !$foto || $producto['comercio_id'] != $id || $foto['producto_id'] != $pid) {
            http_response_code(404); echo json_encode(['error' => 'No encontrado']); exit;
        }
        FileManager::eliminarImagen('productos/' . $id, $foto['imagen']);
        ProductoFoto::delete($fid);
        if ($foto['es_principal']) {
            $next = ProductoFoto::getPrincipal($pid);
            if ($next) ProductoFoto::setPrincipal($pid, $next['id']);
        }
        $principal = ProductoFoto::getPrincipal($pid);
        Producto::update($pid, ['imagen' => $principal ? $principal['imagen'] : null]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    /**
     * Marcar foto como principal (AJAX)
     */
    public function productoFotoPrincipal(string $id, string $pid, string $fid): void
    {
        $id = (int) $id; $pid = (int) $pid; $fid = (int) $fid;
        $producto = Producto::findById($pid);
        $foto = ProductoFoto::findById($fid);
        if (!$producto || !$foto || $producto['comercio_id'] != $id || $foto['producto_id'] != $pid) {
            http_response_code(404); echo json_encode(['error' => 'No encontrado']); exit;
        }
        ProductoFoto::setPrincipal($pid, $fid);
        Producto::update($pid, ['imagen' => $foto['imagen']]);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }

    public function productoToggle(string $id, string $pid): void
    {
        $id = (int) $id;
        $pid = (int) $pid;
        $producto = Producto::findById($pid);

        if (!$producto || $producto['comercio_id'] != $id) {
            $this->json(['success' => false, 'error' => 'Producto no encontrado']);
            return;
        }

        $nuevoEstado = Producto::toggleActivo($pid);

        $this->log('productos', 'toggle', 'comercio', $id, "Producto '{$producto['nombre']}' " . ($nuevoEstado ? 'activado' : 'desactivado'));
        $this->json(['success' => true, 'activo' => $nuevoEstado]);
    }

}
