<?php
namespace App\Services;

use App\Core\Database;

/**
 * Servicio de Redes Sociales
 * Gestiona perfiles, share buttons, OG, WhatsApp flotante y TinyMCE config
 */
class RedesSociales
{
    private static ?array $cache = null;

    /**
     * Obtener un valor de configuración
     */
    public static function get(string $clave, string $default = ''): string
    {
        $all = self::getAll();
        return $all[$clave] ?? $default;
    }

    /**
     * Guardar un valor de configuración
     */
    public static function set(string $clave, string $valor): void
    {
        $siteId = self::getSiteId();
        $db = Database::getInstance();

        $exists = $db->fetch(
            "SELECT id FROM redes_sociales_config WHERE site_id = ? AND clave = ?",
            [$siteId, $clave]
        );

        if ($exists) {
            $db->execute(
                "UPDATE redes_sociales_config SET valor = ? WHERE site_id = ? AND clave = ?",
                [$valor, $siteId, $clave]
            );
        } else {
            $db->insert('redes_sociales_config', [
                'site_id' => $siteId,
                'clave'   => $clave,
                'valor'   => $valor,
            ]);
        }

        self::$cache = null;
    }

    /**
     * Obtener todas las configuraciones
     */
    public static function getAll(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        try {
            $siteId = self::getSiteId();
            $rows = Database::getInstance()->fetchAll(
                "SELECT clave, valor FROM redes_sociales_config WHERE site_id = ?",
                [$siteId]
            );

            $config = [];
            foreach ($rows as $row) {
                $config[$row['clave']] = $row['valor'];
            }

            self::$cache = $config;
        } catch (\Throwable $e) {
            self::$cache = [];
        }

        return self::$cache;
    }

    /**
     * SISTEMA A: Obtener perfiles de redes sociales con URLs configuradas
     */
    public static function getProfiles(): array
    {
        $all = self::getAll();
        $profiles = [];

        $networks = [
            'facebook'  => ['label' => 'Facebook',  'color' => '#1877f2', 'icon' => 'facebook'],
            'instagram' => ['label' => 'Instagram', 'color' => '#e4405f', 'icon' => 'instagram'],
            'twitter'   => ['label' => 'X (Twitter)', 'color' => '#0f1419', 'icon' => 'twitter'],
            'youtube'   => ['label' => 'YouTube',   'color' => '#ff0000', 'icon' => 'youtube'],
            'tiktok'    => ['label' => 'TikTok',    'color' => '#000000', 'icon' => 'tiktok'],
            'linkedin'  => ['label' => 'LinkedIn',  'color' => '#0a66c2', 'icon' => 'linkedin'],
            'pinterest' => ['label' => 'Pinterest', 'color' => '#e60023', 'icon' => 'pinterest'],
            'telegram'  => ['label' => 'Telegram',  'color' => '#0088cc', 'icon' => 'telegram'],
            'whatsapp'  => ['label' => 'WhatsApp',  'color' => '#25d366', 'icon' => 'whatsapp'],
        ];

        foreach ($networks as $key => $meta) {
            $url = $all["profile_{$key}"] ?? '';
            if (!empty($url)) {
                $profiles[$key] = array_merge($meta, ['url' => $url]);
            }
        }

        return $profiles;
    }

    /**
     * SISTEMA B: Obtener configuración de share buttons
     */
    public static function getShareConfig(): array
    {
        $all = self::getAll();
        return [
            'buttons' => [
                'facebook'  => ($all['share_facebook'] ?? '1') === '1',
                'twitter'   => ($all['share_twitter'] ?? '1') === '1',
                'whatsapp'  => ($all['share_whatsapp'] ?? '1') === '1',
                'linkedin'  => ($all['share_linkedin'] ?? '0') === '1',
                'telegram'  => ($all['share_telegram'] ?? '0') === '1',
                'pinterest' => ($all['share_pinterest'] ?? '0') === '1',
                'email'     => ($all['share_email'] ?? '1') === '1',
                'copy'      => ($all['share_copy'] ?? '1') === '1',
                'native'    => ($all['share_native'] ?? '1') === '1',
            ],
            'show_on' => [
                'comercio'   => ($all['share_show_comercio'] ?? '1') === '1',
                'noticias'   => ($all['share_show_noticias'] ?? '1') === '1',
                'fechas'     => ($all['share_show_fechas'] ?? '1') === '1',
                'categorias' => ($all['share_show_categorias'] ?? '0') === '1',
            ],
            'text_template' => $all['share_text_template'] ?? '{titulo} — Encuéntralo en {sitio}',
            'hashtags'      => $all['share_hashtags'] ?? '',
        ];
    }

    /**
     * Obtener defaults de Open Graph
     */
    public static function getOgDefaults(): array
    {
        $all = self::getAll();
        return [
            'image'       => $all['og_default_image'] ?? '',
            'title'       => $all['og_default_title'] ?? '',
            'description' => $all['og_default_description'] ?? '',
        ];
    }

    /**
     * Obtener configuración de WhatsApp flotante
     */
    public static function getWhatsAppFloat(): array
    {
        $all = self::getAll();
        return [
            'enabled'      => ($all['whatsapp_float_enabled'] ?? '0') === '1',
            'number'       => $all['whatsapp_float_number'] ?? '',
            'message'      => $all['whatsapp_float_message'] ?? 'Hola, quiero información sobre {sitio}',
            'position'     => $all['whatsapp_float_position'] ?? 'right',
            'show_on'      => $all['whatsapp_float_show_on'] ?? 'all',
            'custom_pages' => $all['whatsapp_float_custom_pages'] ?? '',
            'hour_start'   => $all['whatsapp_float_hour_start'] ?? '09:00',
            'hour_end'     => $all['whatsapp_float_hour_end'] ?? '20:00',
            'animation'    => ($all['whatsapp_float_animation'] ?? '1') === '1',
        ];
    }

    /**
     * Obtener solo los share buttons activos
     */
    public static function getActiveShareButtons(): array
    {
        $config = self::getShareConfig();
        $active = [];

        $meta = [
            'facebook'  => ['label' => 'Facebook',  'color' => '#1877f2'],
            'twitter'   => ['label' => 'X (Twitter)', 'color' => '#0f1419'],
            'whatsapp'  => ['label' => 'WhatsApp',  'color' => '#25d366'],
            'linkedin'  => ['label' => 'LinkedIn',  'color' => '#0a66c2'],
            'telegram'  => ['label' => 'Telegram',  'color' => '#0088cc'],
            'pinterest' => ['label' => 'Pinterest', 'color' => '#e60023'],
            'email'     => ['label' => 'Email',     'color' => '#64748b'],
            'copy'      => ['label' => 'Copiar enlace', 'color' => '#64748b'],
            'native'    => ['label' => 'Compartir', 'color' => '#1e293b'],
        ];

        foreach ($config['buttons'] as $key => $enabled) {
            if ($enabled && isset($meta[$key])) {
                $active[$key] = $meta[$key];
            }
        }

        return $active;
    }

    /**
     * Verificar si se deben mostrar share buttons en un tipo de pagina
     * @param string $pageType - home|comercio|categoria|fecha|noticia|mapa
     */
    public static function shouldShowShare(string $pageType): bool
    {
        // New location system takes priority
        if (self::exists('share_page_' . $pageType)) {
            return self::get('share_page_' . $pageType, '0') === '1';
        }
        // Fallback to old system for backwards compat
        $config = self::getShareConfig();
        if (isset($config['show_on'][$pageType])) {
            return $config['show_on'][$pageType];
        }
        // Use default for unknown pages
        return self::get('share_page_default', '1') === '1';
    }

    /**
     * Verificar si mostrar share en esta posicion
     * @param string $position - above_content|below_content|sidebar|floating_bar|floating_circle|in_cards
     */
    public static function shouldShowShareAt(string $position): bool
    {
        return self::get('share_pos_' . $position, '0') === '1';
    }

    /**
     * Verificar si mostrar perfiles en esta pagina
     * @param string $pageType - home|comercio|categoria|fecha|noticias_list|noticia|mapa|buscar|contacto|landing
     */
    public static function shouldShowProfiles(string $pageType): bool
    {
        if (self::get('profiles_page_all', '0') === '1') return true;
        if (self::exists('profiles_page_' . $pageType)) {
            return self::get('profiles_page_' . $pageType, '0') === '1';
        }
        return self::get('profiles_page_default', '1') === '1';
    }

    /**
     * Verificar si mostrar perfiles en esta posicion
     * @param string $position - header|below_header|sidebar|after_title|before_footer|footer|floating
     */
    public static function shouldShowProfilesAt(string $position): bool
    {
        return self::get('profiles_pos_' . $position, '0') === '1';
    }

    /**
     * Verificar si existe una clave en la configuracion
     */
    public static function exists(string $clave): bool
    {
        $all = self::getAll();
        return array_key_exists($clave, $all);
    }

    /**
     * Obtener configuracion de estilo de perfiles
     */
    public static function getProfileStyles(): array
    {
        return [
            'style'   => self::get('profiles_style', 'circular_color'),
            'size'    => self::get('profiles_size', 'medium'),
            'align'   => self::get('profiles_align', 'left'),
            'spacing' => self::get('profiles_spacing', 'normal'),
        ];
    }

    /**
     * Obtener array de sameAs para Schema.org
     */
    public static function getSameAsArray(): array
    {
        $profiles = self::getProfiles();
        $urls = [];
        foreach ($profiles as $profile) {
            if (!empty($profile['url'])) {
                $urls[] = $profile['url'];
            }
        }
        return $urls;
    }

    /**
     * Formatear texto de share reemplazando variables
     */
    public static function formatShareText(string $titulo = '', string $descripcion = '', string $url = ''): string
    {
        $template = self::get('share_text_template', '{titulo} — Encuéntralo en {sitio}');
        return str_replace(
            ['{titulo}', '{sitio}', '{url}', '{descripcion}'],
            [$titulo, SITE_NAME, $url, $descripcion],
            $template
        );
    }

    /**
     * Obtener configuración de visualización de perfiles
     */
    public static function getProfilesDisplay(): array
    {
        $all = self::getAll();
        return [
            'show_footer'   => ($all['profiles_show_footer'] ?? '1') === '1',
            'show_header'   => ($all['profiles_show_header'] ?? '0') === '1',
            'show_sidebar'  => ($all['profiles_show_sidebar'] ?? '0') === '1',
            'show_contacto' => ($all['profiles_show_contacto'] ?? '1') === '1',
            'style'         => $all['profiles_style'] ?? 'circular_color',
            'size'          => $all['profiles_size'] ?? 'medium',
        ];
    }

    /**
     * Verificar si el WhatsApp flotante debe mostrarse en la página actual
     */
    public static function shouldShowWhatsAppFloat(): bool
    {
        $config = self::getWhatsAppFloat();

        if (!$config['enabled'] || empty($config['number'])) {
            return false;
        }

        // Verificar horario
        $now = date('H:i');
        if ($config['hour_start'] && $config['hour_end']) {
            if ($now < $config['hour_start'] || $now > $config['hour_end']) {
                return false;
            }
        }

        // Verificar página
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($config['show_on'] === 'all') {
            return true;
        }
        if ($config['show_on'] === 'comercios') {
            return str_starts_with($path, '/comercio/');
        }
        if ($config['show_on'] === 'custom') {
            $pages = array_filter(array_map('trim', explode("\n", $config['custom_pages'])));
            foreach ($pages as $page) {
                if (str_starts_with($path, $page)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Reset cache
     */
    public static function resetCache(): void
    {
        self::$cache = null;
    }

    private static function getSiteId(): int
    {
        if (class_exists('\\App\\Services\\SiteManager')) {
            try {
                return SiteManager::getInstance()->getSiteId();
            } catch (\Throwable $e) {}
        }
        return 1;
    }
}
