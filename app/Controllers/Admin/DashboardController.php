<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\AdminLog;
use App\Models\Banner;
use App\Models\Categoria;
use App\Models\Comercio;
use App\Models\FechaEspecial;
use App\Models\Noticia;
use App\Models\Resena;
use App\Services\Backup;

/**
 * Dashboard administrativo con estadísticas reales
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $stats = [
            'comercios_activos'   => 0,
            'comercios_inactivos' => 0,
            'categorias'          => 0,
            'fechas_personal'     => 0,
            'fechas_calendario'   => 0,
            'fechas_comercial'    => 0,
            'noticias'            => 0,
            'resenas_pendientes'  => 0,
            'resenas_aprobadas'   => 0,
            'resenas_rechazadas'  => 0,
            'visitas_hoy'         => 0,
            'visitas_semana'      => 0,
            'visitas_mes'         => 0,
            'banners_activos'     => 0,
        ];

        $ultimosComercios = [];
        $ultimasResenas   = [];
        $ultimasAcciones  = [];
        $visitasSemana    = [];

        try {
            $stats['comercios_activos']   = Comercio::countActive();
            $stats['comercios_inactivos'] = Comercio::countInactive();
            $stats['categorias']          = Categoria::countActive();
            $stats['fechas_personal']     = FechaEspecial::countByTipo('personal');
            $stats['fechas_calendario']   = FechaEspecial::countByTipo('calendario');
            $stats['fechas_comercial']    = FechaEspecial::countByTipo('comercial');
            $stats['noticias']            = Noticia::countActive();

            $resenaCounts = Resena::countByEstado();
            $stats['resenas_pendientes']  = $resenaCounts['pendiente'];
            $stats['resenas_aprobadas']   = $resenaCounts['aprobada'];
            $stats['resenas_rechazadas']  = $resenaCounts['rechazada'];

            $stats['visitas_hoy']         = $this->db->count('visitas_log', 'DATE(created_at) = CURDATE()');
            $stats['visitas_semana']      = $this->db->count('visitas_log', 'created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)');
            $stats['visitas_mes']         = $this->db->count('visitas_log', 'created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)');
            $stats['banners_activos']     = Banner::countActive();

            // Últimos 5 comercios
            $ultimosComercios = Comercio::getRecentLimit(5);

            // Últimas 5 reseñas pendientes
            $ultimasResenas = $this->db->fetchAll(
                "SELECT r.id, r.nombre_autor, r.calificacion, r.comentario, r.created_at,
                        c.nombre as comercio_nombre
                 FROM resenas r
                 INNER JOIN comercios c ON r.comercio_id = c.id
                 WHERE r.estado = 'pendiente'
                 ORDER BY r.created_at DESC LIMIT 5"
            );

            // Últimas 10 acciones
            $ultimasAcciones = AdminLog::getRecentLimit(10);

            // Visitas últimos 7 días para gráfico
            $visitasSemana = AdminLog::getWeeklyStats();
        } catch (\Throwable $e) {
            // Sin BD, mostrar ceros
        }

        // Rellenar días sin visitas
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $chartData[$date] = 0;
        }
        foreach ($visitasSemana as $v) {
            $chartData[$v['fecha']] = (int) $v['total'];
        }

        // Mini salud del sistema para widget
        $healthWidget = $this->getHealthWidget();

        $this->render('admin/dashboard', [
            'title'              => 'Dashboard — ' . SITE_NAME,
            'stats'              => $stats,
            'ultimosComercios'   => $ultimosComercios,
            'ultimasResenas'     => $ultimasResenas,
            'acciones'           => $ultimasAcciones,
            'chartData'          => $chartData,
            'healthWidget'       => $healthWidget,
        ]);
    }

    /**
     * Widget de salud para el dashboard
     */
    private function getHealthWidget(): array
    {
        $widget = [
            'score'       => 0,
            'lastBackup'  => null,
            'diskFree'    => Backup::formatSize(Backup::getDiskFreeSpace()),
            'alerts'      => [],
        ];

        try {
            // Verificaciones rápidas
            $points = 0;
            $max    = 0;

            // PHP >= 8.0
            $max += 10;
            if (version_compare(PHP_VERSION, '8.0.0', '>=')) $points += 10;
            else $widget['alerts'][] = 'PHP < 8.0';

            // MySQL conectada
            $max += 10;
            try {
                $this->db->getPDO()->query("SELECT 1");
                $points += 10;
            } catch (\Throwable $e) {
                $widget['alerts'][] = 'Error conexión MySQL';
            }

            // storage/ escribible
            $max += 10;
            $storageDirs = ['storage/backups', 'storage/logs', 'storage/cache'];
            $writableCount = 0;
            foreach ($storageDirs as $d) {
                if (is_dir(BASE_PATH . '/' . $d) && is_writable(BASE_PATH . '/' . $d)) $writableCount++;
            }
            if ($writableCount === count($storageDirs)) $points += 10;
            elseif ($writableCount > 0) $points += 5;
            else $widget['alerts'][] = 'storage/ sin permisos';

            // Backup reciente
            $max += 5;
            $backups = Backup::listBackups();
            if (!empty($backups)) {
                $widget['lastBackup'] = $backups[0]['fecha'];
                $lastDate = strtotime($backups[0]['fecha']);
                if ($lastDate >= strtotime('-7 days')) $points += 5;
                else {
                    $points += 2;
                    $widget['alerts'][] = 'Backup > 7 días';
                }
            } else {
                $widget['alerts'][] = 'Sin backups';
            }

            // Disco libre
            $max += 5;
            $freeMb = Backup::getDiskFreeSpace() / (1024 * 1024);
            if ($freeMb > 500) $points += 5;
            elseif ($freeMb > 100) $points += 3;
            else $widget['alerts'][] = 'Disco < 100MB';

            $widget['score'] = $max > 0 ? round(($points / $max) * 100) : 0;
        } catch (\Throwable $e) {
            // Silenciar errores del widget
        }

        return $widget;
    }
}
