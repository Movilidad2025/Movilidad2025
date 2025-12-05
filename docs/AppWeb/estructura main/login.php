<?php
/**
 * Página de Login
 */

require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Si está autenticado, redirigir
if (Auth::isAuthenticated()) {
    header('Location: ' . BASE_URL . 'seleccion-movilidad.php');
    exit;
}

$errors = [];
$success_message = $_GET['mensaje'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!isset($_POST['csrf_token']) || !SessionManager::validateToken($_POST['csrf_token'])) {
        $errors[] = 'Token de seguridad inválido';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $auth = new Auth();
        $result = $auth->login($email, $password);

        if ($result['success']) {
            $redirigir = $_GET['redirigir'] ?? BASE_URL . 'seleccion-movilidad.php';
            header('Location: ' . $redirigir);
            exit;
        } else {
            $errors = $result['errors'];
        }
    }
}

$csrf_token = SessionManager::generateToken();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body class="auth-page">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <h1><?php echo APP_NAME; ?></h1>
                <h2>Iniciar Sesión</h2>

                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo sanitize($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo sanitize($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form" id="login-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="tu@email.com"
                            value="<?php echo sanitize($_POST['email'] ?? ''); ?>"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Tu contraseña"
                            required
                        >
                    </div>

                    <div class="form-group checkbox">
                        <label>
                            <input type="checkbox" name="recordarme" value="1">
                            Recuérdame
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Iniciar Sesión
                    </button>
                </form>

                <div class="auth-footer">
                    <p>¿No tienes cuenta? 
                        <a href="<?php echo BASE_URL; ?>registro.php">Regístrate aquí</a>
                    </p>
                    <p>
                        <a href="<?php echo BASE_URL; ?>">Volver al inicio</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/validaciones.js"></script>
</body>
</html>
