<?php
/**
 * Página de Selección de Tipo de Movilidad
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
$tipo_movilidad_actual = $usuario->getTipoMovilidad();

$mensaje = $_GET['mensaje'] ?? null;
$tipo_mensaje = $_GET['tipo'] ?? 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_seleccionado = $_POST['tipo_movilidad'] ?? null;

    if (!isValidMobilityType($tipo_seleccionado)) {
        $mensaje = 'Tipo de movilidad inválido';
        $tipo_mensaje = 'danger';
    } else {
        if ($usuario->setTipoMovilidad($tipo_seleccionado)) {
            SessionManager::set('tipo_movilidad', $tipo_seleccionado);
            header('Location: ' . BASE_URL . 'configurar-ruta.php?mensaje=' . urlencode('Tipo de movilidad actualizado'));
            exit;
        } else {
            $mensaje = 'Error al guardar el tipo de movilidad';
            $tipo_mensaje = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Movilidad - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body class="dashboard-page">
    <div class="container">
        <header class="dashboard-header">
            <div class="logo">
                <h1><?php echo APP_NAME; ?></h1>
            </div>
            <div class="user-menu">
                <span class="user-name">Bienvenido, <?php echo sanitize($usuario_actual['nombre']); ?></span>
                <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-small btn-outline">
                    Cerrar Sesión
                </a>
            </div>
        </header>

        <main class="main-content">
            <h2>Selecciona tu Tipo de Movilidad</h2>
            <p class="subtitle">Elige cómo te desplazas para obtener rutas optimizadas</p>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo sanitize($tipo_mensaje); ?>">
                    <?php echo sanitize($mensaje); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="movilidad-form">
                <div class="movilidad-grid">
                    <?php 
                    $movilidades = [
                        [
                            'tipo' => 'pie',
                            'nombre' => 'A Pie',
                            'emoji' => '🚶',
                            'descripcion' => 'Camina con seguridad',
                            'beneficios' => 'Rutas con buena iluminación y calidad del aire'
                        ],
                        [
                            'tipo' => 'bicicleta',
                            'nombre' => 'Bicicleta',
                            'emoji' => '🚴',
                            'descripcion' => 'Pedalea en ciclovías',
                            'beneficios' => 'Ciclovías seguras y bien mantenidas'
                        ],
                        [
                            'tipo' => 'silla_ruedas',
                            'nombre' => 'Silla de Ruedas',
                            'emoji' => '♿',
                            'descripcion' => 'Desplázate accesiblemente',
                            'beneficios' => 'Rutas con infraestructura accesible'
                        ]
                    ];
                    
                    foreach ($movilidades as $movilidad):
                    ?>
                        <label class="movilidad-card <?php echo $tipo_movilidad_actual === $movilidad['tipo'] ? 'selected' : ''; ?>">
                            <input 
                                type="radio" 
                                name="tipo_movilidad" 
                                value="<?php echo $movilidad['tipo']; ?>"
                                <?php echo $tipo_movilidad_actual === $movilidad['tipo'] ? 'checked' : ''; ?>
                                required
                            >
                            <div class="card-content">
                                <span class="emoji"><?php echo $movilidad['emoji']; ?></span>
                                <h3><?php echo $movilidad['nombre']; ?></h3>
                                <p class="description"><?php echo $movilidad['descripcion']; ?></p>
                                <p class="benefits">
                                    <strong>Beneficios:</strong> <?php echo $movilidad['beneficios']; ?>
                                </p>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">
                        Continuar a Configurar Ruta
                    </button>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="btn btn-secondary btn-large">
                        Ir al Dashboard
                    </a>
                </div>
            </form>
        </main>

        <footer class="footer">
            <p>&copy; 2025 <?php echo APP_NAME; ?> - Todos los derechos reservados</p>
        </footer>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>
