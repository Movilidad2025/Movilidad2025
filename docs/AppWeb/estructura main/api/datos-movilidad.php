<?php
/**
 * API de Datos de Movilidad
 * Endpoints para obtener datos de calidad de aire, ciclovías, alumbrado, etc.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/classes/Usuario.php';
require_once dirname(__DIR__) . '/classes/Movilidad.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (!Auth::isAuthenticated()) {
    sendJSON(['success' => false, 'error' => 'No autorizado'], 401);
}

$usuario_actual = Auth::getCurrentUser();
$usuario = new Usuario($usuario_actual['id']);
$tipo = $_REQUEST['tipo'] ?? '';

try {
    switch ($tipo) {
        case 'calidad-aire':
            obtenerCalidadAire();
            break;

        case 'ciclovias':
            obtenerCiclovias();
            break;

        case 'alumbrado':
            obtenerAlumbrado();
            break;

        case 'accesibilidad':
            obtenerAccesibilidad();
            break;

        case 'incidentes':
            obtenerIncidentes();
            break;

        case 'datos-ruta':
            obtenerDatosRuta();
            break;

        default:
            sendJSON(['success' => false, 'error' => 'Tipo de dato no válido'], 400);
    }
} catch (Exception $e) {
    sendJSON(['success' => false, 'error' => $e->getMessage()], 500);
}

/**
 * Obtener calidad del aire
 */
function obtenerCalidadAire() {
    $lat = isset($_REQUEST['lat']) ? (float)$_REQUEST['lat'] : null;
    $lng = isset($_REQUEST['lng']) ? (float)$_REQUEST['lng'] : null;
    $rango = isset($_REQUEST['rango']) ? (int)$_REQUEST['rango'] : 10;

    if (!$lat || !$lng) {
        sendJSON(['success' => false, 'error' => 'Coordenadas requeridas'], 400);
    }

    if (!isValidCoordinates($lat, $lng)) {
        sendJSON(['success' => false, 'error' => 'Coordenadas inválidas'], 400);
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare(
        'SELECT * FROM calidad_aire 
         WHERE ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= ?
         ORDER BY fecha_medicion DESC
         LIMIT 10'
    );

    $stmt->bind_param('ddi', $lat, $lng, $rango);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    sendJSON([
        'success' => true,
        'total' => count($result),
        'datos' => $result
    ]);
}

/**
 * Obtener ciclovías
 */
function obtenerCiclovias() {
    $lat = isset($_REQUEST['lat']) ? (float)$_REQUEST['lat'] : null;
    $lng = isset($_REQUEST['lng']) ? (float)$_REQUEST['lng'] : null;
    $alcaldia = $_REQUEST['alcaldia'] ?? null;

    if (!$lat || !$lng) {
        sendJSON(['success' => false, 'error' => 'Coordenadas requeridas'], 400);
    }

    $db = Database::getInstance()->getConnection();

    if ($alcaldia) {
        $stmt = $db->prepare(
            'SELECT * FROM ciclovias 
             WHERE alcaldia = ? AND activa = TRUE
             ORDER BY nombre'
        );
        $stmt->bind_param('s', $alcaldia);
    } else {
        $stmt = $db->prepare(
            'SELECT *, ST_Distance_Sphere(ST_PointN(coordenadas, 1), POINT(?, ?)) / 1000 as distancia
             FROM ciclovias 
             WHERE activa = TRUE
             ORDER BY distancia
             LIMIT 20'
        );
        $stmt->bind_param('dd', $lat, $lng);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    sendJSON([
        'success' => true,
        'total' => count($result),
        'ciclovias' => $result
    ]);
}

/**
 * Obtener alumbrado público
 */
function obtenerAlumbrado() {
    $lat = isset($_REQUEST['lat']) ? (float)$_REQUEST['lat'] : null;
    $lng = isset($_REQUEST['lng']) ? (float)$_REQUEST['lng'] : null;
    $rango = isset($_REQUEST['rango']) ? (int)$_REQUEST['rango'] : 3;

    if (!$lat || !$lng) {
        sendJSON(['success' => false, 'error' => 'Coordenadas requeridas'], 400);
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare(
        'SELECT * FROM alumbrado 
         WHERE ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= ?
         ORDER BY estado_funcionamiento
         LIMIT 25'
    );

    $stmt->bind_param('ddi', $lat, $lng, $rango);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calcular estadísticas
    $stats = [
        'funcionando' => 0,
        'parcial' => 0,
        'no_funciona' => 0
    ];

    foreach ($result as $luminaria) {
        $stats[$luminaria['estado_funcionamiento']]++;
    }

    sendJSON([
        'success' => true,
        'total' => count($result),
        'estadisticas' => $stats,
        'luminarias' => $result
    ]);
}

/**
 * Obtener puntos de accesibilidad
 */
function obtenerAccesibilidad() {
    $lat = isset($_REQUEST['lat']) ? (float)$_REQUEST['lat'] : null;
    $lng = isset($_REQUEST['lng']) ? (float)$_REQUEST['lng'] : null;
    $tipo = $_REQUEST['subtipo'] ?? null;

    if (!$lat || !$lng) {
        sendJSON(['success' => false, 'error' => 'Coordenadas requeridas'], 400);
    }

    $db = Database::getInstance()->getConnection();

    if ($tipo) {
        $stmt = $db->prepare(
            'SELECT * FROM accesibilidad 
             WHERE tipo = ? 
             AND ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 5
             ORDER BY condicion'
        );
        $stmt->bind_param('sdd', $tipo, $lat, $lng);
    } else {
        $stmt = $db->prepare(
            'SELECT * FROM accesibilidad 
             WHERE ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 5
             ORDER BY tipo, condicion
             LIMIT 20'
        );
        $stmt->bind_param('dd', $lat, $lng);
    }

    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    sendJSON([
        'success' => true,
        'total' => count($result),
        'puntos' => $result
    ]);
}

/**
 * Obtener incidentes reportados
 */
function obtenerIncidentes() {
    $lat = isset($_REQUEST['lat']) ? (float)$_REQUEST['lat'] : null;
    $lng = isset($_REQUEST['lng']) ? (float)$_REQUEST['lng'] : null;
    $rango = isset($_REQUEST['rango']) ? (int)$_REQUEST['rango'] : 3;

    if (!$lat || !$lng) {
        sendJSON(['success' => false, 'error' => 'Coordenadas requeridas'], 400);
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare(
        'SELECT * FROM incidentes 
         WHERE ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= ?
         AND estado IN ("reportado", "verificado")
         AND visible_publico = TRUE
         ORDER BY severidad DESC, fecha_reporte DESC
         LIMIT 15'
    );

    $stmt->bind_param('ddi', $lat, $lng, $rango);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    sendJSON([
        'success' => true,
        'total' => count($result),
        'incidentes' => $result
    ]);
}

/**
 * Obtener todos los datos relevantes para una ruta
 */
function obtenerDatosRuta() {
    global $usuario;

    $lat1 = isset($_REQUEST['lat1']) ? (float)$_REQUEST['lat1'] : null;
    $lng1 = isset($_REQUEST['lng1']) ? (float)$_REQUEST['lng1'] : null;
    $lat2 = isset($_REQUEST['lat2']) ? (float)$_REQUEST['lat2'] : null;
    $lng2 = isset($_REQUEST['lng2']) ? (float)$_REQUEST['lng2'] : null;
    $tipo_movilidad = $_REQUEST['tipo'] ?? $usuario->getTipoMovilidad();

    if (!$lat1 || !$lng1 || !$lat2 || !$lng2 || !isValidMobilityType($tipo_movilidad)) {
        sendJSON(['success' => false, 'error' => 'Parámetros inválidos'], 400);
    }

    $movilidad = new Movilidad($tipo_movilidad);
    $datos = $movilidad->obtenerDatos($lat1, $lng1, $lat2, $lng2);

    sendJSON([
        'success' => true,
        'tipo_movilidad' => $tipo_movilidad,
        'datos' => $datos
    ]);
}
