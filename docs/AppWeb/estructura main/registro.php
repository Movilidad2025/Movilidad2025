<?php
/**
 * Página de Registro
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
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF
    if (!isset($_POST['csrf_token']) || !SessionManager::validateToken($_POST['csrf_token'])) {
        $errors[] = 'Token de seguridad inválido';
    } else {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $auth = new Auth();
        $result = $auth->register($nombre, $email, $password, $confirmPassword);

        if ($result['success']) {
            $_SESSION['registro_exito'] = true;
            header('Location: ' . BASE_URL . 'login.php?mensaje=' . urlencode($result['message']));
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
    <title>Registro - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/styles.css">
</head>
<body class="auth-page">
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <h1><?php echo APP_NAME; ?></h1>
                <h2>Crear Cuenta</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo sanitize($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form" id="registro-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <input 
                            type="text" 
                            id="nombre" 
                            name="nombre" 
                            placeholder="Tu nombre completo"
                            value="<?php echo sanitize($nombre ?? ''); ?>"
                            required
                            minlength="3"
                        >
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="tu@email.com"
                            value="<?php echo sanitize($email ?? ''); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña *</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Mínimo 8 caracteres"
                            required
                            minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                        >
                        <small>Debe tener al menos <?php echo PASSWORD_MIN_LENGTH; ?> caracteres</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña *</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirma tu contraseña"
                            required
                            minlength="<?php echo PASSWORD_MIN_LENGTH; ?>"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Registrarse
                    </button>
                </form>

                <div class="auth-footer">
                    <p>¿Ya tienes cuenta? 
                        <a href="<?php echo BASE_URL; ?>login.php">Inicia sesión aquí</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/validaciones.js"></script>
    <script>
        document.getElementById('registro-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }

            if (password.length < <?php echo PASSWORD_MIN_LENGTH; ?>) {
                e.preventDefault();
                alert('La contraseña debe tener al menos <?php echo PASSWORD_MIN_LENGTH; ?> caracteres');
                return false;
            }
        });
    </script>
</body>
</html>
