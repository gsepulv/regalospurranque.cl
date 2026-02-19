<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

/**
 * Controlador principal de mantenimiento
 * Redirige al panel de backups por defecto
 */
class MantenimientoController extends Controller
{
    public function index(): void
    {
        $this->redirect('/admin/mantenimiento/backups');
    }
}
