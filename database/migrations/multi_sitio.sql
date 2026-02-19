-- ============================================================================
-- Multi-sitio: Arquitectura para múltiples directorios comerciales
-- Fase 7, Módulo 3
-- Compatible con MySQL 8.x (no usa IF NOT EXISTS en ALTER)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla: sitios
-- Cada sitio representa un directorio comercial independiente
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sitios` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(170) NOT NULL,
    `dominio` VARCHAR(255) DEFAULT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `favicon` VARCHAR(255) DEFAULT NULL,
    `color_primario` VARCHAR(7) NOT NULL DEFAULT '#2563eb',
    `color_secundario` VARCHAR(7) NOT NULL DEFAULT '#1e40af',
    `ciudad` VARCHAR(100) NOT NULL DEFAULT 'Purranque',
    `lat` DECIMAL(10,8) DEFAULT -40.91305000,
    `lng` DECIMAL(11,8) DEFAULT -73.15913000,
    `zoom` TINYINT NOT NULL DEFAULT 15,
    `email_contacto` VARCHAR(150) DEFAULT NULL,
    `telefono` VARCHAR(20) DEFAULT NULL,
    `redes_sociales` JSON DEFAULT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_sitios_slug` (`slug`),
    UNIQUE KEY `uk_sitios_dominio` (`dominio`),
    INDEX `idx_sitios_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Insertar sitio por defecto (Purranque)
-- ----------------------------------------------------------------------------
INSERT INTO `sitios` (`id`, `nombre`, `slug`, `dominio`, `descripcion`, `ciudad`, `lat`, `lng`, `zoom`, `activo`)
VALUES (1, 'Regalos Purranque', 'purranque', 'regalos.purranque.info',
        'Directorio comercial de Purranque, Chile', 'Purranque',
        -40.91305000, -73.15913000, 15, 1)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- ----------------------------------------------------------------------------
-- Procedimiento para agregar columnas y índices de forma segura
-- MySQL 8.x no soporta ADD COLUMN IF NOT EXISTS
-- ----------------------------------------------------------------------------
DELIMITER //

CREATE PROCEDURE IF NOT EXISTS `add_site_id_columns`()
BEGIN
    -- Helper: verificar si columna existe
    DECLARE col_exists INT DEFAULT 0;

    -- ── comercios ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'comercios' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `comercios` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
        ALTER TABLE `comercios` ADD INDEX `idx_comercios_site` (`site_id`);
    END IF;

    -- ── categorias ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categorias' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `categorias` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
        ALTER TABLE `categorias` ADD INDEX `idx_categorias_site` (`site_id`);
    END IF;

    -- ── fechas_especiales ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fechas_especiales' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `fechas_especiales` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
        ALTER TABLE `fechas_especiales` ADD INDEX `idx_fechas_site` (`site_id`);
    END IF;

    -- ── noticias ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'noticias' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `noticias` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
        ALTER TABLE `noticias` ADD INDEX `idx_noticias_site` (`site_id`);
    END IF;

    -- ── banners ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'banners' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `banners` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
        ALTER TABLE `banners` ADD INDEX `idx_banners_site` (`site_id`);
    END IF;

    -- ── resenas ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'resenas' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `resenas` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
        ALTER TABLE `resenas` ADD INDEX `idx_resenas_site` (`site_id`);
    END IF;

    -- ── configuracion ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'configuracion' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `configuracion` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `grupo`;
    END IF;

    -- ── seo_config ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'seo_config' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `seo_config` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `clave`;
    END IF;

    -- ── seo_redirects ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'seo_redirects' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `seo_redirects` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
        ALTER TABLE `seo_redirects` ADD INDEX `idx_redirects_site` (`site_id`);
    END IF;

    -- ── analytics_diario ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'analytics_diario' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `analytics_diario` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
    END IF;

    -- ── visitas_log ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'visitas_log' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `visitas_log` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
        ALTER TABLE `visitas_log` ADD INDEX `idx_visitas_site` (`site_id`);
    END IF;

    -- ── notificaciones_log ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notificaciones_log' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `notificaciones_log` ADD COLUMN `site_id` INT NOT NULL DEFAULT 1 AFTER `id`;
        ALTER TABLE `notificaciones_log` ADD INDEX `idx_notif_site` (`site_id`);
    END IF;

    -- ── admin_usuarios: agregar site_id y modificar ENUM de rol ──
    SELECT COUNT(*) INTO col_exists FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin_usuarios' AND COLUMN_NAME = 'site_id';
    IF col_exists = 0 THEN
        ALTER TABLE `admin_usuarios` ADD COLUMN `site_id` INT DEFAULT NULL AFTER `rol`;
    END IF;

    -- Modificar ENUM de rol para incluir superadmin
    ALTER TABLE `admin_usuarios`
        MODIFY COLUMN `rol` ENUM('superadmin','admin','editor','comerciante') NOT NULL DEFAULT 'editor';

    -- Asignar sitio 1 a usuarios existentes sin sitio
    UPDATE `admin_usuarios` SET `site_id` = 1 WHERE `site_id` IS NULL AND `rol` != 'superadmin';

END //

DELIMITER ;

-- Ejecutar el procedimiento
CALL `add_site_id_columns`();

-- Limpiar el procedimiento temporal
DROP PROCEDURE IF EXISTS `add_site_id_columns`;
