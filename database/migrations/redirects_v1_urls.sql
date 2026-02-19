-- ═══════════════════════════════════════════════════════════════
-- Redirects 301: URLs v1 → URLs v2
-- Regalos Purranque — Migración SEO
-- ═══════════════════════════════════════════════════════════════
-- Ejecutar DESPUÉS de la migración de datos (migrate_v1_to_v2.php)
-- Nota: El script de migración ya inserta estos redirects.
--       Este archivo es referencia y backup.
-- ═══════════════════════════════════════════════════════════════

SET NAMES utf8mb4;

-- ─── Páginas estáticas v1 (.php) → v2 (URLs limpias) ────────

INSERT IGNORE INTO seo_redirects (url_origen, url_destino, tipo, activo) VALUES
('/buscar.php',     '/buscar',     301, 1),
('/mapa.php',       '/mapa',       301, 1),
('/terminos.php',   '/terminos',   301, 1),
('/privacidad.php', '/privacidad', 301, 1),
('/cookies.php',    '/cookies',    301, 1);

-- ─── Categorías: v1 usa categoria.php?slug=xxx → v2 usa /categoria/xxx ───
-- Los slugs de categorías NO cambian entre v1 y v2

INSERT IGNORE INTO seo_redirects (url_origen, url_destino, tipo, activo) VALUES
('/categoria.php?slug=gastronomia',          '/categoria/gastronomia',          301, 1),
('/categoria.php?slug=flores-y-plantas',     '/categoria/flores-y-plantas',     301, 1),
('/categoria.php?slug=belleza-y-spa',        '/categoria/belleza-y-spa',        301, 1),
('/categoria.php?slug=regalos-y-accesorios', '/categoria/regalos-y-accesorios', 301, 1),
('/categoria.php?slug=dulces-y-reposteria',  '/categoria/dulces-y-reposteria',  301, 1),
('/categoria.php?slug=moda-y-confeccion',    '/categoria/moda-y-confeccion',    301, 1),
('/categoria.php?slug=experiencias',         '/categoria/experiencias',         301, 1),
('/categoria.php?slug=decoracion',           '/categoria/decoracion',           301, 1),
('/categoria.php?slug=tecnologia-y-gadgets', '/categoria/tecnologia-y-gadgets', 301, 1),
('/categoria.php?slug=joyeria-y-accesorios', '/categoria/joyeria-y-accesorios', 301, 1);

-- ─── Fechas: v1 usa fecha.php?slug=xxx → v2 usa /fecha/xxx ──────────
-- ALGUNOS slugs cambian entre v1 y v2 (se agregan preposiciones)

-- Calendario (slugs idénticos)
INSERT IGNORE INTO seo_redirects (url_origen, url_destino, tipo, activo) VALUES
('/fecha.php?slug=san-valentin',            '/fecha/san-valentin',            301, 1),
('/fecha.php?slug=dia-de-la-mujer',         '/fecha/dia-de-la-mujer',         301, 1),
('/fecha.php?slug=dia-de-la-madre',         '/fecha/dia-de-la-madre',         301, 1),
('/fecha.php?slug=dia-del-padre',           '/fecha/dia-del-padre',           301, 1),
('/fecha.php?slug=dia-del-nino',            '/fecha/dia-del-nino',            301, 1),
('/fecha.php?slug=fiestas-patrias',         '/fecha/fiestas-patrias',         301, 1),
('/fecha.php?slug=halloween',               '/fecha/halloween',               301, 1),
('/fecha.php?slug=aniversario-purranque',   '/fecha/aniversario-purranque',   301, 1),
('/fecha.php?slug=navidad',                 '/fecha/navidad',                 301, 1),
('/fecha.php?slug=ano-nuevo',               '/fecha/ano-nuevo',               301, 1),
('/fecha.php?slug=graduaciones',            '/fecha/graduaciones',            301, 1);

-- Personal (slugs idénticos)
INSERT IGNORE INTO seo_redirects (url_origen, url_destino, tipo, activo) VALUES
('/fecha.php?slug=cumpleanos',              '/fecha/cumpleanos',              301, 1),
('/fecha.php?slug=baby-shower',             '/fecha/baby-shower',             301, 1),
('/fecha.php?slug=bautizo',                 '/fecha/bautizo',                 301, 1),
('/fecha.php?slug=primera-comunion',        '/fecha/primera-comunion',        301, 1),
('/fecha.php?slug=jubilacion',              '/fecha/jubilacion',              301, 1);

-- Personal/Calendario (slugs que CAMBIAN entre v1 y v2)
INSERT IGNORE INTO seo_redirects (url_origen, url_destino, tipo, activo) VALUES
('/fecha.php?slug=pascua',                  '/fecha/pascua-de-resurreccion',    301, 1),
('/fecha.php?slug=aniversario-matrimonio',  '/fecha/aniversario-de-matrimonio', 301, 1),
('/fecha.php?slug=cumple-mes-pololeo',      '/fecha/cumple-mes-de-pololeo',     301, 1),
('/fecha.php?slug=bodas-matrimonios',       '/fecha/bodas-y-matrimonios',       301, 1),
('/fecha.php?slug=despedida-soltero',       '/fecha/despedida-de-solteroa',     301, 1);

-- Redirects adicionales: URLs limpias v1 (si alguien las guardó) → v2
-- Algunos slugs de fechas cambiaron, crear redirects de URL limpia también
INSERT IGNORE INTO seo_redirects (url_origen, url_destino, tipo, activo) VALUES
('/fecha/pascua',                '/fecha/pascua-de-resurreccion',    301, 1),
('/fecha/aniversario-matrimonio','/fecha/aniversario-de-matrimonio', 301, 1),
('/fecha/cumple-mes-pololeo',    '/fecha/cumple-mes-de-pololeo',     301, 1),
('/fecha/bodas-matrimonios',     '/fecha/bodas-y-matrimonios',       301, 1),
('/fecha/despedida-soltero',     '/fecha/despedida-de-solteroa',     301, 1);

-- ─── Comercios: v1 usa comercio.php?slug=xxx → v2 usa /comercio/xxx ─
-- Los slugs de comercios se preservan exactos (SEO)
-- Estos se generan dinámicamente en el script de migración
-- Ejemplo:
-- INSERT IGNORE INTO seo_redirects (url_origen, url_destino, tipo, activo) VALUES
-- ('/comercio.php?slug=centro-the-beauty-spot', '/comercio/centro-the-beauty-spot', 301, 1);

-- ─── Noticias: v1 usa noticia.php?slug=xxx → v2 usa /noticia/xxx ────
-- Los slugs de noticias se preservan exactos (SEO)
-- Estos se generan dinámicamente en el script de migración
-- Ejemplo:
-- INSERT IGNORE INTO seo_redirects (url_origen, url_destino, tipo, activo) VALUES
-- ('/noticia.php?slug=san-valentin', '/noticia/san-valentin', 301, 1);

-- ═══════════════════════════════════════════════════════════════
-- NOTA: Los redirects de comercios y noticias se insertan
-- automáticamente por el script migrate_v1_to_v2.php usando
-- los slugs reales de la BD. No es necesario insertarlos aquí
-- manualmente.
-- ═══════════════════════════════════════════════════════════════

-- ─── Equivalencia de URLs (NO necesitan redirect) ────────────
-- Las siguientes URLs son idénticas en v1 y v2:
--   /                      → Home (idéntica)
--   /noticias              → Listado de noticias (idéntica)
--   /sitemap.xml           → Sitemap (idéntica)
--   /robots.txt            → Robots (idéntica)
-- ═══════════════════════════════════════════════════════════════
