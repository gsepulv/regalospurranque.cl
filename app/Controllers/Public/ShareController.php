<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Comercio;
use App\Models\Noticia;

/**
 * Pagina de compartir con preview optimizado para redes sociales
 */
class ShareController extends Controller
{
    /**
     * GET /compartir/{tipo}/{slug}
     */
    public function show(string $tipo, string $slug): void
    {
        $data = null;
        $ogImage = null;
        $shareUrl = '';
        $shareTitle = '';
        $shareDescription = '';

        switch ($tipo) {
            case 'comercio':
                $data = Comercio::getBySlug($slug);
                if (!$data) {
                    Response::error(404);
                    return;
                }
                $shareUrl = url('/comercio/' . $data['slug']);
                $shareTitle = $data['nombre'] . ' — ' . SITE_NAME;
                $shareDescription = truncate($data['descripcion'] ?? 'Visita este comercio en ' . SITE_NAME, 160);
                $ogImage = !empty($data['portada']) ? asset('img/portadas/' . $data['portada']) : null;
                break;

            case 'noticia':
                $data = Noticia::getBySlug($slug);
                if (!$data) {
                    Response::error(404);
                    return;
                }
                $shareUrl = url('/noticia/' . $data['slug']);
                $shareTitle = $data['titulo'] . ' — ' . SITE_NAME;
                $shareDescription = $data['extracto'] ?? truncate($data['contenido'] ?? '', 160);
                $ogImage = !empty($data['imagen']) ? asset('img/noticias/' . $data['imagen']) : null;
                break;

            default:
                Response::error(404);
                return;
        }

        $this->render('public/share', [
            'title'       => $shareTitle,
            'description' => $shareDescription,
            'og_image'    => $ogImage,
            'og_type'     => $tipo === 'noticia' ? 'article' : 'business.business',
            'tipo'        => $tipo,
            'slug'        => $slug,
            'data'        => $data,
            'shareUrl'    => $shareUrl,
            'shareTitle'  => $shareTitle,
            'shareDescription' => $shareDescription,
        ]);
    }
}
