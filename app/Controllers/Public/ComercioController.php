<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Comercio;
use App\Models\Categoria;
use App\Models\Resena;
use App\Models\Banner;
use App\Models\Producto;
use App\Services\VisitTracker;
use App\Services\Seo;

/**
 * Detalle de un comercio
 */
class ComercioController extends Controller
{
    /**
     * GET /comercios
     */
    public function index(): void
    {
        $filters = [];
        $page       = max(1, (int) $this->request->get('page', 1));
        $total      = Comercio::countSearch($filters);
        $totalPages = max(1, (int) ceil($total / PER_PAGE));
        $offset     = ($page - 1) * PER_PAGE;

        $comercios  = Comercio::search($filters, PER_PAGE, $offset);
        $categorias = Categoria::getWithComerciosCount();
        $banners    = Banner::getByTipo('sidebar');

        VisitTracker::track(null, '/comercios', 'comercios');

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Comercios'],
        ];

        $this->render('public/comercios', [
            'title'       => 'Comercios en Purranque · Directorio Comercial · ' . SITE_NAME,
            'description' => 'Directorio completo de comercios y servicios en Purranque, Chile. Encuentra tiendas, restaurantes y más con contacto y ubicación.',
            'og_image'    => asset('img/og/comercio-default.jpg'),
            'keywords'    => 'comercios purranque, directorio comercial purranque, tiendas purranque, restaurantes purranque',
            'comercios'   => $comercios,
            'categorias'  => $categorias,
            'banners'     => $banners,
            'total'       => $total,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'baseUrl'     => '/comercios',
            'breadcrumbs' => $breadcrumbs,
            'schemas'     => [
                Seo::schemaItemList($comercios, 'Comercios en Purranque'),
                Seo::schemaBreadcrumbs($breadcrumbs),
            ],
        ]);
    }

    /**
     * GET /comercio/{slug}
     */
    public function show(string $slug): void
    {
        $comercio = Comercio::getBySlug($slug);

        if (!$comercio) {
            Response::error(404);
            return;
        }

        $inactivo = !$comercio['activo'];
        $id = (int) $comercio['id'];

        // Datos complementarios
        $fotos       = Comercio::getFotos($id);
        $horarios    = Comercio::getHorarios($id);
        $resenas     = Resena::getByComercio($id, 'aprobada', 10, 0);
        $distribucion = Resena::getDistribucion($id);
        $relacionados = Comercio::getRelacionados($id, 4);
        $banners     = Banner::getByTipo('sidebar');
        $productos   = Producto::findByComercioId($id);

        // Tracking (no contar visitas de fichas inactivas)
        if (!$inactivo) {
            Comercio::incrementVisitas($id);
            if (!empty($productos)) Producto::incrementVistas($id);
            VisitTracker::track($id, "/comercio/{$slug}", 'comercio');
        }

        // SEO
        $catPrincipal = '';
        if (!empty($comercio['categorias'])) {
            foreach ($comercio['categorias'] as $cat) {
                $catPrincipal = $cat['nombre'];
                if (!empty($cat['es_principal'])) break;
            }
        }
        $titleBase = $comercio['nombre'] . ($catPrincipal ? ' · ' . $catPrincipal : '') . ' en Purranque';
        if (mb_strlen($titleBase) > 55) {
            $titleBase = mb_substr($titleBase, 0, 55);
            $lastSpace = mb_strrpos($titleBase, ' ');
            if ($lastSpace) $titleBase = mb_substr($titleBase, 0, $lastSpace);
        }
        $title = $comercio['seo_titulo'] ?: $titleBase . ' · ' . SITE_NAME;
        $description = $comercio['seo_descripcion'] ?: mb_substr($comercio['nombre'] . ': ' . ($comercio['descripcion'] ?? ''), 0, 120) . '. ' . ($catPrincipal ? $catPrincipal . ' en Purranque.' : 'Comercio en Purranque.');
        $ogImage = $comercio['portada'] ? asset('img/portadas/' . $comercio['portada']) : null;

        $breadcrumbs = [
            ['label' => 'Inicio', 'url' => '/'],
            ['label' => 'Comercios', 'url' => '/comercios'],
            ['label' => $comercio['nombre']],
        ];

        $this->render('public/comercio', [
            'title'         => $title,
            'description'   => $description,
            'keywords'      => $comercio['seo_keywords'] ?? '',
            'og_image'      => $ogImage,
            'og_type'       => 'business.business',
            'noindex'       => $inactivo,
            'comercio'      => $comercio,
            'inactivo'      => $inactivo,
            'fotos'         => $fotos,
            'horarios'      => $horarios,
            'resenas'       => $resenas,
            'distribucion'  => $distribucion,
            'relacionados'  => $relacionados,
            'banners'       => $banners,
            'productos'     => $productos,
            'breadcrumbs'   => $breadcrumbs,
            'schemas'       => $inactivo ? [] : [
                Seo::schemaLocalBusiness($comercio, $horarios),
                Seo::schemaBreadcrumbs($breadcrumbs),
            ],
        ]);
    }

    /**
     * Share page for individual product (OG meta tags)
     */
    public function productoShare(string $id): void
    {
        $id = (int) $id;
        $producto = Producto::findByIdWithComercio($id);
        if (!$producto || !$producto['activo']) {
            header('Location: ' . url('/'));
            exit;
        }
        $comercio = [
            'id' => $producto['comercio_id'],
            'nombre' => $producto['comercio_nombre'],
            'slug' => $producto['comercio_slug'],
            'logo' => $producto['comercio_logo'],
        ];
        include BASE_PATH . '/views/public/producto-share.php';
    }

    /**
     * Generate OG image for product sharing (1200x630)
     */
    public function productoOgImage(string $id): void
    {
        $id = (int) $id;
        $producto = Producto::findByIdWithComercio($id);
        if (!$producto) {
            http_response_code(404);
            exit;
        }

        $cacheDir = UPLOAD_PATH . '/og-cache';
        if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
        $cachePath = $cacheDir . '/producto-' . $id . '.jpg';

        // Serve from cache if fresh
        if (file_exists($cachePath)) {
            $cacheTime = filemtime($cachePath);
            $updatedAt = strtotime($producto['updated_at'] ?? '2000-01-01');
            if ($cacheTime > $updatedAt) {
                header('Content-Type: image/jpeg');
                header('Cache-Control: public, max-age=86400');
                readfile($cachePath);
                exit;
            }
        }

        $W = 1200; $H = 630;
        $barH = 100;
        $barY = $H - $barH;
        $fontB = '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf';
        $fontR = '/usr/share/fonts/dejavu/DejaVuSans.ttf';

        // Create canvas with white background
        $img = imagecreatetruecolor($W, $H);
        imageantialias($img, true);
        $white  = imagecolorallocate($img, 255, 255, 255);
        $bgSoft = imagecolorallocate($img, 250, 250, 250);
        $barBg  = imagecolorallocate($img, 255, 255, 255);
        $black  = imagecolorallocate($img, 40, 40, 40);
        $gray   = imagecolorallocate($img, 130, 130, 130);
        $green  = imagecolorallocate($img, 76, 175, 80);
        $greenDark = imagecolorallocate($img, 56, 142, 60);
        $shadow = imagecolorallocate($img, 230, 230, 230);
        imagefill($img, 0, 0, $bgSoft);

        // Load product image
        $prodImgPath = UPLOAD_PATH . '/productos/' . $producto['comercio_id'] . '/' . ($producto['imagen'] ?? '');
        $prodImg = null;
        if (!empty($producto['imagen']) && file_exists($prodImgPath)) {
            $ext = strtolower(pathinfo($prodImgPath, PATHINFO_EXTENSION));
            if ($ext === 'jpg' || $ext === 'jpeg') $prodImg = @imagecreatefromjpeg($prodImgPath);
            elseif ($ext === 'png') $prodImg = @imagecreatefrompng($prodImgPath);
            elseif ($ext === 'webp') $prodImg = @imagecreatefromwebp($prodImgPath);
        }

        if ($prodImg) {
            $pw = imagesx($prodImg);
            $ph = imagesy($prodImg);

            // Product image area: padded, never upscale
            $areaW = $W - 80;       // 40px padding each side
            $areaH = $barY - 40;    // 20px padding top+bottom
            $ratio = min($areaW / $pw, $areaH / $ph, 1.0); // never upscale
            $newW = (int)($pw * $ratio);
            $newH = (int)($ph * $ratio);

            // Create a high-quality resized version
            $resized = imagecreatetruecolor($newW, $newH);
            imagealphablending($resized, true);
            imagesavealpha($resized, true);
            // Fill with soft bg
            $resBg = imagecolorallocate($resized, 250, 250, 250);
            imagefill($resized, 0, 0, $resBg);
            imagecopyresampled($resized, $prodImg, 0, 0, 0, 0, $newW, $newH, $pw, $ph);

            // Center on canvas
            $dx = (int)(($W - $newW) / 2);
            $dy = (int)(($barY - $newH) / 2);

            // Subtle shadow behind image (offset rectangle)
            imagefilledrectangle($img, $dx + 4, $dy + 4, $dx + $newW + 4, $dy + $newH + 4, $shadow);

            // White card behind image
            imagefilledrectangle($img, $dx - 2, $dy - 2, $dx + $newW + 2, $dy + $newH + 2, $white);

            // Paste resized image
            imagecopy($img, $resized, $dx, $dy, 0, 0, $newW, $newH);
            imagedestroy($resized);
            imagedestroy($prodImg);
        } else {
            // No product image - show initial letter centered
            $initial = mb_strtoupper(mb_substr($producto['nombre'], 0, 1));
            $lightGray = imagecolorallocate($img, 220, 220, 220);
            // Circle placeholder
            $cx = $W / 2; $cy = ($barY) / 2;
            imagefilledellipse($img, (int)$cx, (int)$cy, 200, 200, $lightGray);
            $bbox = imagettfbbox(60, 0, $fontB, $initial);
            $tw = $bbox[2] - $bbox[0]; $th = $bbox[1] - $bbox[7];
            imagettftext($img, 60, 0, (int)($cx - $tw/2), (int)($cy + $th/2), $gray, $fontB, $initial);
        }

        // Bottom bar - clean white with top border
        imagefilledrectangle($img, 0, $barY, $W, $H, $barBg);
        imagefilledrectangle($img, 0, $barY, $W, $barY + 2, $green); // green accent line

        // Logo in bottom bar
        $logoPath = UPLOAD_PATH . '/logos/' . ($producto['comercio_logo'] ?? '');
        $logoSize = 54;
        $logoX = 36;
        $logoY = $barY + (int)(($barH - $logoSize) / 2);

        if (!empty($producto['comercio_logo']) && file_exists($logoPath)) {
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $logoImg = null;
            if ($ext === 'jpg' || $ext === 'jpeg') $logoImg = @imagecreatefromjpeg($logoPath);
            elseif ($ext === 'png') $logoImg = @imagecreatefrompng($logoPath);
            elseif ($ext === 'webp') $logoImg = @imagecreatefromwebp($logoPath);

            if ($logoImg) {
                $lw = imagesx($logoImg); $lh = imagesy($logoImg);
                // Downscale only, high quality
                $logoRes = imagecreatetruecolor($logoSize, $logoSize);
                imagealphablending($logoRes, true);
                $logoWhite = imagecolorallocate($logoRes, 255, 255, 255);
                imagefill($logoRes, 0, 0, $logoWhite);
                imagecopyresampled($logoRes, $logoImg, 0, 0, 0, 0, $logoSize, $logoSize, $lw, $lh);
                imagedestroy($logoImg);
                imagecopy($img, $logoRes, $logoX, $logoY, 0, 0, $logoSize, $logoSize);
                imagedestroy($logoRes);
            }
        } else {
            // Placeholder circle
            $cx2 = $logoX + $logoSize/2; $cy2 = $logoY + $logoSize/2;
            imagefilledellipse($img, (int)$cx2, (int)$cy2, $logoSize, $logoSize, $green);
            $ini = mb_strtoupper(mb_substr($producto['comercio_nombre'], 0, 1));
            $bb = imagettfbbox(18, 0, $fontB, $ini);
            $itw = $bb[2] - $bb[0]; $ith = $bb[1] - $bb[7];
            imagettftext($img, 18, 0, (int)($cx2 - $itw/2), (int)($cy2 + $ith/2), $white, $fontB, $ini);
        }

        // Text next to logo
        $textX = $logoX + $logoSize + 18;
        imagettftext($img, 16, 0, $textX, $barY + 45, $black, $fontB, $producto['comercio_nombre']);
        imagettftext($img, 11, 0, $textX, $barY + 68, $gray, $fontR, 'regalospurranque.cl');

        // Price pill (top-right, rounded feel via two rects)
        if ($producto['precio']) {
            $tipo = $producto['tipo'] ?? 'producto';
            $priceTxt = '$ ' . number_format($producto['precio'], 0, '.', '.');
            if ($tipo === 'arriendo') $priceTxt .= ' /mes';
            $bbox3 = imagettfbbox(20, 0, $fontB, $priceTxt);
            $ptw = $bbox3[2] - $bbox3[0] + 36;
            $pth = 42;
            $px = $W - $ptw - 24;
            $py = 18;
            // Green pill
            imagefilledrectangle($img, $px, $py, $px + $ptw, $py + $pth, $green);
            imagettftext($img, 20, 0, $px + 18, $py + 30, $white, $fontB, $priceTxt);
        }

        // Product name (bottom-right area of bar)
        $nameMaxW = $W - $textX - 40;
        $prodName = $producto['nombre'];
        // Truncate if too long
        while (mb_strlen($prodName) > 3) {
            $bb2 = imagettfbbox(13, 0, $fontR, $prodName);
            if (($bb2[2] - $bb2[0]) <= $nameMaxW) break;
            $prodName = mb_substr($prodName, 0, -1);
        }
        imagettftext($img, 13, 0, $W - 36 - (imagettfbbox(13, 0, $fontR, $prodName)[2] - imagettfbbox(13, 0, $fontR, $prodName)[0]), $barY + 55, $gray, $fontR, $prodName);

        // Save as high-quality JPEG
        imagejpeg($img, $cachePath, 95);
        imagedestroy($img);

        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=86400');
        readfile($cachePath);
        exit;
    }
}
