<?php
/**
 * Página de Dashboard
 */

require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'classes/Usuario.php';

// Requerir autenticación
Auth::requireAuth();

$usuario_actual = Auth::getCurrentUser();
$usuario = new Usuario($usuario_actual['id']);
$tipo_movilidad = $usuario->getTipoMovilidad();

// Obtener datos del usuario
$rutas_guardadas = $usuario->getRutasGuardadas(5);
$rutas_favoritas = $usuario->getRutasFavoritas();
$historial = $usuario->getHistorialRutas(10);

$mensaje = $_GET['mensaje'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body class="dashboard-page">
    <div class="container">
        <header class="dashboard-header">
            <div class="logo">
                <h1><?php echo APP_NAME; ?></h1>
            </div>
            <div class="user-menu">
                <span class="user-name">
                    <?php echo $tipo_movilidad ? getMobilityTypeEmoji($tipo_movilidad) . ' ' . getMobilityTypeName($tipo_movilidad) : ''; ?>
                </span>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-small btn-outline">
                    Cerrar Sesión
                </a>
            </div>
        </header>

        <main class="main-content">
            <h2>Bienvenido, <?php echo sanitize($usuario_actual['nombre']); ?></h2>

            <?php if ($mensaje): ?>
                <div class="alert alert-success">
                    <?php echo sanitize($mensaje); ?>
                </div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <!-- Sección de Acciones Rápidas -->
                <section class="quick-actions">
                    <h3>Acciones Rápidas</h3>
                    <div class="actions-grid">
                        <a href="<?php echo BASE_URL; ?>seleccion-movilidad.php" class="action-card">
                            <span class="icon">🚀</span>
                            <span class="label">Nueva Ruta</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>configurar-ruta.php" class="action-card">
                            <span class="icon">🗺️</span>
                            <span class="label">Configurar</span>
                        </a>
                        <a href="<?php echo BASE_URL; ?>seleccion-movilidad.php" class="action-card">
                            <span class="icon">🔄</span>
                            <span class="label">Cambiar Movilidad</span>
                        </a>
                        <a href="#" class="action-card" id="btn-perfil">
                            <span class="icon">👤</span>
                            <span class="label">Mi Perfil</span>
                        </a>
                    </div>
                </section>

                <!-- Rutas Guardadas -->
                <?php if (!empty($rutas_guardadas)): ?>
                    <section class="rutas-section">
                        <h3>Mis Rutas Guardadas</h3>
                        <div class="rutas-list">
                            <?php foreach ($rutas_guardadas as $ruta): ?>
                                <div class="ruta-item">
                                    <div class="ruta-header">
                                        <strong><?php echo sanitize($ruta['nombre_ruta']); ?></strong>
                                        <?php if ($ruta['favorita']): ?>
                                            <span class="badge-favorita">⭐</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ruta-body">
                                        <p>
                                            <strong>De:</strong> <?php echo sanitize(substr($ruta['punto_partida'], 0, 40)); ?>
                                        </p>
                                        <p>
                                            <strong>A:</strong> <?php echo sanitize(substr($ruta['punto_destino'], 0, 40)); ?>
                                        </p>
                                        <?php if ($ruta['distancia_km']): ?>
                                            <p>
                                                <strong>Distancia:</strong> <?php echo number_format($ruta['distancia_km'], 2); ?> km
                                            </p>
                                        <?php endif; ?>
                                        <?php if ($ruta['tiempo_estimado_minutos']): ?>
                                            <p>
                                                <strong>Tiempo:</strong> <?php echo formatTime($ruta['tiempo_estimado_minutos'] * 60); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ruta-footer">
                                        <a href="<?php echo BASE_URL; ?>mapa-ruta.php?lat_partida=<?php echo $ruta['coordenadas_partida'] ?? ''; ?>" class="btn btn-small">
                                            Ver en Mapa
                                        </a>
                                        <button class="btn btn-small btn-outline btn-eliminar" data-id="<?php echo $ruta['id']; ?>">
                                            Eliminar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Rutas Favoritas -->
                <?php if (!empty($rutas_favoritas)): ?>
                    <section class="rutas-section">
                        <h3>⭐ Rutas Favoritas</h3>
                        <div class="rutas-list">
                            <?php foreach ($rutas_favoritas as $ruta): ?>
                                <div class="ruta-item favorita">
                                    <div class="ruta-header">
                                        <strong><?php echo sanitize($ruta['nombre_ruta']); ?></strong>
                                    </div>
                                    <div class="ruta-body">
                                        <p><?php echo sanitize($ruta['punto_partida']); ?> → <?php echo sanitize($ruta['punto_destino']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Historial -->
                <?php if (!empty($historial)): ?>
                    <section class="historial-section">
                        <h3>Historial de Búsquedas</h3>
                        <div class="historial-list">
                            <?php foreach (array_slice($historial, 0, 5) as $item): ?>
                                <div class="historial-item">
                                    <small class="fecha"><?php echo date('d/m/y H:i', strtotime($item['fecha_consulta'])); ?></small>
                                    <p><?php echo sanitize($item['punto_partida']); ?> → <?php echo sanitize($item['punto_destino']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Información de Perfil -->
                <section class="perfil-section">
                    <h3>Mi Perfil</h3>
                    <div class="perfil-info">
                        <p><strong>Nombre:</strong> <?php echo sanitize($usuario_actual['nombre']); ?></p>
                        <p><strong>Email:</strong> <?php echo sanitize($usuario_actual['email']); ?></p>
                        <p><strong>Tipo de Movilidad:</strong> <?php echo $tipo_movilidad ? getMobilityTypeName($tipo_movilidad) : 'No seleccionado'; ?></p>
                    </div>
                </section>
            </div>
        </main>

        <footer class="footer">
            <p>&copy; 2025 <?php echo APP_NAME; ?> - Todos los derechos reservados</p>
        </footer>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    <script>
        // Eliminar rutas
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('¿Estás seguro de que deseas eliminar esta ruta?')) {
                    const id = this.dataset.id;
                    const formData = new FormData();
                    formData.append('accion', 'eliminar');
                    formData.append('id', id);

                    fetch('<?php echo BASE_URL; ?>api/rutas.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.parentElement.parentElement.remove();
                            alert('Ruta eliminada');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
