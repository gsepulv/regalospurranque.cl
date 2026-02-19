<?php
namespace App\Controllers\Admin;

use App\Core\Controller;

/**
 * Gestión de configuración general del sitio
 * Lectura y escritura de parámetros en tabla configuracion
 */
class ConfigController extends Controller
{
    /**
     * Definición de claves de configuración con sus grupos y valores por defecto
     */
    private const DEFAULTS = [
        'general' => [
            'site_name'        => '',
            'site_description' => '',
            'contact_email'    => '',
            'contact_phone'    => '',
            'address'          => '',
            'logo'             => '',
            'favicon'          => '',
        ],
        'social' => [
            'facebook_url'  => '',
            'instagram_url' => '',
            'twitter_url'   => '',
            'youtube_url'   => '',
            'tiktok_url'    => '',
        ],
        'email' => [
            'notification_email'   => '',
            'notification_subject' => '',
        ],
        'features' => [
            'resenas_enabled'   => '1',
            'mapa_enabled'      => '1',
            'noticias_enabled'  => '1',
            'banners_enabled'   => '1',
            'share_enabled'     => '1',
            'analytics_enabled' => '1',
        ],
        'appearance' => [
            'color_primary'      => '#e11d48',
            'color_accent'       => '#f59e0b',
            'show_visit_counter' => '1',
            'show_rating_cards'  => '1',
        ],
    ];

    /**
     * GET /admin/mantenimiento/configuracion
     * Mostrar formulario de configuración agrupada
     */
    public function index(): void
    {
        // Cargar toda la configuración desde BD
        $rows    = $this->db->fetchAll("SELECT clave, valor, grupo FROM configuracion");
        $dbConfig = [];
        foreach ($rows as $row) {
            $dbConfig[$row['clave']] = $row['valor'];
        }

        // Construir configuración plana con defaults + valores BD
        $config = [];
        foreach (self::DEFAULTS as $grupo => $keys) {
            foreach ($keys as $clave => $default) {
                $config[$clave] = $dbConfig[$clave] ?? $default;
            }
        }

        $this->render('admin/mantenimiento/configuracion', [
            'title'  => 'Configuración — ' . SITE_NAME,
            'tab'    => 'configuracion',
            'config' => $config,
        ]);
    }

    /**
     * POST /admin/mantenimiento/configuracion
     * Guardar toda la configuración
     */
    public function update(): void
    {
        // Procesar uploads de logo y favicon si existen
        $uploadFields = ['logo', 'favicon'];
        $uploaded     = [];

        foreach ($uploadFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $result = $this->handleFileUpload($field);
                if ($result !== null) {
                    $uploaded[$field] = $result;
                }
            }
        }

        // Guardar cada clave de configuración
        foreach (self::DEFAULTS as $grupo => $keys) {
            foreach ($keys as $clave => $default) {
                // Si hay archivo subido, usar la ruta del upload
                if (isset($uploaded[$clave])) {
                    $valor = $uploaded[$clave];
                } else {
                    $valor = trim($this->request->post($clave, $default));
                }

                $this->db->execute(
                    "INSERT INTO configuracion (clave, valor, grupo) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
                    [$clave, $valor, $grupo]
                );
            }
        }

        $this->log('mantenimiento', 'actualizar_config', 'configuracion', 0, 'Configuración general actualizada');

        $this->redirect('/admin/mantenimiento/configuracion', [
            'success' => 'Configuración guardada correctamente',
        ]);
    }

    /**
     * Manejar subida de archivo (logo o favicon)
     * Retorna la ruta relativa del archivo guardado o null si falla
     */
    private function handleFileUpload(string $field): ?string
    {
        $file = $_FILES[$field];

        // Validar tipo MIME
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            'image/x-icon', 'image/vnd.microsoft.icon',
        ];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowedMimes, true)) {
            return null;
        }

        // Validar tamaño (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return null;
        }

        // Generar nombre seguro
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $ext      = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ext));
        $filename = $field . '_' . time() . '.' . $ext;

        $destDir  = BASE_PATH . '/assets/img/config';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $destPath = $destDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            return '/assets/img/config/' . $filename;
        }

        return null;
    }
}
