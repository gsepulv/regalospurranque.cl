<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

/**
 * Gestión de Planes Comerciales + Validación de Comercios
 * CRUD completo sobre planes_config + asignación + validación
 */
class PlanAdminController extends Controller
{
    /**
     * Vista principal con tabs: Configuración | Asignar | Validación
     */
    public function index(): void
    {
        $tab = $this->request->get('tab', 'config');

        // Planes desde planes_config
        $planes = $this->db->fetchAll("SELECT * FROM planes_config ORDER BY orden ASC");

        // Conteo por plan
        $conteos = [];
        foreach ($planes as $p) {
            $conteos[$p['slug']] = $this->db->count('comercios', "plan = '{$p['slug']}' AND activo = 1");
        }

        // Comercios para tab asignar
        $comercios = $this->db->fetchAll(
            "SELECT c.id, c.nombre, c.slug, c.plan, c.plan_precio, c.plan_inicio, c.plan_fin,
                    c.max_fotos, c.activo, c.validado, c.validado_fecha, c.validado_notas,
                    GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') as categorias
             FROM comercios c
             LEFT JOIN comercio_categoria cc ON c.id = cc.comercio_id
             LEFT JOIN categorias cat ON cc.categoria_id = cat.id
             GROUP BY c.id
             ORDER BY FIELD(c.plan, 'sponsor','premium','basico','freemium','banner'), c.nombre ASC"
        );

        // Para tab validación
        $validados = $this->db->fetchAll(
            "SELECT * FROM comercios WHERE validado = 1 AND activo = 1 ORDER BY validado_fecha DESC"
        );
        $noValidados = $this->db->fetchAll(
            "SELECT * FROM comercios WHERE (validado = 0 OR validado IS NULL) AND activo = 1 ORDER BY nombre"
        );

        $this->render('admin/planes/index', [
            'title'       => 'Planes y Validación — ' . SITE_NAME,
            'tab'         => $tab,
            'planes'      => $planes,
            'conteos'     => $conteos,
            'comercios'   => $comercios,
            'validados'   => $validados,
            'noValidados' => $noValidados,
        ]);
    }

    /**
     * Formulario crear plan
     */
    public function create(): void
    {
        $this->render('admin/planes/form', [
            'title' => 'Nuevo Plan — ' . SITE_NAME,
            'plan'  => null,
        ]);
    }

    /**
     * Guardar nuevo plan
     */
    public function store(): void
    {
        $datos = $this->getPlanData();

        if (empty($datos['slug']) || empty($datos['nombre'])) {
            $this->redirect('/admin/planes/crear', ['error' => 'El slug y nombre son obligatorios.']);
            return;
        }

        // Verificar slug único
        $existe = $this->db->fetch("SELECT id FROM planes_config WHERE slug = ?", [$datos['slug']]);
        if ($existe) {
            $this->redirect('/admin/planes/crear', ['error' => "Ya existe un plan con el slug '{$datos['slug']}'."]);
            return;
        }

        $this->db->insert('planes_config', $datos);
        $this->log('planes', 'crear_plan', 'plan', 0, "Plan creado: {$datos['nombre']}");
        $this->redirect('/admin/planes', ['success' => "Plan '{$datos['nombre']}' creado correctamente."]);
    }

    /**
     * Formulario editar plan
     */
    public function edit(int $id): void
    {
        $plan = $this->db->fetch("SELECT * FROM planes_config WHERE id = ?", [$id]);
        if (!$plan) {
            $this->redirect('/admin/planes', ['error' => 'Plan no encontrado.']);
            return;
        }

        $this->render('admin/planes/form', [
            'title' => 'Editar Plan: ' . $plan['nombre'] . ' — ' . SITE_NAME,
            'plan'  => $plan,
        ]);
    }

    /**
     * Actualizar plan existente
     */
    public function update(int $id): void
    {
        $plan = $this->db->fetch("SELECT * FROM planes_config WHERE id = ?", [$id]);
        if (!$plan) {
            $this->redirect('/admin/planes', ['error' => 'Plan no encontrado.']);
            return;
        }

        $datos = $this->getPlanData();
        unset($datos['slug']); // No permitir cambiar slug

        $this->db->update('planes_config', $datos, 'id = ?', [$id]);
        $this->log('planes', 'editar_plan', 'plan', $id, "Plan editado: {$plan['nombre']}");
        $this->redirect('/admin/planes', ['success' => "Plan '{$plan['nombre']}' actualizado."]);
    }

    /**
     * Eliminar plan
     */
    public function delete(int $id): void
    {
        $plan = $this->db->fetch("SELECT * FROM planes_config WHERE id = ?", [$id]);
        if (!$plan) {
            $this->redirect('/admin/planes', ['error' => 'Plan no encontrado.']);
            return;
        }

        // Verificar que no haya comercios con este plan
        $enUso = $this->db->count('comercios', "plan = '{$plan['slug']}' AND activo = 1");
        if ($enUso > 0) {
            $this->redirect('/admin/planes', ['error' => "No se puede eliminar: hay {$enUso} comercio(s) activo(s) con este plan."]);
            return;
        }

        $this->db->delete('planes_config', 'id = ?', [$id]);
        $this->log('planes', 'eliminar_plan', 'plan', $id, "Plan eliminado: {$plan['nombre']}");
        $this->redirect('/admin/planes', ['success' => "Plan '{$plan['nombre']}' eliminado."]);
    }

    /**
     * Asignar plan a comercio (AJAX - mantiene compatibilidad)
     */
    public function updatePlan(): void
    {
        $comercioId = (int)($_POST['comercio_id'] ?? 0);
        $plan       = $_POST['plan'] ?? '';

        // Validar que el plan exista en planes_config
        $planConfig = $this->db->fetch("SELECT * FROM planes_config WHERE slug = ?", [$plan]);
        if (!$planConfig) {
            $this->json(['ok' => false, 'error' => 'Plan inválido'], 400);
            return;
        }

        $comercio = $this->db->fetch("SELECT id, nombre, plan FROM comercios WHERE id = ?", [$comercioId]);
        if (!$comercio) {
            $this->json(['ok' => false, 'error' => 'Comercio no encontrado'], 404);
            return;
        }

        $this->db->update('comercios', ['plan' => $plan], 'id = ?', [$comercioId]);
        $this->log('planes', 'cambiar_plan', 'comercio', $comercioId,
            "{$comercio['nombre']}: {$comercio['plan']} → {$plan}");
        $this->json(['ok' => true, 'plan' => $plan, 'csrf' => $_SESSION['csrf_token']]);
    }

    /**
     * Asignar plan a comercio (formulario clásico)
     */
    public function assignPlan(): void
    {
        $comercioId = (int)($_POST['comercio_id'] ?? 0);
        $plan       = $_POST['plan'] ?? '';
        $planPrecio = !empty($_POST['plan_precio']) ? (int)$_POST['plan_precio'] : null;
        $planInicio = !empty($_POST['plan_inicio']) ? $_POST['plan_inicio'] : null;
        $planFin    = !empty($_POST['plan_fin']) ? $_POST['plan_fin'] : null;
        $maxFotos   = !empty($_POST['max_fotos']) ? (int)$_POST['max_fotos'] : null;

        $planConfig = $this->db->fetch("SELECT * FROM planes_config WHERE slug = ?", [$plan]);
        $comercio = $this->db->fetch("SELECT id, nombre, plan FROM comercios WHERE id = ?", [$comercioId]);

        if (!$planConfig || !$comercio) {
            $this->redirect('/admin/planes?tab=asignar', ['error' => 'Plan o comercio no encontrado.']);
            return;
        }

        if (!$maxFotos) {
            $maxFotos = $planConfig['max_fotos'];
        }

        $this->db->update('comercios', [
            'plan'        => $plan,
            'plan_precio' => $planPrecio,
            'plan_inicio' => $planInicio,
            'plan_fin'    => $planFin,
            'max_fotos'   => $maxFotos,
        ], 'id = ?', [$comercioId]);

        $this->log('planes', 'asignar_plan', 'comercio', $comercioId,
            "{$comercio['nombre']}: {$comercio['plan']} → {$plan}");
        $this->redirect('/admin/planes?tab=asignar', ['success' => "Plan de '{$comercio['nombre']}' actualizado a " . ucfirst($plan) . "."]);
    }

    /**
     * Validar/desvalidar comercio
     */
    public function validar(): void
    {
        $comercioId = (int)($_POST['comercio_id'] ?? 0);
        $validar    = isset($_POST['validar']) ? 1 : 0;
        $notas      = trim($_POST['validado_notas'] ?? '');

        $comercio = $this->db->fetch("SELECT id, nombre FROM comercios WHERE id = ?", [$comercioId]);
        if (!$comercio) {
            $this->redirect('/admin/planes?tab=validacion', ['error' => 'Comercio no encontrado.']);
            return;
        }

        if ($validar) {
            $this->db->update('comercios', [
                'validado'       => 1,
                'validado_fecha' => date('Y-m-d H:i:s'),
                'validado_notas' => $notas,
            ], 'id = ?', [$comercioId]);
            $this->log('planes', 'validar_comercio', 'comercio', $comercioId, "Validado: {$comercio['nombre']}");
            $this->redirect('/admin/planes?tab=validacion', ['success' => "'{$comercio['nombre']}' marcado como validado."]);
        } else {
            $this->db->update('comercios', [
                'validado'       => 0,
                'validado_fecha' => null,
                'validado_notas' => null,
            ], 'id = ?', [$comercioId]);
            $this->log('planes', 'desvalidar_comercio', 'comercio', $comercioId, "Desvalidado: {$comercio['nombre']}");
            $this->redirect('/admin/planes?tab=validacion', ['success' => "Validación de '{$comercio['nombre']}' removida."]);
        }
    }

    /**
     * Toggle sello verificado por plan
     */
    public function toggleSello(): void
    {
        $planId = (int)($_POST['plan_id'] ?? 0);
        $plan = $this->db->fetch("SELECT id, nombre, tiene_sello FROM planes_config WHERE id = ?", [$planId]);

        if (!$plan) {
            $this->redirect('/admin/planes?tab=validacion', ['error' => 'Plan no encontrado.']);
            return;
        }

        $nuevoValor = $plan['tiene_sello'] ? 0 : 1;
        $this->db->update('planes_config', ['tiene_sello' => $nuevoValor], 'id = ?', [$planId]);
        $estado = $nuevoValor ? 'activado' : 'desactivado';
        $this->log('planes', 'toggle_sello', 'plan', $planId, "Sello {$estado}: {$plan['nombre']}");
        $this->redirect('/admin/planes?tab=validacion', ['success' => "Sello verificado {$estado} para plan '{$plan['nombre']}'."]);
    }

    /**
     * Extraer datos del plan desde POST
     */
    private function getPlanData(): array
    {
        return [
            'slug'                => preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['slug'] ?? '')),
            'nombre'              => trim($_POST['nombre'] ?? ''),
            'descripcion'         => trim($_POST['descripcion'] ?? ''),
            'precio_intro'        => (int)($_POST['precio_intro'] ?? 0),
            'precio_regular'      => (int)($_POST['precio_regular'] ?? 0),
            'max_fotos'           => (int)($_POST['max_fotos'] ?? 1),
            'max_redes'           => (int)($_POST['max_redes'] ?? 1),
            'tiene_mapa'          => isset($_POST['tiene_mapa']) ? 1 : 0,
            'tiene_horarios'      => isset($_POST['tiene_horarios']) ? 1 : 0,
            'tiene_sello'         => isset($_POST['tiene_sello']) ? 1 : 0,
            'tiene_reporte'       => isset($_POST['tiene_reporte']) ? 1 : 0,
            'posicion'            => $_POST['posicion'] ?? 'normal',
            'max_cupos'           => !empty($_POST['max_cupos']) ? (int)$_POST['max_cupos'] : null,
            'max_cupos_categoria' => !empty($_POST['max_cupos_categoria']) ? (int)$_POST['max_cupos_categoria'] : null,
            'color'               => $_POST['color'] ?? '#6B7280',
            'icono'               => trim($_POST['icono'] ?? ''),
            'orden'               => (int)($_POST['orden'] ?? 0),
            'activo'              => isset($_POST['activo']) ? 1 : 0,
        ];
    }
}
