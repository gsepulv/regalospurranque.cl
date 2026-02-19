<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Analytics;

/**
 * Reportes y estadísticas del sitio
 */
class ReporteAdminController extends Controller
{
    /**
     * GET /admin/reportes — Dashboard general
     */
    public function index(): void
    {
        [$desde, $hasta] = $this->getPeriodo();

        $dashboard    = Analytics::getDashboard($desde, $hasta);
        $visitasDia   = Analytics::getVisitasPorDia($desde, $hasta);
        $paginasTop   = Analytics::getPaginasTop($desde, $hasta, 10);
        $tiposVisita  = Analytics::getVisitasPorTipo($desde, $hasta);
        $referrersTop = Analytics::getReferrersTop($desde, $hasta, 10);

        $this->render('admin/reportes/index', [
            'title'        => 'Reportes — ' . SITE_NAME,
            'dashboard'    => $dashboard,
            'visitasDia'   => $visitasDia,
            'paginasTop'   => $paginasTop,
            'tiposVisita'  => $tiposVisita,
            'referrersTop' => $referrersTop,
            'desde'        => $desde,
            'hasta'        => $hasta,
        ]);
    }

    /**
     * GET /admin/reportes/visitas — Reporte detallado de visitas
     */
    public function visitas(): void
    {
        [$desde, $hasta] = $this->getPeriodo();

        $visitasDia   = Analytics::getVisitasPorDia($desde, $hasta);
        $paginasTop   = Analytics::getPaginasTop($desde, $hasta, 20);
        $referrersTop = Analytics::getReferrersTop($desde, $hasta, 20);

        // Calcular totales
        $totalVisitas = array_sum(array_column($visitasDia, 'visitas'));
        $totalUnicos  = array_sum(array_column($visitasDia, 'unicos'));

        $this->render('admin/reportes/visitas', [
            'title'        => 'Reporte de Visitas — ' . SITE_NAME,
            'visitasDia'   => $visitasDia,
            'paginasTop'   => $paginasTop,
            'referrersTop' => $referrersTop,
            'totalVisitas' => $totalVisitas,
            'totalUnicos'  => $totalUnicos,
            'desde'        => $desde,
            'hasta'        => $hasta,
        ]);
    }

    /**
     * GET /admin/reportes/comercios — Ranking de comercios
     */
    public function comercios(): void
    {
        [$desde, $hasta] = $this->getPeriodo();

        $comercios    = Analytics::getComerciosTop($desde, $hasta, 50);
        $whatsappTop  = Analytics::getWhatsAppTop($desde, $hasta, 20);

        $this->render('admin/reportes/comercios', [
            'title'       => 'Reportes de Comercios — ' . SITE_NAME,
            'comercios'   => $comercios,
            'whatsappTop' => $whatsappTop,
            'desde'       => $desde,
            'hasta'       => $hasta,
        ]);
    }

    /**
     * GET /admin/reportes/categorias — Visitas por categoría
     */
    public function categorias(): void
    {
        [$desde, $hasta] = $this->getPeriodo();

        $categorias = Analytics::getVisitasPorCategoria($desde, $hasta);

        $this->render('admin/reportes/categorias', [
            'title'      => 'Reportes de Categorías — ' . SITE_NAME,
            'categorias' => $categorias,
            'desde'      => $desde,
            'hasta'      => $hasta,
        ]);
    }

    /**
     * GET /admin/reportes/fechas — Visitas por fecha especial
     */
    public function fechas(): void
    {
        [$desde, $hasta] = $this->getPeriodo();

        $fechas = Analytics::getVisitasPorFecha($desde, $hasta);

        $this->render('admin/reportes/fechas', [
            'title'  => 'Reportes de Fechas Especiales — ' . SITE_NAME,
            'fechas' => $fechas,
            'desde'  => $desde,
            'hasta'  => $hasta,
        ]);
    }

    /**
     * GET /admin/reportes/banners — Estadísticas de banners
     */
    public function banners(): void
    {
        $banners = Analytics::getBannersStats();

        $this->render('admin/reportes/banners', [
            'title'   => 'Reportes de Banners — ' . SITE_NAME,
            'banners' => $banners,
        ]);
    }

    /**
     * GET /admin/reportes/export — Exportar CSV
     */
    public function exportCsv(): void
    {
        $tipo = $this->request->get('tipo', 'visitas');
        [$desde, $hasta] = $this->getPeriodo();

        $data = Analytics::getExportData($tipo, $desde, $hasta);

        if (empty($data)) {
            $this->back(['error' => 'No hay datos para exportar']);
            return;
        }

        $filename = "reporte_{$tipo}_{$desde}_{$hasta}.csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache');

        $output = fopen('php://output', 'w');

        // BOM para Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]), ';');
        }

        // Rows
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Obtener período desde request o default (últimos 30 días)
     */
    private function getPeriodo(): array
    {
        $desde = $this->request->get('desde', date('Y-m-d', strtotime('-30 days')));
        $hasta = $this->request->get('hasta', date('Y-m-d'));

        // Validar formato de fecha
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde)) {
            $desde = date('Y-m-d', strtotime('-30 days'));
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $hasta = date('Y-m-d');
        }

        return [$desde, $hasta];
    }
}
