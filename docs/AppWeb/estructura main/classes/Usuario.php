<?php
/**
 * Clase Usuario
 * Maneja operaciones relacionadas con usuarios
 */

require_once dirname(__DIR__) . '/includes/database.php';

class Usuario {
    private $db;
    private $id;
    private $nombre;
    private $email;
    private $tipo_movilidad;

    public function __construct($id = null) {
        $this->db = Database::getInstance()->getConnection();
        if ($id) {
            $this->loadById($id);
        }
    }

    /**
     * Cargar usuario por ID
     */
    public function loadById($id) {
        $stmt = $this->db->prepare('SELECT id, nombre, email, tipo_movilidad FROM usuarios WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            $this->email = $row['email'];
            $this->tipo_movilidad = $row['tipo_movilidad'];
            return true;
        }
        return false;
    }

    /**
     * Obtener ID del usuario
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Obtener nombre del usuario
     */
    public function getNombre() {
        return $this->nombre;
    }

    /**
     * Obtener email del usuario
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Obtener tipo de movilidad
     */
    public function getTipoMovilidad() {
        return $this->tipo_movilidad;
    }

    /**
     * Actualizar tipo de movilidad
     */
    public function setTipoMovilidad($tipo) {
        if (!isValidMobilityType($tipo)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE usuarios SET tipo_movilidad = ? WHERE id = ?');
        $stmt->bind_param('si', $tipo, $this->id);

        if ($stmt->execute()) {
            $this->tipo_movilidad = $tipo;
            return true;
        }
        return false;
    }

    /**
     * Actualizar último acceso
     */
    public function updateLastAccess() {
        $stmt = $this->db->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?');
        $stmt->bind_param('i', $this->id);
        return $stmt->execute();
    }

    /**
     * Obtener preferencias del usuario
     */
    public function getPreferencias() {
        $stmt = $this->db->prepare(
            'SELECT * FROM preferencias_usuario WHERE usuario_id = ?'
        );
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc();
    }

    /**
     * Actualizar preferencias del usuario
     */
    public function updatePreferencias($preferencias) {
        $stmt = $this->db->prepare(
            'INSERT INTO preferencias_usuario (usuario_id, ' . implode(', ', array_keys($preferencias)) . ')
             VALUES (?, ' . implode(', ', array_fill(0, count($preferencias), '?')) . ')
             ON DUPLICATE KEY UPDATE ' . 
             implode(', ', array_map(function($key) { return "$key = VALUES($key)"; }, array_keys($preferencias)))
        );

        $types = 'i' . str_repeat('s', count($preferencias));
        $values = array_merge([$this->id], array_values($preferencias));

        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    /**
     * Obtener datos del perfil
     */
    public function getPerfil() {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'tipo_movilidad' => $this->tipo_movilidad,
            'tipo_movilidad_nombre' => getMobilityTypeName($this->tipo_movilidad),
            'preferencias' => $this->getPreferencias()
        ];
    }

    /**
     * Obtener rutas guardadas
     */
    public function getRutasGuardadas($limite = 10) {
        $stmt = $this->db->prepare(
            'SELECT * FROM rutas WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT ?'
        );
        $stmt->bind_param('ii', $this->id, $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener rutas favoritas
     */
    public function getRutasFavoritas() {
        $stmt = $this->db->prepare(
            'SELECT * FROM rutas WHERE usuario_id = ? AND favorita = TRUE ORDER BY fecha_creacion DESC'
        );
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener historial de rutas
     */
    public function getHistorialRutas($limite = 20) {
        $stmt = $this->db->prepare(
            'SELECT * FROM historial_rutas WHERE usuario_id = ? ORDER BY fecha_consulta DESC LIMIT ?'
        );
        $stmt->bind_param('ii', $this->id, $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Guardar ruta en historial
     */
    public function guardarEnHistorial($punto_partida, $punto_destino, $tipo_movilidad, $ruta_id = null) {
        $stmt = $this->db->prepare(
            'INSERT INTO historial_rutas (usuario_id, ruta_id, punto_partida, punto_destino, tipo_movilidad)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iisss', $this->id, $ruta_id, $punto_partida, $punto_destino, $tipo_movilidad);
        return $stmt->execute();
    }

    /**
     * Verificar si usuario existe
     */
    public static function exists($email) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
