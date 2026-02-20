<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\CambioPendiente;
use App\Models\Comercio;

/**
 * Gestión de cambios pendientes de comerciantes
 * Admin revisa, aprueba o rechaza cambios enviados desde /mi-comercio/editar
 */
class CambiosPendientesController extends Controller
{
    /**
     * Listado de cambios pendientes
     */
    public function index(): void
    {
        $estado = $this->request->get('estado', 'pendiente');

        $filtroEstado = in_array($estado, ['pendiente', 'aprobado', 'rechazado']) ? $estado : null;
        $cambios = CambioPendiente::getFiltered($filtroEstado);

        // Contar por estado
        $conteos = [
            'pendiente' => CambioPendiente::countByEstado('pendiente'),
            'aprobado'  => CambioPendiente::countByEstado('aprobado'),
            'rechazado' => CambioPendiente::countByEstado('rechazado'),
        ];

        $this->render('admin/cambios-pendientes/index', [
            'title'       => 'Cambios Pendientes — ' . SITE_NAME,
            'cambios'     => $cambios,
            'conteos'     => $conteos,
            'estadoActual' => $estado,
        ]);
    }

    /**
     * Ver detalle de un cambio
     */
    public function show(string $id): void
    {
        $id = (int)$id;
        $cambio = CambioPendiente::find($id);

        if (!$cambio) {
            $this->redirect('/admin/cambios-pendientes', ['error' => 'Cambio no encontrado']);
            return;
        }

        // Datos actuales del comercio para comparar
        $comercio = Comercio::find($cambio['comercio_id']);

        // Decodificar JSON de cambios
        $cambiosData = json_decode($cambio['cambios_json'], true) ?: [];

        // Nombres legibles para campos
        $labels = [
            'nombre' => 'Nombre', 'descripcion' => 'Descripción', 'whatsapp' => 'WhatsApp',
            'telefono' => 'Teléfono', 'email' => 'Email', 'sitio_web' => 'Sitio web',
            'direccion' => 'Dirección', 'lat' => 'Latitud', 'lng' => 'Longitud',
            'logo' => 'Logo', 'portada' => 'Portada',
            'facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok',
            'youtube' => 'YouTube', 'x_twitter' => 'X (Twitter)', 'linkedin' => 'LinkedIn',
            'telegram' => 'Telegram', 'pinterest' => 'Pinterest',
            'categorias' => 'Categorías', 'fechas' => 'Fechas especiales',
        ];

        $this->render('admin/cambios-pendientes/show', [
            'title'       => 'Revisar cambio — ' . SITE_NAME,
            'cambio'      => $cambio,
            'comercio'    => $comercio,
            'cambiosData' => $cambiosData,
            'labels'      => $labels,
        ]);
    }

    /**
     * Aprobar cambios y aplicarlos al comercio
     */
    public function aprobar(string $id): void
    {
        $id = (int)$id;
        $cambio = CambioPendiente::getPendiente($id);

        if (!$cambio) {
            $this->redirect('/admin/cambios-pendientes', ['error' => 'Cambio no encontrado o ya procesado']);
            return;
        }

        $cambiosData = json_decode($cambio['cambios_json'], true) ?: [];
        $comercioId = $cambio['comercio_id'];

        // Aplicar cambios de campos simples
        $camposDirectos = ['nombre','descripcion','whatsapp','telefono','email','sitio_web','direccion',
                           'lat','lng','logo','portada','facebook','instagram','tiktok','youtube',
                           'x_twitter','linkedin','telegram','pinterest'];

        $update = [];
        foreach ($camposDirectos as $campo) {
            if (isset($cambiosData[$campo])) {
                $update[$campo] = $cambiosData[$campo]['nuevo'];
            }
        }

        if (!empty($update)) {
            // Si cambió el nombre, actualizar slug
            if (isset($update['nombre'])) {
                $update['slug'] = $this->generarSlug($update['nombre'], $comercioId);
            }
            Comercio::updateById($comercioId, $update);
        }

        // Aplicar categorías
        if (isset($cambiosData['categorias'])) {
            $principal = $cambiosData['categorias']['principal'] ?? null;
            Comercio::syncCategorias($comercioId, $cambiosData['categorias']['nuevo'], $principal ? (int)$principal : null);
        }

        // Aplicar fechas
        if (isset($cambiosData['fechas'])) {
            Comercio::syncFechas($comercioId, $cambiosData['fechas']['nuevo']);
        }

        Comercio::recalcularCalidad($comercioId);

        // Marcar como aprobado
        $notas = trim($_POST['notas'] ?? '');
        CambioPendiente::updateById($id, [
            'estado'      => 'aprobado',
            'notas'       => $notas,
            'revisado_por' => \App\Services\Auth::id(),
            'revisado_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->log('cambios', 'aprobar', 'comercio', $comercioId, "Cambios aprobados para comercio ID {$comercioId}");
        $this->redirect('/admin/cambios-pendientes', ['success' => 'Cambios aprobados y aplicados correctamente']);
    }

    /**
     * Rechazar cambios
     */
    public function rechazar(string $id): void
    {
        $id = (int)$id;
        $cambio = CambioPendiente::getPendiente($id);

        if (!$cambio) {
            $this->redirect('/admin/cambios-pendientes', ['error' => 'Cambio no encontrado o ya procesado']);
            return;
        }

        $notas = trim($_POST['notas'] ?? '');

        CambioPendiente::updateById($id, [
            'estado'      => 'rechazado',
            'notas'       => $notas,
            'revisado_por' => \App\Services\Auth::id(),
            'revisado_at'  => date('Y-m-d H:i:s'),
        ]);

        $this->log('cambios', 'rechazar', 'comercio', $cambio['comercio_id'], "Cambios rechazados para comercio ID {$cambio['comercio_id']}");
        $this->redirect('/admin/cambios-pendientes', ['success' => 'Cambios rechazados']);
    }

    // ── Helpers ──

    private function generarSlug(string $nombre, int $excludeId): string
    {
        $slug = slugify($nombre);
        $original = $slug;
        $i = 1;
        while ($this->db->fetch("SELECT id FROM comercios WHERE slug = ? AND id != ?", [$slug, $excludeId])) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }
}
