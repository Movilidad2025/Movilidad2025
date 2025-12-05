<?php
/**
 * Autenticación de Usuarios
 * Maneja login, registro y validación de credenciales
 */

require_once 'database.php';
require_once 'session.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registrar nuevo usuario
     */
    public function register($nombre, $email, $password, $confirmPassword) {
        // Validar entrada
        $errors = [];

        if (empty($nombre) || strlen($nombre) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (empty($password) || strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Las contraseñas no coinciden';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Verificar si email ya existe
        $stmt = $this->db->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'errors' => ['El email ya está registrado']];
        }

        // Hash de contraseña
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insertar usuario
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios (nombre, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('sss', $nombre, $email, $passwordHash);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registro exitoso. Por favor inicia sesión'];
        } else {
            return ['success' => false, 'errors' => ['Error al registrar usuario: ' . $this->db->error]];
        }
    }

    /**
     * Iniciar sesión
     */
    public function login($email, $password) {
        $errors = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        if (empty($password)) {
            $errors[] = 'La contraseña es requerida';
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Buscar usuario
        $stmt = $this->db->prepare(
            'SELECT id, nombre, email, password_hash, tipo_movilidad, activo FROM usuarios WHERE email = ?'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'errors' => ['Email o contraseña incorrectos']];
        }

        $usuario = $result->fetch_assoc();

        if (!$usuario['activo']) {
            return ['success' => false, 'errors' => ['La cuenta ha sido desactivada']];
        }

        // Verificar contraseña
        if (!password_verify($password, $usuario['password_hash'])) {
            return ['success' => false, 'errors' => ['Email o contraseña incorrectos']];
        }

        // Establecer sesión
        SessionManager::setUser(
            $usuario['id'],
            $usuario['nombre'],
            $usuario['email'],
            $usuario['tipo_movilidad']
        );

        return ['success' => true, 'message' => 'Bienvenido ' . $usuario['nombre']];
    }

    /**
     * Cerrar sesión
     */
    public function logout() {
        SessionManager::destroy();
        return ['success' => true, 'message' => 'Sesión cerrada exitosamente'];
    }

    /**
     * Verificar si usuario está autenticado
     */
    public static function isAuthenticated() {
        return SessionManager::isAuthenticated();
    }

    /**
     * Obtener usuario actual
     */
    public static function getCurrentUser() {
        if (!self::isAuthenticated()) {
            return null;
        }
        return SessionManager::getUser();
    }

    /**
     * Requerir autenticación (redirige si no está autenticado)
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            header('Location: ' . BASE_URL . 'login.php?redirigir=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
}
