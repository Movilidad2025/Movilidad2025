<?php
/**
 * Conexión a Base de Datos
 * Maneja la conexión MySQLi con manejo de errores
 */

require_once 'config.php';

class Database {
    private static $instance = null;
    private $connection;
    private $error;

    private function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        // Crear conexión
        $this->connection = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME
        );

        // Verificar conexión
        if ($this->connection->connect_error) {
            $this->error = 'Error de conexión: ' . $this->connection->connect_error;
            if (DEBUG) {
                die($this->error);
            } else {
                die('Error de conexión a la base de datos. Por favor intente más tarde.');
            }
        }

        // Configurar charset
        if (!$this->connection->set_charset(DB_CHARSET)) {
            $this->error = 'Error al configurar charset: ' . $this->connection->error;
            if (DEBUG) {
                die($this->error);
            }
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    public function getLastInsertId() {
        return $this->connection->insert_id;
    }

    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }

    public function getError() {
        return $this->connection->error;
    }

    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function __destruct() {
        $this->closeConnection();
    }
}

// Obtener instancia de base de datos
$db = Database::getInstance();
