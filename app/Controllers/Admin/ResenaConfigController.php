<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

/**
 * Configuración del módulo de reseñas
 */
class ResenaConfigController extends Controller
{
    private array $defaults = [
        'resenas_activas'    => '1',
        'moderacion'         => '1',
        'min_caracteres'     => '10',
        'max_caracteres'     => '2000',
        'permitir_anonimas'  => '0',
        'notificar_nueva'    => '1',
        'permitir_respuesta' => '1',
        'max_por_ip_dia'     => '3',
    ];

    /**
     * GET /admin/resenas/configuracion
     */
    public function index(): void
    {
        $config = $this->getConfig();

        $this->render('admin/resenas/configuracion', [
            'title'  => 'Configuración de Reseñas — ' . SITE_NAME,
            'config' => $config,
        ]);
    }

    /**
     * POST /admin/resenas/configuracion
     */
    public function update(): void
    {
        $keys = array_keys($this->defaults);

        foreach ($keys as $key) {
            $value = $this->request->post($key, $this->defaults[$key]);

            // Checkboxes envían el valor solo si están marcados
            if (in_array($key, ['resenas_activas', 'moderacion', 'permitir_anonimas', 'notificar_nueva', 'permitir_respuesta'])) {
                $value = $this->request->post($key) !== null ? '1' : '0';
            }

            $this->db->execute(
                "INSERT INTO configuracion (clave, valor, grupo) VALUES (?, ?, 'resenas')
                 ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
                [$key, $value]
            );
        }

        $this->log('resenas', 'config_update', 'configuracion', 0, 'Configuración de reseñas actualizada');

        $this->back(['success' => 'Configuración guardada correctamente']);
    }

    /**
     * Obtener configuración actual con defaults
     */
    private function getConfig(): array
    {
        $rows = $this->db->fetchAll(
            "SELECT clave, valor FROM configuracion WHERE grupo = 'resenas'"
        );

        $config = $this->defaults;
        foreach ($rows as $row) {
            $config[$row['clave']] = $row['valor'];
        }

        return $config;
    }
}
