<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Share;

/**
 * Estadísticas de compartidos en redes sociales
 */
class ShareAdminController extends Controller
{
    /**
     * GET /admin/share
     */
    public function index(): void
    {
        $desde = $this->request->get('desde', date('Y-m-d', strtotime('-30 days')));
        $hasta = $this->request->get('hasta', date('Y-m-d'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $desde = date('Y-m-d', strtotime('-30 days'));
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $hasta = date('Y-m-d');
        }

        $total      = Share::getTotal($desde, $hasta);
        $porRed     = Share::getPorRed($desde, $hasta);
        $topContent = Share::getTopCompartido($desde, $hasta, 15);
        $topCom     = Share::getTopComercios($desde, $hasta, 15);
        $porDia     = Share::getPorDia($desde, $hasta);

        $this->render('admin/share/index', [
            'title'      => 'Compartidos — ' . SITE_NAME,
            'total'      => $total,
            'porRed'     => $porRed,
            'topContent' => $topContent,
            'topCom'     => $topCom,
            'porDia'     => $porDia,
            'desde'      => $desde,
            'hasta'      => $hasta,
        ]);
    }
}
