<?php
/**
 * Página de Inicio (Home)
 */

require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Si está autenticado, redirigir al dashboard
if (Auth::isAuthenticated()) {
    header('Location: ' . BASE_URL . 'seleccion-movilidad.php');
    exit;
}

$mensaje = $_GET['mensaje'] ?? null;
$tipo_mensaje = $_GET['tipo'] ?? 'info';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Movilidad Segura en CDMX</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body class="home-page">
    <div class="container">
        <header class="header">
            <div class="logo">
                <h1><?php echo APP_NAME; ?></h1>
                <p class="subtitle">Rutas Seguras para Todos</p>
            </div>
        </header>

        <main class="main-content">
            <?php if ($mensaje): ?>
                <div class="alert alert-<?php echo sanitize($tipo_mensaje); ?>">
                    <?php echo sanitize($mensaje); ?>
                </div>
            <?php endif; ?>

            <section class="hero">
                <h2>Bienvenido a <?php echo APP_NAME; ?></h2>
                <p>Encuentra rutas seguras y accesibles en Ciudad de México según tu tipo de movilidad</p>
                
                <div class="hero-content">
                    <div class="hero-text">
                        <h3>¿Cómo funciona?</h3>
                        <ul class="features">
                            <li>✓ Selecciona tu tipo de movilidad (a pie, bicicleta o silla de ruedas)</li>
                            <li>✓ Recibe rutas optimizadas y seguras</li>
                            <li>✓ Consulta datos en tiempo real de calidad del aire, ciclovías y alumbrado</li>
                            <li>✓ Evita zonas peligrosas con reportes de incidentes</li>
                            <li>✓ Guarda tus rutas favoritas</li>
                        </ul>
                    </div>

                    <div class="hero-benefits">
                        <div class="benefit-card">
                            <span class="emoji">🚶</span>
                            <h4>Para Peatones</h4>
                            <p>Rutas con mejor iluminación y calidad del aire</p>
                        </div>

                        <div class="benefit-card">
                            <span class="emoji">🚴</span>
                            <h4>Para Ciclistas</h4>
                            <p>Encuentra ciclovías seguras y bien mantenidas</p>
                        </div>

                        <div class="benefit-card">
                            <span class="emoji">♿</span>
                            <h4>Para Silla de Ruedas</h4>
                            <p>Rutas accesibles con buena infraestructura</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="cta-section">
                <h3>Comienza Ahora</h3>
                <div class="cta-buttons">
                    <a href="<?php echo BASE_URL; ?>registro.php" class="btn btn-primary btn-large">
                        Registrarse
                    </a>
                    <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-secondary btn-large">
                        Iniciar Sesión
                    </a>
                </div>
            </section>

            <section class="info-section">
                <h3>Información de la Ciudad</h3>
                <div class="info-grid">
                    <div class="info-card">
                        <h4>📍 Centro de CDMX</h4>
                        <p>Coordenadas: 19.43° N, 99.13° O</p>
                    </div>
                    <div class="info-card">
                        <h4>🌍 Área de Cobertura</h4>
                        <p>Ciudad de México (CDMX)</p>
                    </div>
                    <div class="info-card">
                        <h4>📊 Datos Disponibles</h4>
                        <p>Calidad del aire, ciclovías, alumbrado y más</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="footer">
            <p>&copy; 2025 <?php echo APP_NAME; ?> - Todos los derechos reservados</p>
            <p class="version">Versión <?php echo APP_VERSION; ?></p>
        </footer>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>
