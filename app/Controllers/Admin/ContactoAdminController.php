<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\MensajeContacto;

class ContactoAdminController extends Controller
{
    /**
     * GET /admin/contacto — Listado paginado de mensajes de contacto
     */
    public function index(): void
    {
        $page   = max(1, (int) $this->request->get('page', 1));
        $estado = $this->request->get('estado', '');
        $limit  = ADMIN_PER_PAGE;

        $where  = '1=1';
        $params = [];

        if ($estado === 'no_leido') {
            $where .= ' AND leido = 0';
        } elseif ($estado === 'leido') {
            $where .= ' AND leido = 1';
        } elseif ($estado === 'respondido') {
            $where .= ' AND respondido = 1';
        }

        $total      = MensajeContacto::countAll($where, $params);
        $totalPages = max(1, (int) ceil($total / $limit));
        $page       = min($page, $totalPages);
        $offset     = ($page - 1) * $limit;

        $mensajes = MensajeContacto::getAll($limit, $offset, $where, $params);

        $stats = [
            'total'     => MensajeContacto::countAll(),
            'no_leidos' => MensajeContacto::countNoLeidos(),
        ];

        $this->render('admin/contacto/index', [
            'title'       => 'Mensajes de Contacto — ' . SITE_NAME,
            'mensajes'    => $mensajes,
            'stats'       => $stats,
            'filters'     => ['estado' => $estado],
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
            'baseUrl'     => '/admin/contacto',
            'queryParams' => array_filter(['estado' => $estado]),
        ]);
    }
}
