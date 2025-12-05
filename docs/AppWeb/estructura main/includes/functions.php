<?php
/**
 * Funciones Auxiliares
 * Funciones reutilizables en toda la aplicación
 */

require_once 'config.php';

/**
 * Sanitizar entrada para evitar XSS
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar URL
 */
function validateURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Generar slug a partir de texto
 */
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Obtener distancia entre dos puntos (Fórmula de Haversine)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371; // km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat / 2) * sin($dLat / 2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon / 2) * sin($dLon / 2);

    $c = 2 * asin(sqrt($a));
    $distance = $earth_radius * $c;

    return round($distance, 2);
}

/**
 * Convertir segundos a formato legible
 */
function formatTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    if ($hours > 0) {
        return $hours . 'h ' . $minutes . 'min';
    } else {
        return $minutes . ' min';
    }
}

/**
 * Validar tipo de movilidad
 */
function isValidMobilityType($type) {
    return in_array($type, MOBILITY_TYPES);
}

/**
 * Obtener nombre legible del tipo de movilidad
 */
function getMobilityTypeName($type) {
    $names = [
        'pie' => 'A Pie',
        'bicicleta' => 'Bicicleta',
        'silla_ruedas' => 'Silla de Ruedas'
    ];
    return $names[$type] ?? $type;
}

/**
 * Obtener emoji del tipo de movilidad
 */
function getMobilityTypeEmoji($type) {
    $emojis = [
        'pie' => '🚶',
        'bicicleta' => '🚴',
        'silla_ruedas' => '♿'
    ];
    return $emojis[$type] ?? '';
}

/**
 * Enviar respuesta JSON
 */
function sendJSON($data, $statusCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Obtener mensaje de error formateado
 */
function getErrorMessage($error) {
    if (is_array($error)) {
        return implode(', ', $error);
    }
    return (string)$error;
}

/**
 * Log de actividades (desarrollo)
 */
function logActivity($message, $level = 'INFO') {
    if (DEBUG) {
        $logDir = ROOT_DIR . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/activity_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}

/**
 * Validar coordenadas
 */
function isValidCoordinates($lat, $lng) {
    $lat = (float)$lat;
    $lng = (float)$lng;
    return $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180;
}

/**
 * Obtener icono de calidad del aire
 */
function getAirQualityIcon($index) {
    switch ($index) {
        case 'Buena':
            return '🟢';
        case 'Aceptable':
            return '🟡';
        case 'Mala':
            return '🟠';
        case 'Muy Mala':
            return '🔴';
        default:
            return '⚪';
    }
}

/**
 * Obtener color de calidad del aire
 */
function getAirQualityColor($index) {
    switch ($index) {
        case 'Buena':
            return '#00AA00';
        case 'Aceptable':
            return '#FFAA00';
        case 'Mala':
            return '#FF5500';
        case 'Muy Mala':
            return '#CC0000';
        default:
            return '#999999';
    }
}

/**
 * Redirigir con mensaje
 */
function redirectWithMessage($url, $message, $type = 'info') {
    session_start();
    $_SESSION['mensaje'] = $message;
    $_SESSION['tipo_mensaje'] = $type;
    header('Location: ' . BASE_URL . $url);
    exit;
}

/**
 * Obtener y limpiar mensaje de sesión
 */
function getSessionMessage() {
    if (isset($_SESSION['mensaje'])) {
        $mensaje = [
            'texto' => $_SESSION['mensaje'],
            'tipo' => $_SESSION['tipo_mensaje'] ?? 'info'
        ];
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
        return $mensaje;
    }
    return null;
}
