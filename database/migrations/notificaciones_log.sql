-- ============================================================================
-- Tabla: notificaciones_log
-- Registro de todas las notificaciones enviadas por el sistema
-- Fase 7, Módulo 1: Sistema de Notificaciones
-- ============================================================================

CREATE TABLE IF NOT EXISTS `notificaciones_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `destinatario` VARCHAR(150) NOT NULL,
    `asunto` VARCHAR(255) NOT NULL,
    `template` VARCHAR(100) NOT NULL,
    `estado` ENUM('enviado','fallido') NOT NULL DEFAULT 'enviado',
    `datos` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_notif_log_estado` (`estado`),
    INDEX `idx_notif_log_template` (`template`),
    INDEX `idx_notif_log_fecha` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Configuraciones de notificaciones (insert en tabla configuracion)
-- ============================================================================

INSERT INTO `configuracion` (`clave`, `valor`, `grupo`) VALUES
    ('notificaciones_activas', '1', 'notificaciones'),
    ('email_from', '', 'notificaciones'),
    ('email_reply_to', '', 'notificaciones'),
    ('notif_nueva_resena', '1', 'notificaciones'),
    ('notif_resena_aprobada', '1', 'notificaciones'),
    ('notif_resena_rechazada', '0', 'notificaciones'),
    ('notif_resena_respuesta', '1', 'notificaciones'),
    ('notif_reporte_resena', '1', 'notificaciones'),
    ('notif_nuevo_comercio', '1', 'notificaciones'),
    ('notif_bienvenida_comercio', '1', 'notificaciones'),
    ('notif_backup', '0', 'notificaciones'),
    ('notif_error_sistema', '1', 'notificaciones'),
    ('notif_resumen_semanal', '1', 'notificaciones'),
    ('notif_fecha_proxima', '1', 'notificaciones')
ON DUPLICATE KEY UPDATE `grupo` = VALUES(`grupo`);
