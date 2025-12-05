<?php
/**
 * Gestión de Sesiones
 * Configura y maneja las sesiones de usuario
 */

require_once 'config.php';

class SessionManager {
    const SESSION_PREFIX = 'movilidad_';

    public static function init() {
        // Configurar opciones de sesión
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', !DEBUG); // Solo HTTPS en producción
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar timeout de sesión
        self::checkTimeout();
    }

    public static function set($key, $value) {
        $_SESSION[self::SESSION_PREFIX . $key] = $value;
    }

    public static function get($key, $default = null) {
        $key = self::SESSION_PREFIX . $key;
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[self::SESSION_PREFIX . $key]);
    }

    public static function delete($key) {
        unset($_SESSION[self::SESSION_PREFIX . $key]);
    }

    public static function destroy() {
        $_SESSION = [];
        if (ini_get('session.use_cookies') && session_status() !== PHP_SESSION_NONE) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        session_destroy();
    }

    private static function checkTimeout() {
        $timeout_key = self::SESSION_PREFIX . 'last_activity';
        $timeout = time() - SESSION_TIMEOUT;

        if (isset($_SESSION[$timeout_key]) && $_SESSION[$timeout_key] < $timeout) {
            self::destroy();
            header('Location: ' . BASE_URL . 'login.php?mensaje=sesion_expirada');
            exit;
        }

        $_SESSION[$timeout_key] = time();
    }

    public static function isAuthenticated() {
        return self::has('usuario_id') && self::get('usuario_id') !== null;
    }

    public static function getUserId() {
        return self::get('usuario_id');
    }

    public static function setUser($userId, $nombre, $email, $tipoMovilidad) {
        self::set('usuario_id', $userId);
        self::set('nombre', $nombre);
        self::set('email', $email);
        self::set('tipo_movilidad', $tipoMovilidad);
    }

    public static function getUser() {
        return [
            'id' => self::get('usuario_id'),
            'nombre' => self::get('nombre'),
            'email' => self::get('email'),
            'tipo_movilidad' => self::get('tipo_movilidad')
        ];
    }

    public static function generateToken() {
        if (!isset($_SESSION[self::SESSION_PREFIX . 'csrf_token'])) {
            $_SESSION[self::SESSION_PREFIX . 'csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_PREFIX . 'csrf_token'];
    }

    public static function validateToken($token) {
        $stored = $_SESSION[self::SESSION_PREFIX . 'csrf_token'] ?? null;
        return $stored && hash_equals($stored, $token);
    }
}

// Inicializar sesiones
SessionManager::init();
