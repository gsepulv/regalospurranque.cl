-- Tabla de mensajes de contacto
CREATE TABLE IF NOT EXISTS mensajes_contacto (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    asunto VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    ip VARCHAR(45) DEFAULT '',
    leido TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Configuración de notificación de contacto
INSERT IGNORE INTO configuracion (clave, valor) VALUES ('notif_contacto', '1');
