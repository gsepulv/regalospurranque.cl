-- ============================================================================
-- Fase 8b: Ubicacion flexible para Perfiles y Share
-- Ejecutar en MySQL 8.4 compatible con only_full_group_by
-- ============================================================================

-- SISTEMA A: Perfiles — En que PAGINAS
INSERT IGNORE INTO redes_sociales_config (site_id, clave, valor) VALUES
(1, 'profiles_page_all', '0'),
(1, 'profiles_page_home', '1'),
(1, 'profiles_page_comercio', '1'),
(1, 'profiles_page_categoria', '0'),
(1, 'profiles_page_fecha', '0'),
(1, 'profiles_page_noticias_list', '0'),
(1, 'profiles_page_noticia', '1'),
(1, 'profiles_page_mapa', '0'),
(1, 'profiles_page_buscar', '0'),
(1, 'profiles_page_contacto', '1'),
(1, 'profiles_page_landing', '1'),
(1, 'profiles_page_default', '1');

-- SISTEMA A: Perfiles — En que POSICION
INSERT IGNORE INTO redes_sociales_config (site_id, clave, valor) VALUES
(1, 'profiles_pos_header', '0'),
(1, 'profiles_pos_below_header', '0'),
(1, 'profiles_pos_sidebar', '0'),
(1, 'profiles_pos_after_title', '0'),
(1, 'profiles_pos_before_footer', '0'),
(1, 'profiles_pos_footer', '1'),
(1, 'profiles_pos_floating', '0');

-- SISTEMA A: Perfiles — Estilo visual
INSERT IGNORE INTO redes_sociales_config (site_id, clave, valor) VALUES
(1, 'profiles_style', 'circular_color'),
(1, 'profiles_size', 'medium'),
(1, 'profiles_align', 'left'),
(1, 'profiles_spacing', 'normal');

-- SISTEMA B: Share — En que PAGINAS
INSERT IGNORE INTO redes_sociales_config (site_id, clave, valor) VALUES
(1, 'share_page_home', '0'),
(1, 'share_page_comercio', '1'),
(1, 'share_page_categoria', '0'),
(1, 'share_page_fecha', '1'),
(1, 'share_page_noticia', '1'),
(1, 'share_page_mapa', '0'),
(1, 'share_page_default', '1');

-- SISTEMA B: Share — En que POSICION
INSERT IGNORE INTO redes_sociales_config (site_id, clave, valor) VALUES
(1, 'share_pos_above_content', '0'),
(1, 'share_pos_below_content', '1'),
(1, 'share_pos_sidebar', '0'),
(1, 'share_pos_floating_bar', '0'),
(1, 'share_pos_floating_circle', '1'),
(1, 'share_pos_in_cards', '0');
