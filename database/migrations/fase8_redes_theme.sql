-- ============================================================================
-- Fase 8: Redes Sociales, Theme y TinyMCE Config
-- Ejecutar en MySQL 8.4 compatible con only_full_group_by
-- ============================================================================

-- Tabla clave-valor para redes sociales, share, OG, WhatsApp, TinyMCE y theme
CREATE TABLE IF NOT EXISTS redes_sociales_config (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id INT UNSIGNED NOT NULL DEFAULT 1,
    clave VARCHAR(100) NOT NULL,
    valor TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_site_clave (site_id, clave),
    INDEX idx_site (site_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Datos iniciales para site_id = 1
-- ============================================================================
INSERT INTO redes_sociales_config (site_id, clave, valor) VALUES

-- ── SISTEMA A: URLs de perfiles de redes sociales ──────────────────────────
(1, 'profile_facebook', ''),
(1, 'profile_instagram', ''),
(1, 'profile_twitter', ''),
(1, 'profile_twitter_username', ''),
(1, 'profile_youtube', ''),
(1, 'profile_tiktok', ''),
(1, 'profile_linkedin', ''),
(1, 'profile_pinterest', ''),
(1, 'profile_telegram', ''),
(1, 'profile_whatsapp', ''),

-- Configuración de visualización de perfiles
(1, 'profiles_show_footer', '1'),
(1, 'profiles_show_header', '0'),
(1, 'profiles_show_sidebar', '0'),
(1, 'profiles_show_contacto', '1'),
(1, 'profiles_style', 'circular_color'),
(1, 'profiles_size', 'medium'),

-- ── SISTEMA B: Botones de share ────────────────────────────────────────────
(1, 'share_facebook', '1'),
(1, 'share_twitter', '1'),
(1, 'share_whatsapp', '1'),
(1, 'share_linkedin', '0'),
(1, 'share_telegram', '0'),
(1, 'share_pinterest', '0'),
(1, 'share_email', '1'),
(1, 'share_copy', '1'),
(1, 'share_native', '1'),

-- Dónde mostrar share
(1, 'share_show_comercio', '1'),
(1, 'share_show_noticias', '1'),
(1, 'share_show_fechas', '1'),
(1, 'share_show_categorias', '0'),

-- Personalización del share
(1, 'share_text_template', '{titulo} — Encuéntralo en {sitio}'),
(1, 'share_hashtags', 'Purranque,Comercio'),

-- ── Open Graph defaults ────────────────────────────────────────────────────
(1, 'og_default_image', ''),
(1, 'og_default_title', ''),
(1, 'og_default_description', ''),

-- ── WhatsApp flotante ──────────────────────────────────────────────────────
(1, 'whatsapp_float_enabled', '0'),
(1, 'whatsapp_float_number', ''),
(1, 'whatsapp_float_message', 'Hola, quiero información sobre {sitio}'),
(1, 'whatsapp_float_position', 'right'),
(1, 'whatsapp_float_show_on', 'all'),
(1, 'whatsapp_float_custom_pages', ''),
(1, 'whatsapp_float_hour_start', '09:00'),
(1, 'whatsapp_float_hour_end', '20:00'),
(1, 'whatsapp_float_animation', '1'),

-- ── TinyMCE config ─────────────────────────────────────────────────────────
(1, 'tinymce_api_key', 'i6a26x0ftxfpmazpucux3gg4y8voy86jukypqmypyt89pobb'),
(1, 'tinymce_height', '500'),
(1, 'tinymce_language', 'es'),
(1, 'tinymce_autosave', '1'),
(1, 'tinymce_autosave_interval', '30'),
(1, 'tinymce_max_image_mb', '3'),
(1, 'tinymce_max_image_width', '1200'),

-- ── Theme / Colores ────────────────────────────────────────────────────────
(1, 'theme_primary', '#ea580c'),
(1, 'theme_primary_light', '#fed7aa'),
(1, 'theme_primary_dark', '#c2410c'),
(1, 'theme_accent', '#1a365d'),
(1, 'theme_accent_light', '#dbeafe'),
(1, 'theme_accent_dark', '#1e3a5f'),
(1, 'theme_header_bg', '#1e293b'),
(1, 'theme_header_text', '#ffffff'),
(1, 'theme_footer_bg', '#1e293b'),
(1, 'theme_footer_text', '#ffffff'),
(1, 'theme_btn_primary_bg', '#ea580c'),
(1, 'theme_btn_primary_text', '#ffffff'),
(1, 'theme_btn_accent_bg', '#1a365d'),
(1, 'theme_btn_accent_text', '#ffffff'),
(1, 'theme_preset', 'naranja')

ON DUPLICATE KEY UPDATE valor = VALUES(valor);

-- Crear directorio para imágenes de contenido TinyMCE
-- (hacer manualmente: /public/assets/img/noticias/contenido/)
