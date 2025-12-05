<?php
/**
 * API de Rutas
 * Endpoints para operaciones con rutas
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/classes/Usuario.php';
require_once dirname(__DIR__) . '/classes/Ruta.php';
require_once dirname(__DIR__) . '/classes/Mapa.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (!Auth::isAuthenticated()) {
    sendJSON(['success' => false, 'error' => 'No autorizado'], 401);
}

$usuario_actual = Auth::getCurrentUser();
$usuario = new Usuario($usuario_actual['id']);
$accion = $_REQUEST['accion'] ?? '';

try {
    switch ($accion) {
        case 'calcular':
            calcularRuta();
            break;

        case 'guardar':
            guardarRuta();
            break;

        case 'eliminar':
            eliminarRuta();
            break;

        case 'listar':
            listarRutas();
            break;

        case 'obtener':
            obtenerRuta();
            break;

        case 'favorita':
            marcarFavorita();
            break;

        default:
            sendJSON(['success' => false, 'error' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    sendJSON(['success' => false, 'error' => $e->getMessage()], 500);
}

/**
 * Calcular ruta entre dos puntos
 */
function calcularRuta() {
    global $usuario_actual, $usuario;

    $punto_partida = sanitize($_REQUEST['punto_partida'] ?? '');
    $punto_destino = sanitize($_REQUEST['punto_destino'] ?? '');
    $tipo_movilidad = $_REQUEST['tipo_movilidad'] ?? '';

    if (empty($punto_partida) || empty($punto_destino) || !isValidMobilityType($tipo_movilidad)) {
        sendJSON(['success' => false, 'error' => 'Parámetros inválidos'], 400);
    }

    $mapa = new Mapa();

    // Geocodificar puntos
    $geo_partida = $mapa->geocodificar($punto_partida);
    $geo_destino = $mapa->geocodificar($punto_destino);

    if (!$geo_partida || !$geo_destino) {
        sendJSON(['success' => false, 'error' => 'No se pudo geocodificar uno o ambos puntos'], 400);
    }

    // Validar que estén dentro de CDMX
    if (!Mapa::estaDentroDelimiteCDMX($geo_partida['latitude'], $geo_partida['longitude']) ||
        !Mapa::estaDentroDelimiteCDMX($geo_destino['latitude'], $geo_destino['longitude'])) {
        sendJSON(['success' => false, 'error' => 'Las coordenadas deben estar dentro de CDMX'], 400);
    }

    // Calcular distancia
    $distancia = calculateDistance(
        $geo_partida['latitude'],
        $geo_partida['longitude'],
        $geo_destino['latitude'],
        $geo_destino['longitude']
    );

    // Estimar tiempo
    $tiempo = Mapa::estimarTiempoViaje($distancia, $tipo_movilidad);

    // Guardar en historial
    $usuario->guardarEnHistorial($punto_partida, $punto_destino, $tipo_movilidad);

    sendJSON([
        'success' => true,
        'data' => [
            'punto_partida' => $punto_partida,
            'punto_destino' => $punto_destino,
            'lat_partida' => $geo_partida['latitude'],
            'lng_partida' => $geo_partida['longitude'],
            'lat_destino' => $geo_destino['latitude'],
            'lng_destino' => $geo_destino['longitude'],
            'distancia_km' => $distancia,
            'tiempo_estimado_minutos' => $tiempo['minutos'],
            'tiempo_formato' => $tiempo['formato'],
            'tipo_movilidad' => $tipo_movilidad,
            'mapa_url' => BASE_URL . "mapa-ruta.php?punto_partida=" . urlencode($punto_partida) . 
                         "&punto_destino=" . urlencode($punto_destino) .
                         "&lat_partida=" . $geo_partida['latitude'] .
                         "&lng_partida=" . $geo_partida['longitude'] .
                         "&lat_destino=" . $geo_destino['latitude'] .
                         "&lng_destino=" . $geo_destino['longitude']
        ]
    ]);
}

/**
 * Guardar ruta
 */
function guardarRuta() {
    global $usuario_actual, $usuario;

    $punto_partida = sanitize($_REQUEST['punto_partida'] ?? '');
    $punto_destino = sanitize($_REQUEST['punto_destino'] ?? '');
    $tipo_movilidad = $_REQUEST['tipo_movilidad'] ?? '';
    $lat_partida = isset($_REQUEST['lat_partida']) ? (float)$_REQUEST['lat_partida'] : null;
    $lng_partida = isset($_REQUEST['lng_partida']) ? (float)$_REQUEST['lng_partida'] : null;
    $lat_destino = isset($_REQUEST['lat_destino']) ? (float)$_REQUEST['lat_destino'] : null;
    $lng_destino = isset($_REQUEST['lng_destino']) ? (float)$_REQUEST['lng_destino'] : null;

    if (empty($punto_partida) || empty($punto_destino) || !$lat_partida || !$lng_partida || !$lat_destino || !$lng_destino) {
        sendJSON(['success' => false, 'error' => 'Datos de ruta incompletos'], 400);
    }

    $nombre_ruta = $punto_partida . ' - ' . $punto_destino;
    $distancia = calculateDistance($lat_partida, $lng_partida, $lat_destino, $lng_destino);
    $tiempo = Mapa::estimarTiempoViaje($distancia, $tipo_movilidad);

    $ruta = new Ruta();
    $ruta_id = $ruta->crear(
        $usuario_actual['id'],
        $nombre_ruta,
        $punto_partida,
        $punto_destino,
        $tipo_movilidad,
        $lat_partida,
        $lng_partida,
        $lat_destino,
        $lng_destino,
        $distancia,
        $tiempo['minutos']
    );

    if ($ruta_id) {
        sendJSON([
            'success' => true,
            'message' => 'Ruta guardada exitosamente',
            'ruta_id' => $ruta_id
        ]);
    } else {
        sendJSON(['success' => false, 'error' => 'Error al guardar la ruta'], 500);
    }
}

/**
 * Eliminar ruta
 */
function eliminarRuta() {
    global $usuario_actual;

    $ruta_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;

    if (!$ruta_id) {
        sendJSON(['success' => false, 'error' => 'ID de ruta inválido'], 400);
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT usuario_id FROM rutas WHERE id = ?');
    $stmt->bind_param('i', $ruta_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendJSON(['success' => false, 'error' => 'Ruta no encontrada'], 404);
    }

    $ruta = $result->fetch_assoc();
    if ($ruta['usuario_id'] != $usuario_actual['id']) {
        sendJSON(['success' => false, 'error' => 'No tienes permiso para eliminar esta ruta'], 403);
    }

    $ruta_obj = new Ruta($ruta_id);
    if ($ruta_obj->eliminar()) {
        sendJSON(['success' => true, 'message' => 'Ruta eliminada']);
    } else {
        sendJSON(['success' => false, 'error' => 'Error al eliminar la ruta'], 500);
    }
}

/**
 * Listar rutas del usuario
 */
function listarRutas() {
    global $usuario;

    $rutas = $usuario->getRutasGuardadas(100);
    sendJSON([
        'success' => true,
        'total' => count($rutas),
        'rutas' => $rutas
    ]);
}

/**
 * Obtener una ruta específica
 */
function obtenerRuta() {
    global $usuario_actual;

    $ruta_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;

    if (!$ruta_id) {
        sendJSON(['success' => false, 'error' => 'ID de ruta inválido'], 400);
    }

    $ruta = new Ruta($ruta_id);
    if ($ruta->getId()) {
        sendJSON([
            'success' => true,
            'ruta' => $ruta->getInfo()
        ]);
    } else {
        sendJSON(['success' => false, 'error' => 'Ruta no encontrada'], 404);
    }
}

/**
 * Marcar/desmarcar como favorita
 */
function marcarFavorita() {
    global $usuario_actual;

    $ruta_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
    $favorita = isset($_REQUEST['favorita']) ? (bool)$_REQUEST['favorita'] : true;

    if (!$ruta_id) {
        sendJSON(['success' => false, 'error' => 'ID de ruta inválido'], 400);
    }

    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare('SELECT usuario_id FROM rutas WHERE id = ?');
    $stmt->bind_param('i', $ruta_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        sendJSON(['success' => false, 'error' => 'Ruta no encontrada'], 404);
    }

    $ruta = $result->fetch_assoc();
    if ($ruta['usuario_id'] != $usuario_actual['id']) {
        sendJSON(['success' => false, 'error' => 'No tienes permiso'], 403);
    }

    $ruta_obj = new Ruta($ruta_id);
    if ($ruta_obj->marcarFavorita($favorita)) {
        sendJSON([
            'success' => true,
            'message' => $favorita ? 'Marcada como favorita' : 'Desmarcada como favorita'
        ]);
    } else {
        sendJSON(['success' => false, 'error' => 'Error al actualizar'], 500);
    }
}
