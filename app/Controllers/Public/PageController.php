<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Services\VisitTracker;

/**
 * Paginas estaticas legales
 */
class PageController extends Controller
{
    /**
     * GET /terminos
     */
    public function terminos(): void
    {
        VisitTracker::track(null, '/terminos', 'pagina');

        $this->render('public/terminos', [
            'title'       => 'Términos y Condiciones — ' . SITE_NAME,
            'description' => 'Términos y condiciones de uso de ' . SITE_NAME,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/'],
                ['label' => 'Términos y Condiciones'],
            ],
        ]);
    }

    /**
     * GET /privacidad
     */
    public function privacidad(): void
    {
        VisitTracker::track(null, '/privacidad', 'pagina');

        $this->render('public/privacidad', [
            'title'       => 'Política de Privacidad — ' . SITE_NAME,
            'description' => 'Política de privacidad y protección de datos de ' . SITE_NAME,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/'],
                ['label' => 'Política de Privacidad'],
            ],
        ]);
    }

    /**
     * GET /cookies
     */
    public function cookies(): void
    {
        VisitTracker::track(null, '/cookies', 'pagina');

        $this->render('public/cookies', [
            'title'       => 'Política de Cookies — ' . SITE_NAME,
            'description' => 'Política de cookies de ' . SITE_NAME,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/'],
                ['label' => 'Política de Cookies'],
            ],
        ]);
    }
}
