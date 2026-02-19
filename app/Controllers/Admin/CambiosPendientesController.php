<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

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

        $where = '1=1';
        $params = [];
        if (in_array($estado, ['pendiente', 'aprobado', 'rechazado'])) {
            $where .= ' AND cp.estado = ?';
            $params[] = $estado;
        }

        $cambios = $this->db->fetchAll(
            "SELECT cp.*, c.nombre as comercio_nombre, c.slug as comercio_slug,
                    u.nombre as usuario_nombre, u.email as usuario_email
             FROM comercio_cambios_pendientes cp
             JOIN comercios c ON cp.comercio_id = c.id
             JOIN admin_usuarios u ON cp.usuario_id = u.id
             WHERE {$where}
             ORDER BY cp.created_at DESC
             LIMIT 50",
            $params
        );

        // Contar por estado
        $conteos = [
            'pendiente' => $this->db->fetch("SELECT COUNT(*) as n FROM comercio_cambios_pendientes WHERE estado = 'pendiente'")['n'] ?? 0,
            'aprobado'  => $this->db->fetch("SELECT COUNT(*) as n FROM comercio_cambios_pendientes WHERE estado = 'aprobado'")['n'] ?? 0,
            'rechazado' => $this->db->fetch("SELECT COUNT(*) as n FROM comercio_cambios_pendientes WHERE estado = 'rechazado'")['n'] ?? 0,
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
        $cambio = $this->db->fetch(
            "SELECT cp.*, c.nombre as comercio_nombre, c.id as comercio_id,
                    u.nombre as usuario_nombre, u.email as usuario_email
             FROM comercio_cambios_pendientes cp
             JOIN comercios c ON cp.comercio_id = c.id
             JOIN admin_usuarios u ON cp.usuario_id = u.id
             WHERE cp.id = ?",
            [$id]
        );

        if (!$cambio) {
            $this->redirect('/admin/cambios-pendientes', ['error' => 'Cambio no encontrado']);
            return;
        }

        // Datos actuales del comercio para comparar
        $comercio = $this->db->fetch("SELECT * FROM comercios WHERE id = ?", [$cambio['comercio_id']]);

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
        $cambio = $this->db->fetch("SELECT * FROM comercio_cambios_pendientes WHERE id = ? AND estado = 'pendiente'", [$id]);

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
            $this->db->update('comercios', $update, 'id = ?', [$comercioId]);
        }

        // Aplicar categorías
        if (isset($cambiosData['categorias'])) {
            $this->db->delete('comercio_categoria', 'comercio_id = ?', [$comercioId]);
            $principal = $cambiosData['categorias']['principal'] ?? 0;
            foreach ($cambiosData['categorias']['nuevo'] as $catId) {
                $this->db->insert('comercio_categoria', [
                    'comercio_id'  => $comercioId,
                    'categoria_id' => (int)$catId,
                    'es_principal' => ((int)$catId === (int)$principal) ? 1 : 0,
                ]);
            }
        }

        // Aplicar fechas
        if (isset($cambiosData['fechas'])) {
            $this->db->delete('comercio_fecha', 'comercio_id = ?', [$comercioId]);
            foreach ($cambiosData['fechas']['nuevo'] as $fId) {
                $this->db->insert('comercio_fecha', [
                    'comercio_id' => $comercioId,
                    'fecha_id'    => (int)$fId,
                    'activo'      => 1,
                ]);
            }
        }

        // Marcar como aprobado
        $notas = trim($_POST['notas'] ?? '');
        $this->db->update('comercio_cambios_pendientes', [
            'estado'      => 'aprobado',
            'notas'       => $notas,
            'revisado_por' => \App\Services\Auth::id(),
            'revisado_at'  => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

        $this->log('cambios', 'aprobar', 'comercio', $comercioId, "Cambios aprobados para comercio ID {$comercioId}");
        $this->redirect('/admin/cambios-pendientes', ['success' => 'Cambios aprobados y aplicados correctamente']);
    }

    /**
     * Rechazar cambios
     */
    public function rechazar(string $id): void
    {
        $id = (int)$id;
        $cambio = $this->db->fetch("SELECT * FROM comercio_cambios_pendientes WHERE id = ? AND estado = 'pendiente'", [$id]);

        if (!$cambio) {
            $this->redirect('/admin/cambios-pendientes', ['error' => 'Cambio no encontrado o ya procesado']);
            return;
        }

        $notas = trim($_POST['notas'] ?? '');

        $this->db->update('comercio_cambios_pendientes', [
            'estado'      => 'rechazado',
            'notas'       => $notas,
            'revisado_por' => \App\Services\Auth::id(),
            'revisado_at'  => date('Y-m-d H:i:s'),
        ], 'id = ?', [$id]);

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
