<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\FileManager;

/**
 * CRUD de noticias
 */
class NoticiaAdminController extends Controller
{
    public function index(): void
    {
        $page     = max(1, (int) $this->request->get('page', 1));
        $perPage  = ADMIN_PER_PAGE;
        $buscar   = trim($this->request->get('q', ''));
        $estado   = $this->request->get('estado', '');

        $where  = '1=1';
        $params = [];

        if ($buscar !== '') {
            $where .= ' AND n.titulo LIKE ?';
            $params[] = "%{$buscar}%";
        }
        if ($estado === '1') {
            $where .= ' AND n.activo = 1';
        } elseif ($estado === '0') {
            $where .= ' AND n.activo = 0';
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as total FROM noticias n WHERE {$where}",
            $params
        )['total'] ?? 0;

        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $noticias = $this->db->fetchAll(
            "SELECT n.* FROM noticias n
             WHERE {$where}
             ORDER BY n.fecha_publicacion DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $this->render('admin/noticias/index', [
            'title'       => 'Noticias — ' . SITE_NAME,
            'noticias'    => $noticias,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
            'filters'     => ['q' => $buscar, 'estado' => $estado],
        ]);
    }

    public function create(): void
    {
        $categorias = $this->db->fetchAll("SELECT id, nombre, icono FROM categorias WHERE activo = 1 ORDER BY orden");
        $fechas = $this->db->fetchAll("SELECT id, nombre, icono, tipo FROM fechas_especiales WHERE activo = 1 ORDER BY tipo, nombre");

        $this->render('admin/noticias/form', [
            'title'      => 'Nueva Noticia — ' . SITE_NAME,
            'categorias' => $categorias,
            'fechas'     => $fechas,
        ]);
    }

    public function store(): void
    {
        $v = $this->validate($_POST, [
            'titulo' => 'required|string|min:5|max:200',
            'slug'   => 'required|slug|unique:noticias,slug',
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'titulo'            => trim($_POST['titulo']),
            'slug'              => trim($_POST['slug']),
            'contenido'         => $_POST['contenido'] ?? '',
            'extracto'          => trim($_POST['extracto'] ?? ''),
            'autor'             => trim($_POST['autor'] ?? ''),
            'fecha_publicacion' => !empty($_POST['fecha_publicacion']) ? $_POST['fecha_publicacion'] : date('Y-m-d H:i:s'),
            'activo'            => isset($_POST['activo']) ? 1 : 0,
            'destacada'         => isset($_POST['destacada']) ? 1 : 0,
            'seo_titulo'        => trim($_POST['seo_titulo'] ?? ''),
            'seo_descripcion'   => trim($_POST['seo_descripcion'] ?? ''),
            'seo_keywords'      => trim($_POST['seo_keywords'] ?? ''),
            'seo_noindex'       => isset($_POST['seo_noindex']) ? 1 : 0,
        ];

        $imagen = $this->request->file('imagen');
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            $data['imagen'] = FileManager::subirImagen($imagen, 'noticias', 1200);
        }

        $ogImagen = $this->request->file('seo_imagen_og');
        if ($ogImagen && $ogImagen['error'] === UPLOAD_ERR_OK) {
            $data['seo_imagen_og'] = FileManager::subirImagen($ogImagen, 'og', 1200);
        }

        $id = $this->db->insert('noticias', $data);

        // Categorías
        foreach ($_POST['categorias'] ?? [] as $catId) {
            $this->db->insert('noticia_categoria', [
                'noticia_id'   => $id,
                'categoria_id' => (int) $catId,
            ]);
        }

        // Fechas
        foreach ($_POST['fechas'] ?? [] as $fechaId) {
            $this->db->insert('noticia_fecha', [
                'noticia_id' => $id,
                'fecha_id'   => (int) $fechaId,
            ]);
        }

        $this->log('noticias', 'crear', 'noticia', $id, "Noticia creada: {$data['titulo']}");
        $this->redirect('/admin/noticias', ['success' => 'Noticia creada correctamente']);
    }

    public function edit(string $id): void
    {
        $id = (int) $id;
        $noticia = $this->db->fetch("SELECT * FROM noticias WHERE id = ?", [$id]);
        if (!$noticia) {
            $this->redirect('/admin/noticias', ['error' => 'Noticia no encontrada']);
            return;
        }

        $categorias = $this->db->fetchAll("SELECT id, nombre, icono FROM categorias WHERE activo = 1 ORDER BY orden");
        $fechas = $this->db->fetchAll("SELECT id, nombre, icono, tipo FROM fechas_especiales WHERE activo = 1 ORDER BY tipo, nombre");

        $catIds = array_column(
            $this->db->fetchAll("SELECT categoria_id FROM noticia_categoria WHERE noticia_id = ?", [$id]),
            'categoria_id'
        );
        $fechaIds = array_column(
            $this->db->fetchAll("SELECT fecha_id FROM noticia_fecha WHERE noticia_id = ?", [$id]),
            'fecha_id'
        );

        $this->render('admin/noticias/form', [
            'title'      => 'Editar Noticia — ' . SITE_NAME,
            'noticia'    => $noticia,
            'categorias' => $categorias,
            'fechas'     => $fechas,
            'catIds'     => $catIds,
            'fechaIds'   => $fechaIds,
        ]);
    }

    public function update(string $id): void
    {
        $id = (int) $id;
        $noticia = $this->db->fetch("SELECT * FROM noticias WHERE id = ?", [$id]);
        if (!$noticia) {
            $this->redirect('/admin/noticias', ['error' => 'Noticia no encontrada']);
            return;
        }

        $v = $this->validate($_POST, [
            'titulo' => 'required|string|min:5|max:200',
            'slug'   => "required|slug|unique:noticias,slug,{$id}",
        ]);

        if ($v->fails()) {
            $this->back(['errors' => $v->errors(), 'old' => $_POST]);
            return;
        }

        $data = [
            'titulo'            => trim($_POST['titulo']),
            'slug'              => trim($_POST['slug']),
            'contenido'         => $_POST['contenido'] ?? '',
            'extracto'          => trim($_POST['extracto'] ?? ''),
            'autor'             => trim($_POST['autor'] ?? ''),
            'fecha_publicacion' => !empty($_POST['fecha_publicacion']) ? $_POST['fecha_publicacion'] : $noticia['fecha_publicacion'],
            'activo'            => isset($_POST['activo']) ? 1 : 0,
            'destacada'         => isset($_POST['destacada']) ? 1 : 0,
            'seo_titulo'        => trim($_POST['seo_titulo'] ?? ''),
            'seo_descripcion'   => trim($_POST['seo_descripcion'] ?? ''),
            'seo_keywords'      => trim($_POST['seo_keywords'] ?? ''),
            'seo_noindex'       => isset($_POST['seo_noindex']) ? 1 : 0,
        ];

        $imagen = $this->request->file('imagen');
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            if (!empty($noticia['imagen'])) {
                FileManager::eliminarImagen('noticias', $noticia['imagen']);
            }
            $data['imagen'] = FileManager::subirImagen($imagen, 'noticias', 1200);
        }

        $ogImagen = $this->request->file('seo_imagen_og');
        if ($ogImagen && $ogImagen['error'] === UPLOAD_ERR_OK) {
            if (!empty($noticia['seo_imagen_og'])) {
                FileManager::eliminarImagen('og', $noticia['seo_imagen_og']);
            }
            $data['seo_imagen_og'] = FileManager::subirImagen($ogImagen, 'og', 1200);
        }

        $this->db->update('noticias', $data, 'id = ?', [$id]);

        // Sync categorías
        $this->db->delete('noticia_categoria', 'noticia_id = ?', [$id]);
        foreach ($_POST['categorias'] ?? [] as $catId) {
            $this->db->insert('noticia_categoria', [
                'noticia_id'   => $id,
                'categoria_id' => (int) $catId,
            ]);
        }

        // Sync fechas
        $this->db->delete('noticia_fecha', 'noticia_id = ?', [$id]);
        foreach ($_POST['fechas'] ?? [] as $fechaId) {
            $this->db->insert('noticia_fecha', [
                'noticia_id' => $id,
                'fecha_id'   => (int) $fechaId,
            ]);
        }

        $this->log('noticias', 'editar', 'noticia', $id, "Noticia editada: {$data['titulo']}");
        $this->redirect('/admin/noticias', ['success' => 'Noticia actualizada correctamente']);
    }

    public function toggleActive(string $id): void
    {
        $id = (int) $id;
        $noticia = $this->db->fetch("SELECT id, titulo, activo FROM noticias WHERE id = ?", [$id]);
        if (!$noticia) {
            $this->json(['ok' => false, 'error' => 'No encontrada'], 404);
            return;
        }

        $newState = $noticia['activo'] ? 0 : 1;
        $this->db->update('noticias', ['activo' => $newState], 'id = ?', [$id]);

        $this->log('noticias', $newState ? 'activar' : 'desactivar', 'noticia', $id, $noticia['titulo']);
        $this->json(['ok' => true, 'activo' => $newState, 'csrf' => $_SESSION['csrf_token']]);
    }

    /**
     * Upload de imagen desde TinyMCE
     */
    public function uploadImage(): void
    {
        $file = $this->request->file('file');
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'No se recibio imagen'], 400);
            return;
        }

        // Validar MIME
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedMimes)) {
            $this->json(['error' => 'Tipo de archivo no permitido'], 400);
            return;
        }

        // Validar tamaño
        $maxMb = (int) \App\Services\RedesSociales::get('tinymce_max_image_mb', '3');
        if ($file['size'] > $maxMb * 1024 * 1024) {
            $this->json(['error' => "La imagen excede {$maxMb}MB"], 400);
            return;
        }

        // Redimensionar si excede ancho máximo
        $maxWidth = (int) \App\Services\RedesSociales::get('tinymce_max_image_width', '1200');
        $imgInfo = getimagesize($file['tmp_name']);
        if ($imgInfo && $imgInfo[0] > $maxWidth) {
            $this->resizeImage($file['tmp_name'], $mime, $maxWidth);
        }

        // Generar nombre único
        $ext = match($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
            default      => 'jpg',
        };
        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        $destDir = BASE_PATH . '/assets/img/noticias/contenido';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $destPath = $destDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->json(['error' => 'Error al guardar imagen'], 500);
            return;
        }

        $this->json(['location' => asset('img/noticias/contenido/' . $filename)]);
    }

    /**
     * Redimensionar imagen manteniendo proporción
     */
    private function resizeImage(string $path, string $mime, int $maxWidth): void
    {
        $src = match($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/gif'  => imagecreatefromgif($path),
            default      => null,
        };

        if (!$src) return;

        $origW = imagesx($src);
        $origH = imagesy($src);
        $ratio = $origH / $origW;
        $newW = $maxWidth;
        $newH = (int) ($maxWidth * $ratio);

        $dst = imagecreatetruecolor($newW, $newH);

        // Preserve transparency for PNG/WebP
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        match($mime) {
            'image/jpeg' => imagejpeg($dst, $path, 85),
            'image/png'  => imagepng($dst, $path, 8),
            'image/webp' => imagewebp($dst, $path, 85),
            'image/gif'  => imagegif($dst, $path),
            default      => null,
        };

        imagedestroy($src);
        imagedestroy($dst);
    }

    public function delete(string $id): void
    {
        $id = (int) $id;
        $noticia = $this->db->fetch("SELECT * FROM noticias WHERE id = ?", [$id]);
        if (!$noticia) {
            $this->redirect('/admin/noticias', ['error' => 'Noticia no encontrada']);
            return;
        }

        if (!empty($noticia['imagen'])) {
            FileManager::eliminarImagen('noticias', $noticia['imagen']);
        }
        if (!empty($noticia['seo_imagen_og'])) {
            FileManager::eliminarImagen('og', $noticia['seo_imagen_og']);
        }

        $this->db->delete('noticias', 'id = ?', [$id]);
        $this->log('noticias', 'eliminar', 'noticia', $id, "Noticia eliminada: {$noticia['titulo']}");
        $this->redirect('/admin/noticias', ['success' => 'Noticia eliminada correctamente']);
    }
}
