<?php
/**
 * Página de Logout
 */

require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/auth.php';

$auth = new Auth();
$auth->logout();

// Redirigir a home
header('Location: ' . BASE_URL . 'index.php?mensaje=' . urlencode('Sesión cerrada exitosamente') . '&tipo=success');
exit;
