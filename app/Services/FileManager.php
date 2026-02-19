<?php
namespace App\Services;

/**
 * Servicio de gestión de archivos
 * Subida, redimensión, thumbnails y eliminación segura de imágenes
 */
class FileManager
{
    /**
     * Subir imagen con validación y redimensión
     *
     * @param array  $file     Elemento de $_FILES
     * @param string $carpeta  Subcarpeta dentro de UPLOAD_PATH (ej: 'logos', 'portadas')
     * @param int    $maxWidth Ancho máximo en pixels
     * @return string|false    Nombre del archivo guardado o false si falla
     */
    public static function subirImagen(array $file, string $carpeta, int $maxWidth = 1200): string|false
    {
        // Validar error de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Validar tamaño
        if ($file['size'] > UPLOAD_MAX_SIZE) {
            return false;
        }

        // Validar tipo MIME real
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, UPLOAD_ALLOWED_TYPES, true)) {
            return false;
        }

        // Extensión según MIME
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];
        $ext = $extensions[$mime] ?? 'jpg';

        // Generar nombre único
        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $slug = substr(preg_replace('/[^a-z0-9\-]/', '', strtolower($baseName)), 0, 30);
        $fileName = $slug . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

        // Crear directorio si no existe
        $destDir = UPLOAD_PATH . '/' . $carpeta;
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        // Prevenir path traversal
        $realDir = realpath($destDir);
        $realUpload = realpath(UPLOAD_PATH);
        if ($realDir === false || !str_starts_with($realDir, $realUpload)) {
            return false;
        }

        $destPath = $destDir . '/' . $fileName;

        // Mover archivo temporal
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return false;
        }

        // Redimensionar si excede maxWidth (solo jpg/png/webp)
        if (in_array($ext, ['jpg', 'png', 'webp'])) {
            self::redimensionar($destPath, $maxWidth);
        }

        // Generar thumbnail
        self::generarThumbnail($destPath, $carpeta, $fileName);

        return $fileName;
    }

    /**
     * Redimensionar imagen si excede el ancho máximo
     */
    private static function redimensionar(string $path, int $maxWidth): void
    {
        $info = @getimagesize($path);
        if (!$info || $info[0] <= $maxWidth) {
            return;
        }

        $srcW = $info[0];
        $srcH = $info[1];
        $ratio = $srcW / $srcH;
        $newW = $maxWidth;
        $newH = (int) round($maxWidth / $ratio);

        $src = self::crearDesdeArchivo($path, $info['mime']);
        if (!$src) return;

        $dst = imagecreatetruecolor($newW, $newH);
        self::preservarTransparencia($dst, $info['mime']);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

        self::guardarImagen($dst, $path, $info['mime']);
        imagedestroy($src);
        imagedestroy($dst);
    }

    /**
     * Generar thumbnail de una imagen
     */
    public static function generarThumbnail(string $srcPath, string $carpeta, string $fileName, int $width = 300): string
    {
        $thumbDir = UPLOAD_PATH . '/' . $carpeta . '/thumbs';
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $thumbPath = $thumbDir . '/' . $fileName;

        $info = @getimagesize($srcPath);
        if (!$info) {
            // Si no se puede procesar, copiar el original
            copy($srcPath, $thumbPath);
            return $fileName;
        }

        $srcW = $info[0];
        $srcH = $info[1];

        if ($srcW <= $width) {
            copy($srcPath, $thumbPath);
            return $fileName;
        }

        $ratio = $srcW / $srcH;
        $newW = $width;
        $newH = (int) round($width / $ratio);

        $src = self::crearDesdeArchivo($srcPath, $info['mime']);
        if (!$src) {
            copy($srcPath, $thumbPath);
            return $fileName;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        self::preservarTransparencia($dst, $info['mime']);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

        self::guardarImagen($dst, $thumbPath, $info['mime']);
        imagedestroy($src);
        imagedestroy($dst);

        return $fileName;
    }

    /**
     * Eliminar imagen y su thumbnail
     */
    public static function eliminarImagen(string $carpeta, string $fileName): bool
    {
        if (empty($fileName)) {
            return false;
        }

        // Prevenir path traversal
        if (str_contains($fileName, '..') || str_contains($fileName, '/') || str_contains($fileName, '\\')) {
            return false;
        }

        $basePath = UPLOAD_PATH . '/' . $carpeta;
        $filePath = $basePath . '/' . $fileName;
        $thumbPath = $basePath . '/thumbs/' . $fileName;

        // Verificar que está dentro de UPLOAD_PATH
        $realBase = realpath(UPLOAD_PATH);
        if ($realBase === false) {
            return false;
        }

        $deleted = false;

        if (file_exists($filePath)) {
            $realFile = realpath($filePath);
            if ($realFile && str_starts_with($realFile, $realBase)) {
                unlink($realFile);
                $deleted = true;
            }
        }

        if (file_exists($thumbPath)) {
            $realThumb = realpath($thumbPath);
            if ($realThumb && str_starts_with($realThumb, $realBase)) {
                unlink($realThumb);
            }
        }

        return $deleted;
    }

    /**
     * Crear recurso GD desde archivo
     */
    private static function crearDesdeArchivo(string $path, string $mime): \GdImage|false
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif'  => @imagecreatefromgif($path),
            default      => false,
        };
    }

    /**
     * Guardar imagen según formato
     */
    private static function guardarImagen(\GdImage $img, string $path, string $mime): void
    {
        match ($mime) {
            'image/jpeg' => imagejpeg($img, $path, 85),
            'image/png'  => imagepng($img, $path, 8),
            'image/webp' => imagewebp($img, $path, 85),
            'image/gif'  => imagegif($img, $path),
            default      => imagejpeg($img, $path, 85),
        };
    }

    /**
     * Preservar transparencia para PNG y WebP
     */
    private static function preservarTransparencia(\GdImage $img, string $mime): void
    {
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($img, false);
            imagesavealpha($img, true);
            $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $transparent);
        }
    }
}
