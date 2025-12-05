<?php
/**
 * Configuración de la aplicación Movilidad CDMX
 * Define constantes y valores de configuración según el ambiente
 */

// Configuración de Base de Datos
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'movilidad_cdmx');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// APIs externas
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: 'tu_api_key_aqui');
define('NOMINATIM_API_URL', 'https://nominatim.openstreetmap.org');

// Seguridad
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'secreto_desarrollo_inseguro');
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos
define('PASSWORD_MIN_LENGTH', 8);

// Información de la aplicación
define('APP_NAME', 'Movilidad CDMX');
define('APP_VERSION', '1.0.0');

// Configuración según ambiente
$environment = getenv('ENVIRONMENT') ?: 'development';

if ($environment === 'development') {
    define('BASE_URL', 'http://localhost/movilidad-cdmx/');
    define('DEBUG', true);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} elseif ($environment === 'production') {
    define('BASE_URL', getenv('BASE_URL') ?: 'https://tudominio.com/');
    define('DEBUG', false);
    ini_set('display_errors', 0);
    error_reporting(E_CRITICAL);
} else {
    define('BASE_URL', 'http://localhost/movilidad-cdmx/');
    define('DEBUG', false);
}

// Rutas
define('ROOT_DIR', dirname(dirname(__FILE__)));
define('INCLUDES_DIR', ROOT_DIR . '/includes');
define('CLASSES_DIR', ROOT_DIR . '/classes');
define('ASSETS_DIR', ROOT_DIR . '/assets');
define('API_DIR', ROOT_DIR . '/api');
define('DATABASE_DIR', ROOT_DIR . '/database');

// Tipos de movilidad
define('MOBILITY_TYPES', ['pie', 'bicicleta', 'silla_ruedas']);

// Coordenadas de Centro de México (para mapas por defecto)
define('CDMX_LAT', 19.4326);
define('CDMX_LNG', -99.1332);
define('CDMX_ZOOM', 11);

// Configuración de CORS (si es necesario)
define('ALLOWED_ORIGINS', ['http://localhost', 'http://localhost:8000']);
