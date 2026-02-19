<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

/**
 * Explorador de archivos del sistema
 * Permite navegar, ver, subir, renombrar y eliminar archivos del proyecto
 * SEGURIDAD: Todas las rutas se validan con realpath() dentro de BASE_PATH
 */
class FileExplorerController extends Controller
{
    /** Archivos protegidos que no se pueden eliminar ni renombrar */
    private const PROTECTED_FILES = [
        'config/database.php',
        'index.php',
        '.htaccess',
        'CLAUDE.md',
    ];

    /** Extensiones de archivos de texto visualizables */
    private const TEXT_EXTENSIONS = [
        'php', 'html', 'css', 'js', 'sql', 'md', 'txt',
        'json', 'xml', 'htaccess', 'log', 'ini', 'yml',
    ];

    /** Extensiones de imagen visualizables */
    private const IMAGE_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
    ];

    // ───────────────────────────────────────────────
    // Acciones públicas
    // ───────────────────────────────────────────────

    /**
     * GET /admin/mantenimiento/archivos
     * Listar contenido de un directorio
     */
    public function browse(): void
    {
        $path = trim($this->request->get('path', ''), '/');
        $fullPath = BASE_PATH . ($path !== '' ? '/' . $path : '');

        $realPath = $this->validatePath($fullPath);
        if ($realPath === false || !is_dir($realPath)) {
            $this->redirect('/admin/mantenimiento/archivos', [
                'error' => 'Directorio no válido o no accesible',
            ]);
            return;
        }

        // Obtener ruta relativa normalizada
        $realBase = realpath(BASE_PATH);
        $currentPath = $realPath === $realBase
            ? ''
            : str_replace('\\', '/', substr($realPath, strlen($realBase) + 1));

        $items = $this->listDirectory($realPath, $currentPath);
        $breadcrumbs = $this->buildBreadcrumbs($currentPath);

        $this->render('admin/mantenimiento/archivos', [
            'title'       => 'Explorador de Archivos — ' . SITE_NAME,
            'tab'         => 'archivos',
            'items'       => $items,
            'currentPath' => $currentPath,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * GET /admin/mantenimiento/archivos/ver
     * Ver contenido de un archivo (texto o imagen)
     */
    public function viewFile(): void
    {
        $path = trim($this->request->get('path', ''), '/');
        if ($path === '') {
            $this->json(['error' => 'Ruta no especificada'], 400);
            return;
        }

        $fullPath = BASE_PATH . '/' . $path;
        $realPath = $this->validatePath($fullPath);

        if ($realPath === false || !is_file($realPath)) {
            $this->json(['error' => 'Archivo no válido o no accesible'], 404);
            return;
        }

        $ext  = $this->getExtension($realPath);
        $name = basename($realPath);

        if ($this->isTextFile($ext)) {
            // Limitar lectura a 1 MB
            $maxSize = 1024 * 1024;
            $size = filesize($realPath);

            if ($size > $maxSize) {
                $content = file_get_contents($realPath, false, null, 0, $maxSize);
                $content .= "\n\n--- Archivo truncado (se muestran solo el primer 1 MB de {$this->formatSize($size)}) ---";
            } else {
                $content = file_get_contents($realPath);
            }

            $this->json([
                'type'    => 'text',
                'name'    => $name,
                'content' => $content,
                'ext'     => $ext,
                'size'    => filesize($realPath),
            ]);
            return;
        }

        if ($this->isImageFile($ext)) {
            // Devolver ruta relativa para visualización
            $realBase = realpath(BASE_PATH);
            $relativePath = str_replace('\\', '/', substr($realPath, strlen($realBase) + 1));

            $this->json([
                'type' => 'image',
                'name' => $name,
                'path' => $relativePath,
                'ext'  => $ext,
                'size' => filesize($realPath),
            ]);
            return;
        }

        $this->json([
            'error' => 'Tipo de archivo no soportado para vista previa',
            'name'  => $name,
            'ext'   => $ext,
        ], 400);
    }

    /**
     * GET /admin/mantenimiento/archivos/descargar
     * Descargar un archivo
     */
    public function downloadFile(): void
    {
        $path = trim($this->request->get('path', ''), '/');
        if ($path === '') {
            $this->redirect('/admin/mantenimiento/archivos', [
                'error' => 'Ruta no especificada',
            ]);
            return;
        }

        $fullPath = BASE_PATH . '/' . $path;
        $realPath = $this->validatePath($fullPath);

        if ($realPath === false || !is_file($realPath)) {
            $this->redirect('/admin/mantenimiento/archivos', [
                'error' => 'Archivo no válido o no accesible',
            ]);
            return;
        }

        $this->log('mantenimiento', 'archivo_descargar', 'archivo', 0, "Descarga: {$path}");
        Response::download($realPath, basename($realPath));
    }

    /**
     * POST /admin/mantenimiento/archivos/subir
     * Subir un archivo al directorio indicado
     */
    public function uploadFile(): void
    {
        $directory = trim($this->request->post('directorio', ''), '/');
        $targetDir = BASE_PATH . ($directory !== '' ? '/' . $directory : '');

        $realDir = $this->validatePath($targetDir);
        if ($realDir === false || !is_dir($realDir)) {
            $this->back(['error' => 'Directorio destino no válido']);
            return;
        }

        // Verificar que se subió un archivo
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->back(['error' => 'Error al subir el archivo']);
            return;
        }

        $file = $_FILES['archivo'];

        // Validar tamaño máximo: 10 MB
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $this->back(['error' => 'El archivo excede el tamaño máximo permitido (10 MB)']);
            return;
        }

        // Limpiar nombre de archivo
        $filename = basename($file['name']);
        $destination = $realDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->back(['error' => 'No se pudo mover el archivo subido']);
            return;
        }

        $relativePath = ($directory !== '' ? $directory . '/' : '') . $filename;
        $this->log('mantenimiento', 'archivo_subir', 'archivo', 0, "Archivo subido: {$relativePath}");

        $this->back(['success' => "Archivo '{$filename}' subido correctamente"]);
    }

    /**
     * POST /admin/mantenimiento/archivos/crear-carpeta
     * Crear una nueva carpeta
     */
    public function createFolder(): void
    {
        $directory = trim($this->request->post('directorio', ''), '/');
        $name      = trim($this->request->post('nombre', ''));

        // Validar nombre: solo alfanuméricos, guiones, puntos y guiones bajos
        if ($name === '' || !preg_match('/^[a-zA-Z0-9\-._]+$/', $name)) {
            $this->back(['error' => 'Nombre de carpeta no válido. Solo se permiten letras, números, guiones, puntos y guiones bajos']);
            return;
        }

        $parentDir = BASE_PATH . ($directory !== '' ? '/' . $directory : '');
        $realParent = $this->validatePath($parentDir);

        if ($realParent === false || !is_dir($realParent)) {
            $this->back(['error' => 'Directorio padre no válido']);
            return;
        }

        $newDir = $realParent . '/' . $name;

        if (file_exists($newDir)) {
            $this->back(['error' => "Ya existe un archivo o carpeta con el nombre '{$name}'"]);
            return;
        }

        if (!mkdir($newDir, 0755)) {
            $this->back(['error' => 'No se pudo crear la carpeta']);
            return;
        }

        $relativePath = ($directory !== '' ? $directory . '/' : '') . $name;
        $this->log('mantenimiento', 'carpeta_crear', 'archivo', 0, "Carpeta creada: {$relativePath}");

        $this->back(['success' => "Carpeta '{$name}' creada correctamente"]);
    }

    /**
     * POST /admin/mantenimiento/archivos/renombrar
     * Renombrar un archivo o carpeta
     */
    public function renameFile(): void
    {
        $currentPath = trim($this->request->post('ruta', ''), '/');
        $newName     = trim($this->request->post('nombre_nuevo', ''));

        if ($currentPath === '' || $newName === '') {
            $this->back(['error' => 'Datos incompletos para renombrar']);
            return;
        }

        // Validar nombre nuevo
        if (!preg_match('/^[a-zA-Z0-9\-._]+$/', $newName)) {
            $this->back(['error' => 'Nombre no válido. Solo se permiten letras, números, guiones, puntos y guiones bajos']);
            return;
        }

        // Verificar que no sea un archivo protegido
        if ($this->isProtected($currentPath)) {
            $this->back(['error' => 'Este archivo está protegido y no se puede renombrar']);
            return;
        }

        $fullPath = BASE_PATH . '/' . $currentPath;
        $realPath = $this->validatePath($fullPath);

        if ($realPath === false || !file_exists($realPath)) {
            $this->back(['error' => 'Archivo o carpeta no encontrado']);
            return;
        }

        // Construir nueva ruta
        $parentDir = dirname($realPath);
        $newPath   = $parentDir . '/' . $newName;

        // Validar que la nueva ruta siga dentro de BASE_PATH
        $realParent = realpath($parentDir);
        $realBase   = realpath(BASE_PATH);
        if (!$realParent || !str_starts_with($realParent, $realBase)) {
            $this->back(['error' => 'Ruta destino no válida']);
            return;
        }

        if (file_exists($newPath)) {
            $this->back(['error' => "Ya existe un archivo o carpeta con el nombre '{$newName}'"]);
            return;
        }

        if (!rename($realPath, $newPath)) {
            $this->back(['error' => 'No se pudo renombrar el archivo']);
            return;
        }

        $this->log('mantenimiento', 'archivo_renombrar', 'archivo', 0, "Renombrado: {$currentPath} -> {$newName}");

        $this->back(['success' => "Renombrado correctamente a '{$newName}'"]);
    }

    /**
     * POST /admin/mantenimiento/archivos/eliminar
     * Eliminar un archivo o carpeta vacía
     */
    public function deleteFile(): void
    {
        $path = trim($this->request->post('ruta', ''), '/');

        if ($path === '') {
            $this->back(['error' => 'Ruta no especificada']);
            return;
        }

        // Verificar que no sea un archivo protegido
        if ($this->isProtected($path)) {
            $this->back(['error' => 'Este archivo está protegido y no se puede eliminar']);
            return;
        }

        $fullPath = BASE_PATH . '/' . $path;
        $realPath = $this->validatePath($fullPath);

        if ($realPath === false || !file_exists($realPath)) {
            $this->back(['error' => 'Archivo o carpeta no encontrado']);
            return;
        }

        if (is_dir($realPath)) {
            // Solo eliminar directorios vacíos
            $contents = @scandir($realPath);
            if ($contents && count($contents) > 2) {
                $this->back(['error' => 'La carpeta no está vacía. Elimine su contenido primero']);
                return;
            }

            if (!rmdir($realPath)) {
                $this->back(['error' => 'No se pudo eliminar la carpeta']);
                return;
            }
        } else {
            if (!unlink($realPath)) {
                $this->back(['error' => 'No se pudo eliminar el archivo']);
                return;
            }
        }

        $this->log('mantenimiento', 'archivo_eliminar', 'archivo', 0, "Eliminado: {$path}");

        $this->back(['success' => 'Eliminado correctamente']);
    }

    // ───────────────────────────────────────────────
    // Métodos auxiliares privados
    // ───────────────────────────────────────────────

    /**
     * Verificar si una ruta relativa corresponde a un archivo protegido
     */
    private function isProtected(string $relativePath): bool
    {
        $normalized = str_replace('\\', '/', trim($relativePath, '/'));

        foreach (self::PROTECTED_FILES as $protected) {
            $protectedNorm = str_replace('\\', '/', $protected);
            if ($normalized === $protectedNorm || str_ends_with($normalized, '/' . $protectedNorm)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validar que una ruta esté dentro de BASE_PATH y no acceda a zonas prohibidas
     * @return string|false Ruta real validada o false si no es válida
     */
    private function validatePath(string $path): string|false
    {
        $realPath = realpath($path);
        $realBase = realpath(BASE_PATH);

        if ($realPath === false || $realBase === false) {
            return false;
        }

        // Debe estar dentro de BASE_PATH
        if (!str_starts_with($realPath, $realBase)) {
            return false;
        }

        // Bloquear acceso a .git/ y .env
        $relative = str_replace('\\', '/', substr($realPath, strlen($realBase)));
        $relative = ltrim($relative, '/');

        if (str_starts_with($relative, '.git/') || $relative === '.git') {
            return false;
        }

        if ($relative === '.env' || str_starts_with($relative, '.env.')) {
            return false;
        }

        return $realPath;
    }

    /**
     * Obtener extensión de un archivo (sin punto, en minúsculas)
     */
    private function getExtension(string $file): string
    {
        $basename = basename($file);

        // Caso especial para archivos sin extensión pero con nombre especial
        if ($basename === '.htaccess') {
            return 'htaccess';
        }

        $ext = pathinfo($basename, PATHINFO_EXTENSION);
        return strtolower($ext);
    }

    /**
     * Verificar si la extensión corresponde a un archivo de texto
     */
    private function isTextFile(string $ext): bool
    {
        return in_array($ext, self::TEXT_EXTENSIONS, true);
    }

    /**
     * Verificar si la extensión corresponde a una imagen
     */
    private function isImageFile(string $ext): bool
    {
        return in_array($ext, self::IMAGE_EXTENSIONS, true);
    }

    /**
     * Construir array de breadcrumbs para la navegación
     * @return array<int, array{name: string, path: string}>
     */
    private function buildBreadcrumbs(string $path): array
    {
        $breadcrumbs = [
            ['name' => 'Raíz', 'path' => ''],
        ];

        if ($path === '') {
            return $breadcrumbs;
        }

        $parts = explode('/', $path);
        $accumulated = '';

        foreach ($parts as $part) {
            $accumulated .= ($accumulated !== '' ? '/' : '') . $part;
            $breadcrumbs[] = [
                'name' => $part,
                'path' => $accumulated,
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Listar contenido de un directorio: carpetas primero, luego archivos
     */
    private function listDirectory(string $realPath, string $currentPath): array
    {
        $contents = @scandir($realPath);
        if (!$contents) {
            return [];
        }

        $folders = [];
        $files   = [];

        foreach ($contents as $item) {
            // Omitir . y ..
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullItemPath = $realPath . '/' . $item;
            $relativePath = ($currentPath !== '' ? $currentPath . '/' : '') . $item;

            // Ocultar .git y .env del listado
            if ($item === '.git' || $item === '.env' || str_starts_with($item, '.env.')) {
                continue;
            }

            $isDir    = is_dir($fullItemPath);
            $ext      = $isDir ? '' : $this->getExtension($fullItemPath);
            $size     = $isDir ? 0 : @filesize($fullItemPath);
            $modified = @filemtime($fullItemPath);

            $entry = [
                'name'     => $item,
                'type'     => $isDir ? 'dir' : 'file',
                'size'     => $size ?: 0,
                'modified' => $modified ? date('Y-m-d H:i:s', $modified) : '',
                'ext'      => $ext,
                'path'     => $relativePath,
            ];

            if ($isDir) {
                $folders[] = $entry;
            } else {
                $files[] = $entry;
            }
        }

        // Ordenar alfabéticamente dentro de cada grupo
        usort($folders, fn($a, $b) => strcasecmp($a['name'], $b['name']));
        usort($files, fn($a, $b) => strcasecmp($a['name'], $b['name']));

        return array_merge($folders, $files);
    }

    /**
     * Formatear tamaño en bytes a unidad legible
     */
    private function formatSize(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
