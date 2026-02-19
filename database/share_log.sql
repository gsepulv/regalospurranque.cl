-- Tabla: share_log
-- Registro de compartidos en redes sociales
CREATE TABLE IF NOT EXISTS `share_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `comercio_id` INT DEFAULT NULL,
    `pagina` VARCHAR(500) DEFAULT NULL,
    `red_social` VARCHAR(50) NOT NULL,
    `ip` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_share_log_comercio` (`comercio_id`),
    INDEX `idx_share_log_red` (`red_social`),
    INDEX `idx_share_log_fecha` (`created_at`),
    CONSTRAINT `fk_share_log_comercio` FOREIGN KEY (`comercio_id`)
        REFERENCES `comercios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
