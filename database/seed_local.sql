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

-- ── Categorías ────────────────────────────────────────────
INSERT INTO categorias (nombre, slug, descripcion, icono, color, orden, activo) VALUES
('Restaurantes',       'restaurantes',       'Restaurantes y locales de comida',               '🍽️', '#e11d48', 1, 1),
('Supermercados',      'supermercados',       'Supermercados y minimarkets',                    '🛒', '#059669', 2, 1),
('Ropa y Calzado',     'ropa-y-calzado',      'Tiendas de ropa, zapaterías y accesorios',       '👕', '#7c3aed', 3, 1),
('Tecnología',         'tecnologia',          'Electrónica, computación y celulares',            '💻', '#2563eb', 4, 1),
('Salud',              'salud',               'Farmacias, consultorios y profesionales de salud','💊', '#dc2626', 5, 1),
('Ferretería',         'ferreteria',          'Ferreterías y materiales de construcción',        '🔨', '#d97706', 6, 1),
('Servicios',          'servicios',           'Servicios profesionales y técnicos',              '🔧', '#0891b2', 7, 1),
('Educación',          'educacion',           'Colegios, cursos y capacitaciones',               '📚', '#4f46e5', 8, 1),
('Belleza',            'belleza',             'Peluquerías, barberías y estética',               '💇', '#ec4899', 9, 1),
('Automotriz',         'automotriz',          'Talleres mecánicos, repuestos y servicios automotrices', '🚗', '#475569', 10, 1),
('Hogar y Jardín',     'hogar-y-jardin',      'Muebles, decoración y jardinería',               '🏡', '#16a34a', 11, 1),
('Entretenimiento',    'entretenimiento',     'Diversión, juegos y actividades recreativas',     '🎮', '#8b5cf6', 12, 1);

-- ── Fechas Especiales ─────────────────────────────────────
INSERT INTO fechas_especiales (nombre, slug, descripcion, icono, fecha_inicio, fecha_fin, recurrente, activo) VALUES
('San Valentín',        'san-valentin',        'Día de los enamorados',                     '❤️',  '2025-02-14', '2025-02-14', 1, 1),
('Día de la Madre',     'dia-de-la-madre',     'Celebración del día de la madre en Chile',   '🌸',  '2025-05-11', '2025-05-11', 1, 1),
('Día del Padre',       'dia-del-padre',       'Celebración del día del padre en Chile',     '👔',  '2025-06-15', '2025-06-15', 1, 1),
('Fiestas Patrias',     'fiestas-patrias',     'Celebración de las fiestas patrias chilenas','🇨🇱', '2025-09-18', '2025-09-19', 1, 1),
('Navidad',             'navidad',             'Celebración de Navidad',                     '🎄',  '2025-12-25', '2025-12-25', 1, 1),
('Año Nuevo',           'ano-nuevo',           'Celebración de Año Nuevo',                   '🎆',  '2025-01-01', '2025-01-01', 1, 1),
('Black Friday',        'black-friday',        'Ofertas y descuentos especiales',             '🏷️',  '2025-11-28', '2025-11-28', 1, 1),
('Cyber Monday',        'cyber-monday',        'Ofertas online especiales',                   '💻',  '2025-12-01', '2025-12-01', 1, 1),
('Día del Niño',        'dia-del-nino',        'Celebración del día del niño en Chile',      '🧒',  '2025-08-10', '2025-08-10', 1, 1),
('Aniversario Purranque','aniversario-purranque','Aniversario de la comuna de Purranque',     '🎉',  '2025-01-06', '2025-01-06', 1, 1);

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
