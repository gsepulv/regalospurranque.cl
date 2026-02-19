<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\Resena;
use App\Services\VisitTracker;

/**
 * Pagina de "mis resenas" por email/cookie
 */
class ReviewController extends Controller
{
    /**
     * GET /mis-resenas
     */
    public function misResenas(): void
    {
        $email = $this->request->get('email', '');
        $resenas = [];

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $resenas = Resena::getByEmail($email, 20, 0);
        }

        VisitTracker::track(null, '/mis-resenas', 'pagina');

        $this->render('public/resenas', [
            'title'       => 'Mis Reseñas — ' . SITE_NAME,
            'description' => 'Consulta tus reseñas en ' . SITE_NAME,
            'noindex'     => true,
            'email'       => $email,
            'resenas'     => $resenas,
            'breadcrumbs' => [
                ['label' => 'Inicio', 'url' => '/'],
                ['label' => 'Mis Reseñas'],
            ],
        ]);
    }
}
