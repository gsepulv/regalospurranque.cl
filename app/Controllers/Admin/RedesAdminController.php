<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\RedesSociales;
use App\Services\FileManager;

/**
 * Gestión de Redes Sociales desde el panel admin
 * 6 Tabs: Perfiles, Share, Open Graph, Estadísticas, WhatsApp Flotante, TinyMCE
 */
class RedesAdminController extends Controller
{
    public function index(): void
    {
        $config = RedesSociales::getAll();

        // Stats de shares (últimos 30 días)
        $shareStats = [];
        try {
            $shareStats = $this->db->fetchAll(
                "SELECT red_social, COUNT(*) as total
                 FROM share_log
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY red_social
                 ORDER BY total DESC"
            );
        } catch (\Throwable $e) {}

        $topShared = [];
        try {
            $topShared = $this->db->fetchAll(
                "SELECT pagina, COUNT(*) as total
                 FROM share_log
                 WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY pagina
                 ORDER BY total DESC
                 LIMIT 10"
            );
        } catch (\Throwable $e) {}

        $this->render('admin/redes-sociales/index', [
            'title'      => 'Redes Sociales — ' . SITE_NAME,
            'config'     => $config,
            'shareStats' => $shareStats,
            'topShared'  => $topShared,
        ]);
    }

    public function update(): void
    {
        $tab = $_POST['_tab'] ?? 'profiles';

        // Guardar todos los campos enviados
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, '_') || $key === '_csrf') continue;
            RedesSociales::set($key, is_array($value) ? implode(',', $value) : trim($value));
        }

        // Para checkboxes no marcados (share toggles)
        $checkboxFields = [
            'profiles' => [
                'profiles_page_all', 'profiles_page_home', 'profiles_page_comercio',
                'profiles_page_categoria', 'profiles_page_fecha', 'profiles_page_noticias_list',
                'profiles_page_noticia', 'profiles_page_mapa', 'profiles_page_buscar',
                'profiles_page_contacto', 'profiles_page_landing', 'profiles_page_default',
                'profiles_pos_header', 'profiles_pos_below_header', 'profiles_pos_sidebar',
                'profiles_pos_after_title', 'profiles_pos_before_footer', 'profiles_pos_footer',
                'profiles_pos_floating',
            ],
            'share' => [
                'share_facebook', 'share_twitter', 'share_whatsapp', 'share_linkedin',
                'share_telegram', 'share_pinterest', 'share_email', 'share_copy', 'share_native',
                'share_page_home', 'share_page_comercio', 'share_page_categoria',
                'share_page_fecha', 'share_page_noticia', 'share_page_mapa', 'share_page_default',
                'share_pos_above_content', 'share_pos_below_content', 'share_pos_sidebar',
                'share_pos_floating_bar', 'share_pos_floating_circle', 'share_pos_in_cards',
            ],
            'whatsapp' => ['whatsapp_float_enabled', 'whatsapp_float_animation'],
            'tinymce'  => ['tinymce_autosave'],
        ];

        if (isset($checkboxFields[$tab])) {
            foreach ($checkboxFields[$tab] as $field) {
                if (!isset($_POST[$field])) {
                    RedesSociales::set($field, '0');
                }
            }
        }

        // Subir imagen OG si se envió
        if ($tab === 'og') {
            $ogImage = $this->request->file('og_default_image_file');
            if ($ogImage && $ogImage['error'] === UPLOAD_ERR_OK) {
                $filename = FileManager::subirImagen($ogImage, 'og', 1200);
                if ($filename) {
                    RedesSociales::set('og_default_image', $filename);
                }
            }
        }

        RedesSociales::resetCache();
        $this->log('redes_sociales', 'actualizar', 'config', 0, "Tab: {$tab}");
        $this->redirect('/admin/redes-sociales', ['success' => 'Configuración guardada correctamente']);
    }
}
