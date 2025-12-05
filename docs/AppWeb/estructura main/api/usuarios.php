<?php
/**
 * API de Usuarios
 * Endpoints para operaciones con usuarios
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/session.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/classes/Usuario.php';

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
        case 'perfil':
            obtenerPerfil();
            break;

        case 'actualizar-movilidad':
            actualizarMovilidad();
            break;

        case 'preferencias':
            obtenerPreferencias();
            break;

        case 'actualizar-preferencias':
            actualizarPreferencias();
            break;

        case 'historial':
            obtenerHistorial();
            break;

        default:
            sendJSON(['success' => false, 'error' => 'Acción no válida'], 400);
    }
} catch (Exception $e) {
    sendJSON(['success' => false, 'error' => $e->getMessage()], 500);
}

/**
 * Obtener perfil del usuario
 */
function obtenerPerfil() {
    global $usuario;

    $perfil = $usuario->getPerfil();
    sendJSON([
        'success' => true,
        'usuario' => $perfil
    ]);
}

/**
 * Actualizar tipo de movilidad
 */
function actualizarMovilidad() {
    global $usuario;

    $tipo = $_REQUEST['tipo'] ?? '';

    if (!isValidMobilityType($tipo)) {
        sendJSON(['success' => false, 'error' => 'Tipo de movilidad inválido'], 400);
    }

    if ($usuario->setTipoMovilidad($tipo)) {
        SessionManager::set('tipo_movilidad', $tipo);
        sendJSON([
            'success' => true,
            'message' => 'Tipo de movilidad actualizado',
            'tipo' => $tipo
        ]);
    } else {
        sendJSON(['success' => false, 'error' => 'Error al actualizar'], 500);
    }
}

/**
 * Obtener preferencias del usuario
 */
function obtenerPreferencias() {
    global $usuario;

    $preferencias = $usuario->getPreferencias();
    sendJSON([
        'success' => true,
        'preferencias' => $preferencias
    ]);
}

/**
 * Actualizar preferencias del usuario
 */
function actualizarPreferencias() {
    global $usuario;

    $preferencias = [];

    // Recopilar preferencias del request
    $campos_validos = [
        'evitar_areas_peligrosas',
        'mostrar_calidad_aire',
        'mostrar_alumbrado',
        'mostrar_ciclovias',
        'mostrar_accesibilidad',
        'distancia_maxima_km',
        'mostrar_incidentes',
        'notificaciones_activas',
        'tema_interfaz'
    ];

    foreach ($campos_validos as $campo) {
        if (isset($_REQUEST[$campo])) {
            $preferencias[$campo] = $_REQUEST[$campo];
        }
    }

    if (empty($preferencias)) {
        sendJSON(['success' => false, 'error' => 'No hay datos para actualizar'], 400);
    }

    if ($usuario->updatePreferencias($preferencias)) {
        sendJSON([
            'success' => true,
            'message' => 'Preferencias actualizadas'
        ]);
    } else {
        sendJSON(['success' => false, 'error' => 'Error al actualizar preferencias'], 500);
    }
}

/**
 * Obtener historial de rutas
 */
function obtenerHistorial() {
    global $usuario;

    $limite = isset($_REQUEST['limite']) ? (int)$_REQUEST['limite'] : 20;
    $historial = $usuario->getHistorialRutas($limite);

    sendJSON([
        'success' => true,
        'total' => count($historial),
        'historial' => $historial
    ]);
}
