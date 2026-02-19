<?php
/**
 * Configuración global de la aplicación
 * Regalos Purranque v2
 *
 * Detección automática de entorno — no requiere cambios manuales para deploy
 */

// Detección automática: producción si el host es el dominio oficial o el subdominio v2
$isProduction = isset($_SERVER['HTTP_HOST'])
    && (
        str_contains($_SERVER['HTTP_HOST'], 'regalospurranque.cl')
        || str_contains($_SERVER['HTTP_HOST'], 'regalos.purranque.info')
    );

// Entorno: development | production
define('APP_ENV', $isProduction ? 'production' : 'development');
define('APP_DEBUG', APP_ENV === 'development');

// Sitio — Dominio canónico SIEMPRE es regalospurranque.cl en producción
define('SITE_NAME', 'Regalos Purranque');
define('SITE_DESCRIPTION', 'Directorio comercial de Purranque, Chile. Encuentra los mejores comercios, ofertas y servicios.');
define('SITE_URL', $isProduction
    ? 'https://regalospurranque.cl'
    : 'http://localhost'
);

// Versión
define('APP_VERSION', '2.0.0');

// Zona horaria
date_default_timezone_set('America/Santiago');

// Sesión
define('SESSION_NAME', 'regalos_sess');
define('SESSION_LIFETIME', 7200); // 2 horas

// Subida de archivos
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('UPLOAD_PATH', BASE_PATH . '/assets/img');

// Ciudad
define('CITY_LAT', -40.91305);
define('CITY_LNG', -73.15913);
define('CITY_NAME', 'Purranque');
define('CITY_ZOOM', 15);

// Paginación
define('PER_PAGE', 12);
define('ADMIN_PER_PAGE', 20);

// Logs
define('LOG_PATH', BASE_PATH . '/storage/logs');

// Cargar helpers
require_once BASE_PATH . '/app/helpers.php';
