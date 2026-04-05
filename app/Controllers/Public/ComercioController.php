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

        // Check cache - regenerate if product was updated after cache
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

        // Canvas 1200x630
        $w = 1200; $h = 630;
        $img = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($img, 255, 255, 255);
        $grayBg = imagecolorallocate($img, 245, 245, 245);
        $black = imagecolorallocate($img, 51, 51, 51);
        $gray = imagecolorallocate($img, 153, 153, 153);
        $green = imagecolorallocate($img, 76, 175, 80);
        imagefill($img, 0, 0, $white);

        // Bottom bar (120px)
        $barH = 120;
        $barY = $h - $barH;
        imagefilledrectangle($img, 0, $barY, $w, $h, $grayBg);
        // Thin green line separator
        imagefilledrectangle($img, 0, $barY, $w, $barY + 3, $green);

        $fontBold = '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf';
        $fontRegular = '/usr/share/fonts/dejavu/DejaVuSans.ttf';

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
            $pw = imagesx($prodImg); $ph = imagesy($prodImg);
            // Max area for product image: 1200 x 510 (above bar)
            $maxW = $w; $maxH = $barY;
            $ratio = min($maxW / $pw, $maxH / $ph);
            $newW = (int)($pw * $ratio);
            $newH = (int)($ph * $ratio);
            $dx = (int)(($w - $newW) / 2);
            $dy = (int)(($maxH - $newH) / 2);
            imagecopyresampled($img, $prodImg, $dx, $dy, 0, 0, $newW, $newH, $pw, $ph);
            imagedestroy($prodImg);
        } else {
            // No image - show product initial large
            $initial = mb_substr($producto['nombre'], 0, 1);
            $bbox = imagettfbbox(80, 0, $fontBold, $initial);
            $tw = $bbox[2] - $bbox[0]; $th = $bbox[1] - $bbox[7];
            imagettftext($img, 80, 0, (int)(($w - $tw)/2), (int)(($barY - $th)/2 + $th), $gray, $fontBold, $initial);
        }

        // Bottom bar: logo + text
        $logoPath = UPLOAD_PATH . '/logos/' . ($producto['comercio_logo'] ?? '');
        $logoSize = 60;
        $logoX = 30; $logoY = $barY + (int)(($barH - $logoSize) / 2);

        if (!empty($producto['comercio_logo']) && file_exists($logoPath)) {
            $ext = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));
            $logoImg = null;
            if ($ext === 'jpg' || $ext === 'jpeg') $logoImg = @imagecreatefromjpeg($logoPath);
            elseif ($ext === 'png') $logoImg = @imagecreatefrompng($logoPath);
            elseif ($ext === 'webp') $logoImg = @imagecreatefromwebp($logoPath);

            if ($logoImg) {
                $lw = imagesx($logoImg); $lh = imagesy($logoImg);
                $logoResized = imagecreatetruecolor($logoSize, $logoSize);
                imagecopyresampled($logoResized, $logoImg, 0, 0, 0, 0, $logoSize, $logoSize, $lw, $lh);
                imagedestroy($logoImg);
                imagecopy($img, $logoResized, $logoX, $logoY, 0, 0, $logoSize, $logoSize);
                imagedestroy($logoResized);
            }
        } else {
            // Placeholder circle with initial
            $cx = $logoX + $logoSize/2; $cy = $logoY + $logoSize/2;
            imagefilledellipse($img, (int)$cx, (int)$cy, $logoSize, $logoSize, $gray);
            $ini = mb_substr($producto['comercio_nombre'], 0, 1);
            $bbox2 = imagettfbbox(20, 0, $fontBold, $ini);
            $tw2 = $bbox2[2] - $bbox2[0]; $th2 = $bbox2[1] - $bbox2[7];
            imagettftext($img, 20, 0, (int)($cx - $tw2/2), (int)($cy + $th2/2), $white, $fontBold, $ini);
        }

        // Commerce name
        $textX = $logoX + $logoSize + 16;
        imagettftext($img, 18, 0, $textX, $barY + 50, $black, $fontBold, $producto['comercio_nombre']);
        imagettftext($img, 12, 0, $textX, $barY + 75, $gray, $fontRegular, 'regalospurranque.cl');

        // Price badge (top-right)
        if ($producto['precio']) {
            $priceTxt = '$ ' . number_format($producto['precio'], 0, '', '.');
            $bbox3 = imagettfbbox(22, 0, $fontBold, $priceTxt);
            $ptw = $bbox3[2] - $bbox3[0] + 30;
            $px = $w - $ptw - 20; $py = 20;
            imagefilledrectangle($img, $px, $py, $px + $ptw, $py + 45, $green);
            imagettftext($img, 22, 0, $px + 15, $py + 33, $white, $fontBold, $priceTxt);
        }

        // Save and serve
        imagejpeg($img, $cachePath, 90);
        imagedestroy($img);

        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=86400');
        readfile($cachePath);
        exit;
    }
}
