<?php
/**
 * Página de Mapa de Ruta
 */

require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'classes/Usuario.php';
require_once 'classes/Mapa.php';
require_once 'classes/Movilidad.php';

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

// Obtener parámetros de la URL
$punto_partida = $_GET['punto_partida'] ?? $_POST['punto_partida'] ?? '';
$punto_destino = $_GET['punto_destino'] ?? $_POST['punto_destino'] ?? '';
$lat_partida = isset($_GET['lat_partida']) ? (float)$_GET['lat_partida'] : (isset($_POST['lat_partida']) ? (float)$_POST['lat_partida'] : CDMX_LAT);
$lng_partida = isset($_GET['lng_partida']) ? (float)$_GET['lng_partida'] : (isset($_POST['lng_partida']) ? (float)$_POST['lng_partida'] : CDMX_LNG);
$lat_destino = isset($_GET['lat_destino']) ? (float)$_GET['lat_destino'] : (isset($_POST['lat_destino']) ? (float)$_POST['lat_destino'] : CDMX_LAT);
$lng_destino = isset($_GET['lng_destino']) ? (float)$_GET['lng_destino'] : (isset($_POST['lng_destino']) ? (float)$_POST['lng_destino'] : CDMX_LNG);

$mapa = new Mapa();
$movilidad = new Movilidad($tipo_movilidad);

// Calcular distancia
$distancia = calculateDistance($lat_partida, $lng_partida, $lat_destino, $lng_destino);
$tiempo = Mapa::estimarTiempoViaje($distancia, $tipo_movilidad);

// Obtener datos de movilidad
$datos_movilidad = $movilidad->obtenerDatos($lat_partida, $lng_partida, $lat_destino, $lng_destino);
$recomendaciones = $movilidad->obtenerRutaSegura($lat_partida, $lng_partida, $lat_destino, $lng_destino);

// Crear configuración del mapa
$config_mapa = $mapa->crearMapaLeaflet(
    ($lat_partida + $lat_destino) / 2,
    ($lng_partida + $lng_destino) / 2,
    10
);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Ruta - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <style>
        #mapa {
            height: 600px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
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
            <h2>Ruta Recomendada</h2>

            <div class="ruta-info-container">
                <div class="ruta-detalle">
                    <h3>Información de la Ruta</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Partida:</span>
                            <span class="value"><?php echo sanitize($punto_partida); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Destino:</span>
                            <span class="value"><?php echo sanitize($punto_destino); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Distancia:</span>
                            <span class="value"><?php echo number_format($distancia, 2); ?> km</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Tiempo Estimado:</span>
                            <span class="value"><?php echo sanitize($tiempo['formato']); ?></span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($recomendaciones['seguridad']) || !empty($recomendaciones['advertencias'])): ?>
                    <div class="recomendaciones">
                        <h3>Recomendaciones de Seguridad</h3>
                        
                        <?php if (!empty($recomendaciones['seguridad'])): ?>
                            <div class="recomendacion-grupo positive">
                                <strong>✓ Lo Positivo:</strong>
                                <ul>
                                    <?php foreach ($recomendaciones['seguridad'] as $rec): ?>
                                        <li><?php echo sanitize($rec); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($recomendaciones['advertencias'])): ?>
                            <div class="recomendacion-grupo warning">
                                <strong>⚠ Advertencias:</strong>
                                <ul>
                                    <?php foreach ($recomendaciones['advertencias'] as $adv): ?>
                                        <li><?php echo sanitize($adv); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mapa Interactivo -->
            <div id="mapa"></div>

            <!-- Datos Específicos por Tipo de Movilidad -->
            <div class="datos-movilidad">
                <h3>Datos Relevantes para tu Ruta</h3>

                <?php if (!empty($datos_movilidad['calidad_aire'])): ?>
                    <section class="dato-seccion">
                        <h4>📊 Calidad del Aire</h4>
                        <div class="datos-grid">
                            <?php foreach ($datos_movilidad['calidad_aire'] as $estacion): ?>
                                <div class="dato-card">
                                    <strong><?php echo sanitize($estacion['nombre_estacion']); ?></strong>
                                    <div>
                                        Índice: <?php echo getAirQualityIcon($estacion['indice_calidad']); ?>
                                        <?php echo sanitize($estacion['indice_calidad']); ?>
                                    </div>
                                    <small>PM2.5: <?php echo $estacion['pm25']; ?> µg/m³</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($datos_movilidad['ciclovias'])): ?>
                    <section class="dato-seccion">
                        <h4>🚴 Ciclovías Disponibles</h4>
                        <div class="datos-grid">
                            <?php foreach ($datos_movilidad['ciclovias'] as $ciclovia): ?>
                                <div class="dato-card">
                                    <strong><?php echo sanitize($ciclovia['nombre']); ?></strong>
                                    <div>
                                        Longitud: <?php echo $ciclovia['longitud_km']; ?> km
                                    </div>
                                    <small>
                                        Estado: <?php echo sanitize($ciclovia['estado_conservacion']); ?>
                                        <?php echo $ciclovia['iluminada'] ? '💡 Iluminada' : ''; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($datos_movilidad['alumbrado'])): ?>
                    <section class="dato-seccion">
                        <h4>💡 Alumbrado Público</h4>
                        <div class="info-item">
                            <span class="label">Luminarias en la zona:</span>
                            <span class="value"><?php echo count($datos_movilidad['alumbrado']); ?> detectadas</span>
                        </div>
                        <small>Estado: <?php 
                            $estados = array_count_values(array_column($datos_movilidad['alumbrado'], 'estado_funcionamiento'));
                            echo 'Funcionando: ' . ($estados['funcionando'] ?? 0) . 
                                 ' | Parcial: ' . ($estados['parcial'] ?? 0) . 
                                 ' | No funciona: ' . ($estados['no_funciona'] ?? 0);
                        ?></small>
                    </section>
                <?php endif; ?>

                <?php if (!empty($datos_movilidad['accesibilidad'])): ?>
                    <section class="dato-seccion">
                        <h4>♿ Puntos de Accesibilidad</h4>
                        <div class="datos-grid">
                            <?php foreach ($datos_movilidad['accesibilidad'] as $punto): ?>
                                <div class="dato-card">
                                    <strong><?php echo ucfirst(str_replace('_', ' ', $punto['tipo'])); ?></strong>
                                    <div>
                                        Condición: <?php echo sanitize($punto['condicion']); ?>
                                    </div>
                                    <small><?php echo sanitize($punto['descripcion'] ?? 'Sin descripción'); ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button id="btn-guardar-ruta" class="btn btn-primary btn-large">
                    💾 Guardar esta Ruta
                </button>
                <a href="<?php echo BASE_URL; ?>configurar-ruta.php" class="btn btn-secondary btn-large">
                    ← Nueva Ruta
                </a>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2025 <?php echo APP_NAME; ?> - Todos los derechos reservados</p>
        </footer>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/mapa.js"></script>
    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const tipoMovilidad = '<?php echo sanitize($tipo_movilidad); ?>';
        
        // Configuración del mapa
        const mapaConfig = <?php echo json_encode($config_mapa); ?>;
        const puntoPartida = <?php echo json_encode(['lat' => $lat_partida, 'lng' => $lng_partida]); ?>;
        const puntoDestino = <?php echo json_encode(['lat' => $lat_destino, 'lng' => $lng_destino]); ?>;
        const nombrePartida = '<?php echo sanitize($punto_partida); ?>';
        const nombreDestino = '<?php echo sanitize($punto_destino); ?>';

        // Inicializar mapa
        inicializarMapa(mapaConfig, puntoPartida, puntoDestino, nombrePartida, nombreDestino);

        // Botón guardar ruta
        document.getElementById('btn-guardar-ruta').addEventListener('click', function() {
            const formData = new FormData();
            formData.append('accion', 'guardar');
            formData.append('punto_partida', nombrePartida);
            formData.append('punto_destino', nombreDestino);
            formData.append('tipo_movilidad', tipoMovilidad);
            formData.append('lat_partida', puntoPartida.lat);
            formData.append('lng_partida', puntoPartida.lng);
            formData.append('lat_destino', puntoDestino.lat);
            formData.append('lng_destino', puntoDestino.lng);

            fetch(BASE_URL + 'api/rutas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Ruta guardada correctamente');
                } else {
                    alert('Error al guardar la ruta: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar la ruta');
            });
        });
    </script>
</body>
</html>
