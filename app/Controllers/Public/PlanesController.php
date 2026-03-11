<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Services\Seo;

class PlanesController extends Controller
{
    public function index(): void
    {
        $this->render('public/planes', [
            'title'       => 'Planes · ' . SITE_NAME,
            'description' => 'Conoce los planes disponibles para tu comercio en ' . SITE_NAME,
            'og_image'    => asset('img/og/og-regalos-purranque.jpg'),
            'keywords'    => 'planes comercio purranque, publicidad purranque, directorio comercial purranque',
            'breadcrumbs' => $breadcrumbs = [
                ['label' => 'Inicio', 'url' => '/'],
                ['label' => 'Planes'],
            ],
            'schemas'     => [Seo::schemaBreadcrumbs($breadcrumbs)],
        ]);
    }
}
