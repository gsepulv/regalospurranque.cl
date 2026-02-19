-- ─────────────────────────────────────────────────────────
-- Regalos Purranque v2 — Datos iniciales (seed)
-- Ejecutar DESPUÉS de schema.sql
-- ─────────────────────────────────────────────────────────

SET NAMES utf8mb4;
USE regalos_v2;

-- ── Admin por defecto ─────────────────────────────────────
-- Password: password (cambiar en producción)
-- Para generar nuevo hash: php -r "echo password_hash('TuNuevaContraseña', PASSWORD_DEFAULT);"
INSERT INTO admin_usuarios (nombre, email, password_hash, rol, activo) VALUES
('Administrador', 'admin@regalos.purranque.info', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- ── Categorías (10) ──────────────────────────────────────
INSERT INTO categorias (nombre, slug, descripcion, icono, color, orden, activo) VALUES
('Gastronomía',           'gastronomia',           'Restaurantes, cafeterías, pastelerías y productos gourmet de Purranque',           '🍽️', '#e11d48',  1, 1),
('Flores y Plantas',      'flores-y-plantas',       'Floristerías, arreglos florales, plantas decorativas y jardines',                  '🌹', '#f472b6',  2, 1),
('Belleza y Spa',         'belleza-y-spa',          'Peluquerías, spa, tratamientos de belleza y cuidado personal',                     '💆', '#ec4899',  3, 1),
('Regalos y Accesorios',  'regalos-y-accesorios',   'Tiendas de regalos, souvenirs, accesorios y artículos especiales',                '🎁', '#7c3aed',  4, 1),
('Dulces y Repostería',   'dulces-y-reposteria',    'Pastelerías, chocolaterías, dulcerías y repostería artesanal',                    '🧁', '#d97706',  5, 1),
('Moda y Confección',     'moda-y-confeccion',      'Tiendas de ropa, calzado, confección a medida y moda local',                      '👗', '#2563eb',  6, 1),
('Experiencias',          'experiencias',           'Actividades, talleres, paseos y experiencias únicas para regalar',                '🎭', '#059669',  7, 1),
('Decoración',            'decoracion',             'Artículos de decoración, hogar, ambientación y diseño interior',                  '🎈', '#0891b2',  8, 1),
('Tecnología y Gadgets',  'tecnologia-y-gadgets',   'Electrónica, celulares, gadgets y accesorios tecnológicos',                       '📱', '#475569',  9, 1),
('Joyería y Accesorios',  'joyeria-y-accesorios',   'Joyerías, relojerías, bijouterie y accesorios de moda',                           '💎', '#b45309', 10, 1);

-- ── Fechas Especiales — Celebraciones Personales (9) ─────
INSERT INTO fechas_especiales (nombre, slug, descripcion, tipo, icono, fecha_inicio, fecha_fin, recurrente, activo) VALUES
('Cumpleaños',               'cumpleanos',               'Regalos de cumpleaños para todo el año',                                     'personal', '🎂', NULL, NULL, 1, 1),
('Aniversario de Matrimonio','aniversario-de-matrimonio', 'Celebra el amor que perdura',                                                'personal', '💍', NULL, NULL, 1, 1),
('Cumple Mes de Pololeo',    'cumple-mes-de-pololeo',     'Cada mes juntos merece celebrarse',                                          'personal', '💑', NULL, NULL, 1, 1),
('Bodas y Matrimonios',      'bodas-y-matrimonios',       'Regalos para los novios y detalles para invitados',                           'personal', '👰', NULL, NULL, 1, 1),
('Baby Shower',              'baby-shower',               'Regalos para la futura mamá y el bebé',                                      'personal', '🍼', NULL, NULL, 1, 1),
('Bautizo',                  'bautizo',                   'Regalos y recuerdos para celebrar el bautizo',                                'personal', '⛪', NULL, NULL, 1, 1),
('Despedida de Soltero/a',   'despedida-de-solteroa',     'Ideas para la última fiesta antes del gran paso',                             'personal', '🥂', NULL, NULL, 1, 1),
('Primera Comunión',         'primera-comunion',          'Regalos significativos para este sacramento',                                 'personal', '✝️', NULL, NULL, 1, 1),
('Jubilación',               'jubilacion',               'Celebra una vida de trabajo con un regalo memorable',                          'personal', '🎊', NULL, NULL, 1, 1);

-- ── Fechas Especiales — Calendario (12) ──────────────────
INSERT INTO fechas_especiales (nombre, slug, descripcion, tipo, icono, fecha_inicio, fecha_fin, recurrente, activo) VALUES
('San Valentín',             'san-valentin',             'Día de los enamorados — 14 de febrero',                                       'calendario', '💕', '2026-02-14', '2026-02-14', 1, 1),
('Día de la Mujer',          'dia-de-la-mujer',          'Día Internacional de la Mujer — 8 de marzo',                                  'calendario', '👩', '2026-03-08', '2026-03-08', 1, 1),
('Pascua de Resurrección',   'pascua-de-resurreccion',   'Celebración de Pascua de Resurrección',                                       'calendario', '🐣', '2026-04-05', '2026-04-05', 1, 1),
('Día de la Madre',          'dia-de-la-madre',          'Celebración del Día de la Madre en Chile',                                    'calendario', '👩‍👧', '2026-05-10', '2026-05-10', 1, 1),
('Día del Padre',            'dia-del-padre',            'Celebración del Día del Padre en Chile',                                      'calendario', '👨‍👧', '2026-06-21', '2026-06-21', 1, 1),
('Día del Niño',             'dia-del-nino',             'Día del Niño en Chile — agosto',                                              'calendario', '👶', '2026-08-09', '2026-08-09', 1, 1),
('Fiestas Patrias',          'fiestas-patrias',          'Fiestas Patrias de Chile — 18 y 19 de septiembre',                             'calendario', '🇨🇱', '2026-09-18', '2026-09-19', 1, 1),
('Halloween',                'halloween',                'Noche de Halloween — 31 de octubre',                                           'calendario', '🎃', '2026-10-31', '2026-10-31', 1, 1),
('Aniversario Purranque',    'aniversario-purranque',    'Aniversario de la comuna de Purranque — 17 de abril',                          'calendario', '🏘️', '2026-04-17', '2026-04-17', 1, 1),
('Navidad',                  'navidad',                  'Navidad — 25 de diciembre',                                                   'calendario', '🎄', '2026-12-25', '2026-12-25', 1, 1),
('Año Nuevo',                'ano-nuevo',                'Año Nuevo — 1 de enero',                                                      'calendario', '🎆', '2026-01-01', '2026-01-01', 1, 1),
('Graduaciones',             'graduaciones',             'Temporada de graduaciones — diciembre',                                        'calendario', '🎓', '2026-12-01', '2026-12-31', 1, 1);

-- ── Fechas Especiales — Eventos Comerciales (5) ──────────
INSERT INTO fechas_especiales (nombre, slug, descripcion, tipo, icono, fecha_inicio, fecha_fin, recurrente, activo) VALUES
('CyberDay Chile',     'cyberday-chile',     'Los mejores descuentos online. Comercios de Purranque con ofertas especiales',              'comercial', '💻', '2026-05-25', '2026-05-27', 1, 1),
('CyberMonday Chile',  'cybermonday-chile',   'Lunes de ofertas imperdibles. Descuentos exclusivos en comercios locales',                  'comercial', '🛒', '2026-10-05', '2026-10-07', 1, 1),
('Travel Sale',        'travel-sale',         'Ofertas en turismo y experiencias. Aprovecha los descuentos de temporada',                  'comercial', '✈️', '2026-03-16', '2026-03-20', 1, 1),
('Black Friday Chile', 'black-friday-chile',  'El viernes de descuentos más grande del año llega a Purranque',                             'comercial', '🖤', '2026-11-27', '2026-11-29', 1, 1),
('Mega Sale',          'mega-sale',           'Grandes liquidaciones de temporada en comercios de Purranque',                              'comercial', '🔥', '2026-01-05', '2026-01-18', 1, 1);

-- ── Configuración general ─────────────────────────────────
INSERT INTO configuracion (clave, valor, grupo) VALUES
('sitio_nombre',      'Regalos Purranque',                                       'general'),
('sitio_descripcion', 'Directorio comercial de Purranque, Chile',                'general'),
('sitio_email',       'contacto@purranque.info',                                 'general'),
('sitio_telefono',    '',                                                        'general'),
('sitio_direccion',   'Purranque, Región de Los Lagos, Chile',                   'general'),
('analytics_activo',  '1',                                                       'analytics'),
('resenas_moderacion','1',                                                       'resenas'),
('resenas_minimo',    '10',                                                      'resenas'),
('backup_frecuencia', 'semanal',                                                 'backup'),
('backup_retener',    '5',                                                       'backup');

-- ── Configuración SEO ─────────────────────────────────────
INSERT INTO seo_config (clave, valor) VALUES
('home_title',       'Regalos Purranque — Directorio Comercial de Purranque, Chile'),
('home_description', 'Encuentra los mejores comercios, ofertas y servicios en Purranque. Tu directorio comercial local.'),
('home_keywords',    'comercios purranque, tiendas purranque, ofertas purranque, directorio comercial purranque'),
('robots_txt',       'User-agent: *\nAllow: /\nSitemap: https://regalos.purranque.info/sitemap.xml'),
('google_analytics', ''),
('google_search_console', '');

-- ── Configuración mantenimiento ───────────────────────────
INSERT INTO configuracion_mantenimiento (clave, valor) VALUES
('activo',  '0'),
('mensaje', 'Estamos realizando mejoras en el sitio. Volvemos pronto.'),
('ip_permitidas', '');
