-- ============================================================================
-- Base de Datos: purranque_regalos_purranque
-- Proyecto: Regalos Purranque v2 - Directorio Comercial
-- Descripcion: Esquema completo con las 30 tablas del sistema
-- Motor: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- SECCION 1: TABLAS PRINCIPALES (CORE)
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla 1: admin_usuarios
-- Usuarios del panel de administracion (admins, editores, comerciantes)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_usuarios` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `telefono` VARCHAR(20) DEFAULT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `rol` ENUM('superadmin','admin','editor','comerciante') NOT NULL DEFAULT 'editor',
    `site_id` INT DEFAULT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `reset_token` VARCHAR(64) DEFAULT NULL,
    `reset_expira` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_admin_usuarios_email` (`email`),
    INDEX `idx_admin_usuarios_rol` (`rol`),
    INDEX `idx_admin_usuarios_activo` (`activo`),
    INDEX `idx_reset_token` (`reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 2: sesiones_admin
-- Control de sesiones activas de los usuarios del panel
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sesiones_admin` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `usuario_id` INT NOT NULL,
    `token` VARCHAR(128) NOT NULL,
    `ip` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `expira` DATETIME NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_sesiones_token` (`token`),
    INDEX `idx_sesiones_usuario` (`usuario_id`),
    INDEX `idx_sesiones_expira` (`expira`),
    CONSTRAINT `fk_sesiones_usuario` FOREIGN KEY (`usuario_id`)
        REFERENCES `admin_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 3: admin_log
-- Registro de auditor&iacute;a de todas las acciones realizadas en el panel
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `usuario_id` INT DEFAULT NULL,
    `usuario_nombre` VARCHAR(100) DEFAULT NULL,
    `modulo` VARCHAR(50) NOT NULL,
    `accion` VARCHAR(50) NOT NULL,
    `entidad_tipo` VARCHAR(50) DEFAULT NULL,
    `entidad_id` INT DEFAULT NULL,
    `detalle` TEXT DEFAULT NULL,
    `datos_antes` JSON DEFAULT NULL,
    `datos_despues` JSON DEFAULT NULL,
    `ip` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_admin_log_modulo_fecha` (`modulo`, `created_at`),
    INDEX `idx_admin_log_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 4: configuracion
-- Parametros generales del sistema (clave-valor agrupados)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracion` (
    `clave` VARCHAR(100) NOT NULL,
    `valor` TEXT DEFAULT NULL,
    `grupo` VARCHAR(50) NOT NULL DEFAULT 'general',
    PRIMARY KEY (`clave`),
    INDEX `idx_configuracion_grupo` (`grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECCION 2: COMERCIOS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla 5: comercios
-- Directorio principal de comercios registrados en la plataforma
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comercios` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `site_id` INT NOT NULL DEFAULT 1,
    `nombre` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(170) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `telefono` VARCHAR(20) DEFAULT NULL,
    `whatsapp` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `sitio_web` VARCHAR(255) DEFAULT NULL,
    `facebook` VARCHAR(300) DEFAULT NULL,
    `instagram` VARCHAR(300) DEFAULT NULL,
    `tiktok` VARCHAR(300) DEFAULT NULL,
    `youtube` VARCHAR(300) DEFAULT NULL,
    `x_twitter` VARCHAR(300) DEFAULT NULL,
    `linkedin` VARCHAR(300) DEFAULT NULL,
    `telegram` VARCHAR(300) DEFAULT NULL,
    `pinterest` VARCHAR(300) DEFAULT NULL,
    `direccion` VARCHAR(255) DEFAULT NULL,
    `lat` DECIMAL(10,8) DEFAULT NULL,
    `lng` DECIMAL(11,8) DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `portada` VARCHAR(255) DEFAULT NULL,
    `plan` ENUM('freemium','basico','premium','sponsor','banner') NOT NULL DEFAULT 'freemium',
    `plan_precio` INT UNSIGNED DEFAULT NULL,
    `plan_inicio` DATE DEFAULT NULL,
    `plan_fin` DATE DEFAULT NULL,
    `max_fotos` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `registrado_por` INT DEFAULT NULL,
    `destacado` TINYINT(1) NOT NULL DEFAULT 0,
    `validado` TINYINT(1) NOT NULL DEFAULT 0,
    `validado_fecha` DATETIME DEFAULT NULL,
    `validado_notas` VARCHAR(500) DEFAULT NULL,
    `visitas` INT NOT NULL DEFAULT 0,
    `whatsapp_clicks` INT NOT NULL DEFAULT 0,
    `seo_titulo` VARCHAR(160) DEFAULT NULL,
    `seo_descripcion` VARCHAR(320) DEFAULT NULL,
    `seo_keywords` VARCHAR(255) DEFAULT NULL,
    `razon_social` VARCHAR(200) DEFAULT NULL,
    `rut_empresa` VARCHAR(15) DEFAULT NULL,
    `giro` VARCHAR(200) DEFAULT NULL,
    `direccion_tributaria` VARCHAR(300) DEFAULT NULL,
    `comuna_tributaria` VARCHAR(100) DEFAULT NULL,
    `contacto_nombre` VARCHAR(150) DEFAULT NULL,
    `contacto_rut` VARCHAR(15) DEFAULT NULL,
    `contacto_telefono` VARCHAR(20) DEFAULT NULL,
    `contacto_email` VARCHAR(200) DEFAULT NULL,
    `contrato_inicio` DATE DEFAULT NULL,
    `contrato_monto` INT UNSIGNED DEFAULT NULL,
    `metodo_pago` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_comercios_slug` (`slug`),
    INDEX `idx_comercios_activo_destacado` (`activo`, `destacado`),
    INDEX `idx_comercios_plan` (`plan`),
    INDEX `idx_comercios_slug` (`slug`),
    INDEX `idx_comercios_site` (`site_id`),
    INDEX `idx_comercios_registrado_por` (`registrado_por`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 6: categorias
-- Categorias para clasificar comercios y noticias
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorias` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `icono` VARCHAR(50) DEFAULT NULL,
    `imagen` VARCHAR(255) DEFAULT NULL,
    `color` VARCHAR(7) NOT NULL DEFAULT '#2563eb',
    `orden` INT NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_categorias_slug` (`slug`),
    INDEX `idx_categorias_activo_orden` (`activo`, `orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 7: comercio_categoria
-- Relacion muchos-a-muchos entre comercios y categorias
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comercio_categoria` (
    `comercio_id` INT NOT NULL,
    `categoria_id` INT NOT NULL,
    `es_principal` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`comercio_id`, `categoria_id`),
    INDEX `idx_comercio_categoria_categoria` (`categoria_id`),
    CONSTRAINT `fk_comcat_comercio` FOREIGN KEY (`comercio_id`)
        REFERENCES `comercios` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comcat_categoria` FOREIGN KEY (`categoria_id`)
        REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 8: fechas_especiales
-- Fechas especiales / eventos del calendario comercial
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fechas_especiales` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(170) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `tipo` ENUM('personal','calendario','comercial') NOT NULL DEFAULT 'calendario',
    `icono` VARCHAR(50) DEFAULT NULL,
    `imagen` VARCHAR(255) DEFAULT NULL,
    `fecha_inicio` DATE DEFAULT NULL,
    `fecha_fin` DATE DEFAULT NULL,
    `recurrente` TINYINT(1) NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_fechas_especiales_slug` (`slug`),
    INDEX `idx_fechas_activo` (`activo`),
    INDEX `idx_fechas_tipo` (`tipo`),
    INDEX `idx_fechas_rango` (`fecha_inicio`, `fecha_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 9: comercio_fecha
-- Relacion muchos-a-muchos entre comercios y fechas especiales
-- Incluye datos de oferta especial por comercio y fecha
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comercio_fecha` (
    `comercio_id` INT NOT NULL,
    `fecha_id` INT NOT NULL,
    `oferta_especial` TEXT DEFAULT NULL,
    `precio_desde` DECIMAL(10,2) DEFAULT NULL,
    `precio_hasta` DECIMAL(10,2) DEFAULT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`comercio_id`, `fecha_id`),
    INDEX `idx_comercio_fecha_fecha` (`fecha_id`),
    CONSTRAINT `fk_comfecha_comercio` FOREIGN KEY (`comercio_id`)
        REFERENCES `comercios` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_comfecha_fecha` FOREIGN KEY (`fecha_id`)
        REFERENCES `fechas_especiales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 10: comercio_fotos
-- Galeria de fotos de cada comercio
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comercio_fotos` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `comercio_id` INT NOT NULL,
    `ruta` VARCHAR(255) NOT NULL,
    `ruta_thumb` VARCHAR(255) DEFAULT NULL,
    `titulo` VARCHAR(150) DEFAULT NULL,
    `orden` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_comercio_fotos_comercio` (`comercio_id`),
    INDEX `idx_comercio_fotos_orden` (`comercio_id`, `orden`),
    CONSTRAINT `fk_fotos_comercio` FOREIGN KEY (`comercio_id`)
        REFERENCES `comercios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 11: comercio_horarios
-- Horarios de atencion por dia de la semana para cada comercio
-- dia: 0=Domingo, 1=Lunes, 2=Martes, 3=Miercoles, 4=Jueves, 5=Viernes, 6=Sabado
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comercio_horarios` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `comercio_id` INT NOT NULL,
    `dia` TINYINT NOT NULL COMMENT '0=Domingo, 1=Lunes, 2=Martes, 3=Miercoles, 4=Jueves, 5=Viernes, 6=Sabado',
    `hora_apertura` TIME DEFAULT NULL,
    `hora_cierre` TIME DEFAULT NULL,
    `cerrado` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_comercio_horarios_dia` (`comercio_id`, `dia`),
    CONSTRAINT `fk_horarios_comercio` FOREIGN KEY (`comercio_id`)
        REFERENCES `comercios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECCION 3: CONTENIDO
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla 12: noticias
-- Articulos y noticias del directorio comercial
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `noticias` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `titulo` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(220) NOT NULL,
    `contenido` LONGTEXT DEFAULT NULL,
    `extracto` VARCHAR(500) DEFAULT NULL,
    `imagen` VARCHAR(255) DEFAULT NULL,
    `autor` VARCHAR(100) DEFAULT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `destacada` TINYINT(1) NOT NULL DEFAULT 0,
    `seo_titulo` VARCHAR(160) DEFAULT NULL,
    `seo_descripcion` VARCHAR(320) DEFAULT NULL,
    `seo_keywords` VARCHAR(255) DEFAULT NULL,
    `seo_imagen_og` VARCHAR(255) DEFAULT NULL,
    `seo_noindex` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_publicacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_noticias_slug` (`slug`),
    INDEX `idx_noticias_activo_destacada` (`activo`, `destacada`),
    INDEX `idx_noticias_fecha_pub` (`fecha_publicacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 13: noticia_categoria
-- Relacion muchos-a-muchos entre noticias y categorias
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `noticia_categoria` (
    `noticia_id` INT NOT NULL,
    `categoria_id` INT NOT NULL,
    PRIMARY KEY (`noticia_id`, `categoria_id`),
    INDEX `idx_noticia_categoria_categoria` (`categoria_id`),
    CONSTRAINT `fk_notcat_noticia` FOREIGN KEY (`noticia_id`)
        REFERENCES `noticias` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notcat_categoria` FOREIGN KEY (`categoria_id`)
        REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 14: noticia_fecha
-- Relacion muchos-a-muchos entre noticias y fechas especiales
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `noticia_fecha` (
    `noticia_id` INT NOT NULL,
    `fecha_id` INT NOT NULL,
    PRIMARY KEY (`noticia_id`, `fecha_id`),
    INDEX `idx_noticia_fecha_fecha` (`fecha_id`),
    CONSTRAINT `fk_notfecha_noticia` FOREIGN KEY (`noticia_id`)
        REFERENCES `noticias` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notfecha_fecha` FOREIGN KEY (`fecha_id`)
        REFERENCES `fechas_especiales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 15: banners
-- Banners publicitarios en distintas posiciones del sitio
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `banners` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `titulo` VARCHAR(150) DEFAULT NULL,
    `tipo` ENUM('hero','sidebar','entre_comercios','footer') NOT NULL DEFAULT 'sidebar',
    `imagen` VARCHAR(255) NOT NULL,
    `url` VARCHAR(500) DEFAULT NULL,
    `posicion` VARCHAR(50) DEFAULT NULL,
    `comercio_id` INT DEFAULT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `clicks` INT NOT NULL DEFAULT 0,
    `impresiones` INT NOT NULL DEFAULT 0,
    `fecha_inicio` DATE DEFAULT NULL,
    `fecha_fin` DATE DEFAULT NULL,
    `orden` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_banners_tipo_activo` (`tipo`, `activo`),
    INDEX `idx_banners_comercio` (`comercio_id`),
    INDEX `idx_banners_fechas` (`fecha_inicio`, `fecha_fin`),
    CONSTRAINT `fk_banners_comercio` FOREIGN KEY (`comercio_id`)
        REFERENCES `comercios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECCION 4: RESENAS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla 16: resenas
-- Resenas y calificaciones de los usuarios hacia los comercios
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resenas` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `comercio_id` INT NOT NULL,
    `nombre_autor` VARCHAR(100) NOT NULL,
    `email_autor` VARCHAR(150) DEFAULT NULL,
    `calificacion` TINYINT NOT NULL,
    `comentario` TEXT DEFAULT NULL,
    `estado` ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
    `respuesta_comercio` TEXT DEFAULT NULL,
    `fecha_respuesta` DATETIME DEFAULT NULL,
    `ip` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_resenas_comercio` (`comercio_id`),
    INDEX `idx_resenas_estado` (`estado`),
    INDEX `idx_resenas_calificacion` (`calificacion`),
    CONSTRAINT `fk_resenas_comercio` FOREIGN KEY (`comercio_id`)
        REFERENCES `comercios` (`id`) ON DELETE CASCADE,
    CONSTRAINT `chk_resenas_calificacion` CHECK (`calificacion` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 17: resenas_reportes
-- Reportes de resenas inapropiadas realizados por usuarios
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `resenas_reportes` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `resena_id` INT NOT NULL,
    `motivo` VARCHAR(100) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `ip` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_reportes_resena` (`resena_id`),
    CONSTRAINT `fk_reportes_resena` FOREIGN KEY (`resena_id`)
        REFERENCES `resenas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECCION 5: SEO Y ANALYTICS
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla 18: seo_config
-- Configuracion global de SEO (meta tags, Open Graph, etc.)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `seo_config` (
    `clave` VARCHAR(100) NOT NULL,
    `valor` TEXT DEFAULT NULL,
    PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 19: seo_redirects
-- Redirecciones 301/302 personalizadas para SEO
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `seo_redirects` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `url_origen` VARCHAR(500) NOT NULL,
    `url_destino` VARCHAR(500) NOT NULL,
    `tipo` SMALLINT NOT NULL DEFAULT 301,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `hits` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_redirects_origen` (`url_origen`(191)),
    INDEX `idx_redirects_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 20: visitas_log
-- Registro detallado de visitas a paginas y comercios
-- Usa BIGINT para soportar alto volumen de registros
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `visitas_log` (
    `id` BIGINT NOT NULL AUTO_INCREMENT,
    `comercio_id` INT DEFAULT NULL,
    `pagina` VARCHAR(500) DEFAULT NULL,
    `tipo` VARCHAR(50) DEFAULT NULL,
    `ip` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `referrer` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_visitas_comercio` (`comercio_id`),
    INDEX `idx_visitas_fecha` (`created_at`),
    INDEX `idx_visitas_pagina` (`pagina`(191)),
    CONSTRAINT `fk_visitas_comercio` FOREIGN KEY (`comercio_id`)
        REFERENCES `comercios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 21: analytics_diario
-- Resumen diario agregado de visitas por pagina
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `analytics_diario` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `fecha` DATE NOT NULL,
    `pagina` VARCHAR(500) DEFAULT NULL,
    `visitas` INT NOT NULL DEFAULT 0,
    `visitantes_unicos` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_analytics_fecha_pagina` (`fecha`, `pagina`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- SECCION 6: SISTEMA
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabla 22: configuracion_mantenimiento
-- Configuracion del modo mantenimiento y parametros del sistema
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `configuracion_mantenimiento` (
    `clave` VARCHAR(100) NOT NULL,
    `valor` TEXT DEFAULT NULL,
    PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Planes: configuración de planes comerciales
-- ============================================================================
CREATE TABLE IF NOT EXISTS `planes_config` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(20) NOT NULL COMMENT 'freemium|basico|premium|sponsor|banner',
    `nombre` VARCHAR(50) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `precio_intro` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Precio introductorio CLP',
    `precio_regular` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Precio regular CLP',
    `duracion_dias` SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Duracion del plan en dias',
    `max_fotos` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `max_redes` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=solo 1 red, 99=todas',
    `tiene_mapa` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=boton GMaps, 1=mapa integrado',
    `tiene_horarios` TINYINT(1) NOT NULL DEFAULT 0,
    `tiene_sello` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Sello verificado del plan',
    `tiene_reporte` TINYINT(1) NOT NULL DEFAULT 0,
    `posicion` ENUM('normal','prioritaria','primero') NOT NULL DEFAULT 'normal',
    `max_cupos` INT UNSIGNED DEFAULT NULL COMMENT 'NULL=ilimitado',
    `max_cupos_categoria` INT UNSIGNED DEFAULT NULL COMMENT 'Para sponsors: max por categoria',
    `color` VARCHAR(7) DEFAULT '#6B7280',
    `icono` VARCHAR(10) DEFAULT NULL,
    `orden` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Renovaciones de planes (solicitudes de comerciantes)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `comercio_renovaciones` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `comercio_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `plan_actual` VARCHAR(20) NOT NULL,
    `plan_solicitado` VARCHAR(20) NOT NULL,
    `estado` ENUM('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
    `motivo_rechazo` TEXT DEFAULT NULL,
    `aprobado_por` INT DEFAULT NULL,
    `monto` DECIMAL(10,2) DEFAULT NULL,
    `comprobante_pago` VARCHAR(255) DEFAULT NULL,
    `fecha_pago` DATE DEFAULT NULL,
    `metodo_pago` ENUM('transferencia','efectivo','webpay','flow','mercadopago') DEFAULT NULL,
    `notas_admin` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_renovacion_comercio` (`comercio_id`),
    INDEX `idx_renovacion_estado` (`estado`),
    INDEX `idx_renovacion_fecha` (`created_at`),
    CONSTRAINT `fk_renovacion_comercio` FOREIGN KEY (`comercio_id`)
        REFERENCES `comercios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Cambios pendientes de comerciantes
-- ============================================================================
CREATE TABLE IF NOT EXISTS `comercio_cambios_pendientes` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `comercio_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `cambios_json` JSON NOT NULL COMMENT 'JSON con campos anterior/nuevo',
    `estado` ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
    `notas` TEXT COMMENT 'Notas del admin al aprobar/rechazar',
    `revisado_por` INT DEFAULT NULL COMMENT 'ID del admin que reviso',
    `revisado_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_comercio` (`comercio_id`),
    KEY `idx_estado` (`estado`),
    KEY `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Mensajes de contacto
-- ============================================================================
CREATE TABLE IF NOT EXISTS `mensajes_contacto` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `asunto` VARCHAR(200) NOT NULL,
    `mensaje` TEXT NOT NULL,
    `ip` VARCHAR(45) DEFAULT '',
    `leido` TINYINT(1) NOT NULL DEFAULT 0,
    `instrucciones_enviadas` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Log de notificaciones enviadas
-- ============================================================================
CREATE TABLE IF NOT EXISTS `notificaciones_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `site_id` INT NOT NULL DEFAULT 1,
    `destinatario` VARCHAR(150) NOT NULL,
    `asunto` VARCHAR(255) NOT NULL,
    `template` VARCHAR(100) NOT NULL,
    `estado` ENUM('enviado','fallido') NOT NULL DEFAULT 'enviado',
    `datos` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notif_log_estado` (`estado`),
    KEY `idx_notif_log_template` (`template`),
    KEY `idx_notif_log_fecha` (`created_at`),
    KEY `idx_notif_site` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Redes sociales: configuración del sitio
-- ============================================================================
CREATE TABLE IF NOT EXISTS `redes_sociales_config` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `clave` VARCHAR(100) NOT NULL,
    `valor` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_site_clave` (`site_id`, `clave`),
    KEY `idx_site` (`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Log de shares (compartidos en redes sociales)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `share_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `comercio_id` INT DEFAULT NULL,
    `pagina` VARCHAR(500) DEFAULT NULL,
    `red_social` VARCHAR(50) NOT NULL,
    `ip` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_share_log_comercio` (`comercio_id`),
    KEY `idx_share_log_red` (`red_social`),
    KEY `idx_share_log_fecha` (`created_at`),
    CONSTRAINT `fk_share_log_comercio` FOREIGN KEY (`comercio_id`) REFERENCES `comercios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Sitios: configuración multi-sitio
-- ============================================================================
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
    `lat` DECIMAL(10,8) DEFAULT '-40.91305000',
    `lng` DECIMAL(11,8) DEFAULT '-73.15913000',
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
    KEY `idx_sitios_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabla 30: politicas_aceptacion
-- Registro de aceptacion/rechazo de politicas al registrar comercio
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `politicas_aceptacion` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `usuario_id` INT DEFAULT NULL,
    `email` VARCHAR(150) NOT NULL,
    `politica` ENUM('terminos','privacidad','contenidos','derechos','cookies') NOT NULL,
    `decision` ENUM('acepto','rechazo') NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `fecha_decision` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_politicas_usuario` (`usuario_id`),
    INDEX `idx_politicas_email` (`email`),
    CONSTRAINT `fk_politicas_usuario` FOREIGN KEY (`usuario_id`)
        REFERENCES `admin_usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Restaurar verificacion de claves foraneas
-- ============================================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- FIN DEL ESQUEMA - purranque_regalos_purranque
-- Total de tablas: 30
-- ============================================================================
