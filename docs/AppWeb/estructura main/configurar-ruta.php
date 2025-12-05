<?php
/**
 * Página de Configuración de Ruta
 */

require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'classes/Usuario.php';
require_once 'classes/Mapa.php';

// Requerir autenticación
Auth::requireAuth();

$usuario_actual = Auth::getCurrentUser();
$usuario = new Usuario($usuario_actual['id']);
$tipo_movilidad = $usuario->getTipoMovilidad();

// Si no tiene tipo de movilidad seleccionado, redirigir
if (!$tipo_movilidad) {
    header('Location: ' . BASE_URL . 'seleccion-movilidad.php');
    exit;
}

$mensaje = $_GET['mensaje'] ?? null;
$tipo_mensaje = $_GET['tipo'] ?? 'info';

$mapa = new Mapa();

// Obtener historial de rutas del usuario
$rutas_recientes = $usuario->getHistorialRutas(5);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Ruta - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-page">
    <div class="container">
        <header class="dashboard-header">
            <div class="logo">
                <h1><?php echo APP_NAME; ?></h1>
            </div>
            <div class="user-menu">
                <span class="user-name">
                    <?php echo getMobilityTypeEmoji($tipo_movilidad); ?> 
                    <?php echo getMobilityTypeName($tipo_movilidad); ?>
                </span>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-small btn-outline">
                    Cerrar Sesión
                </a>
            </div>
        </header>

        <main class="main-content">
            <h2>Configurar tu Ruta</h2>
            <p class="subtitle">Ingresa punto de partida y destino para calcular la mejor ruta</p>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo sanitize($tipo_mensaje); ?>">
                    <?php echo sanitize($mensaje); ?>
                </div>
            <?php endif; ?>

            <div class="config-container">
                <form method="POST" action="<?php echo BASE_URL; ?>api/rutas.php?accion=calcular" class="ruta-form" id="ruta-form">
                    <input type="hidden" name="tipo_movilidad" value="<?php echo sanitize($tipo_movilidad); ?>">

                    <div class="form-group">
                        <label for="punto_partida">Punto de Partida *</label>
                        <div class="input-group">
                            <input 
                                type="text" 
                                id="punto_partida" 
                                name="punto_partida" 
                                placeholder="Ej: Paseo de la Reforma 505"
                                required
                                list="historial-partida"
                            >
                            <button type="button" class="btn-icon" id="btn-ubicacion-actual" title="Usar ubicación actual">
                                📍
                            </button>
                        </div>
                        <datalist id="historial-partida">
                            <?php foreach ($rutas_recientes as $ruta): ?>
                                <option value="<?php echo sanitize($ruta['punto_partida']); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <small id="coords-partida"></small>
                    </div>

                    <div class="form-group">
                        <label for="punto_destino">Punto de Destino *</label>
                        <div class="input-group">
                            <input 
                                type="text" 
                                id="punto_destino" 
                                name="punto_destino" 
                                placeholder="Ej: Museo Nacional de Antropología"
                                required
                                list="historial-destino"
                            >
                            <button type="button" class="btn-icon" id="btn-intercambiar" title="Intercambiar puntos">
                                ⇄
                            </button>
                        </div>
                        <datalist id="historial-destino">
                            <?php foreach ($rutas_recientes as $ruta): ?>
                                <option value="<?php echo sanitize($ruta['punto_destino']); ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <small id="coords-destino"></small>
                    </div>

                    <div class="form-group">
                        <label for="preferencias">Preferencias (opcional)</label>
                        <div class="checkboxes">
                            <label>
                                <input type="checkbox" name="pref_aire" value="1" checked>
                                Considerar calidad del aire
                            </label>
                            <?php if ($tipo_movilidad === 'pie' || $tipo_movilidad === 'silla_ruedas'): ?>
                                <label>
                                    <input type="checkbox" name="pref_alumbrado" value="1" checked>
                                    Priorizar calles iluminadas
                                </label>
                            <?php endif; ?>
                            <?php if ($tipo_movilidad === 'bicicleta'): ?>
                                <label>
                                    <input type="checkbox" name="pref_ciclovias" value="1" checked>
                                    Usar ciclovías cuando sea posible
                                </label>
                            <?php endif; ?>
                            <?php if ($tipo_movilidad === 'silla_ruedas'): ?>
                                <label>
                                    <input type="checkbox" name="pref_accesibilidad" value="1" checked>
                                    Priorizar rutas accesibles
                                </label>
                            <?php endif; ?>
                            <label>
                                <input type="checkbox" name="pref_seguridad" value="1" checked>
                                Evitar zonas peligrosas
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-large">
                            Ver Ruta en Mapa
                        </button>
                        <a href="<?php echo BASE_URL; ?>seleccion-movilidad.php" class="btn btn-secondary btn-large">
                            Cambiar Tipo de Movilidad
                        </a>
                    </div>
                </form>

                <?php if (!empty($rutas_recientes)): ?>
                    <div class="rutas-recientes">
                        <h3>Rutas Recientes</h3>
                        <ul class="rutas-list">
                            <?php foreach (array_slice($rutas_recientes, 0, 3) as $ruta): ?>
                                <li class="ruta-item">
                                    <div class="ruta-info">
                                        <strong><?php echo sanitize($ruta['punto_partida']); ?></strong>
                                        <span class="arrow">→</span>
                                        <strong><?php echo sanitize($ruta['punto_destino']); ?></strong>
                                    </div>
                                    <button type="button" class="btn-usar-ruta" data-partida="<?php echo sanitize($ruta['punto_partida']); ?>" data-destino="<?php echo sanitize($ruta['punto_destino']); ?>">
                                        Usar
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2025 <?php echo APP_NAME; ?> - Todos los derechos reservados</p>
        </footer>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/mapa.js"></script>
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const tipoMovilidad = '<?php echo sanitize($tipo_movilidad); ?>';

        // Botón de ubicación actual
        document.getElementById('btn-ubicacion-actual').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    document.getElementById('coords-partida').textContent = `Coords: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                    document.getElementById('punto_partida').value = `Ubicación actual (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
                }, function(error) {
                    alert('No se puede obtener la ubicación: ' + error.message);
                });
            } else {
                alert('Tu navegador no soporta geolocalización');
            }
        });

        // Botón intercambiar puntos
        document.getElementById('btn-intercambiar').addEventListener('click', function() {
            const partida = document.getElementById('punto_partida').value;
            const destino = document.getElementById('punto_destino').value;
            document.getElementById('punto_partida').value = destino;
            document.getElementById('punto_destino').value = partida;
        });

        // Usar rutas recientes
        document.querySelectorAll('.btn-usar-ruta').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('punto_partida').value = this.dataset.partida;
                document.getElementById('punto_destino').value = this.dataset.destino;
            });
        });

        // Geocodificación mientras se escribe
        const inputPartida = document.getElementById('punto_partida');
        const inputDestino = document.getElementById('punto_destino');

        // Debounce para no hacer muchas solicitudes
        let timeoutPartida;
        inputPartida.addEventListener('blur', function() {
            clearTimeout(timeoutPartida);
            // Aquí se podría hacer geocoding al perder el foco
        });

        let timeoutDestino;
        inputDestino.addEventListener('blur', function() {
            clearTimeout(timeoutDestino);
            // Aquí se podría hacer geocoding al perder el foco
        });
    </script>
</body>
</html>
