<?php
namespace App\Controllers\Public;

use App\Core\Controller;

class PlanesController extends Controller
{
    public function index(): void
    {
        $this->render('public/planes', [
            'title'       => 'Planes — ' . SITE_NAME,
            'description' => 'Conoce los planes disponibles para tu comercio en ' . SITE_NAME,
        ]);
    }
}
