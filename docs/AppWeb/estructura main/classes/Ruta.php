<?php
/**
 * Clase Ruta
 * Maneja operaciones relacionadas con rutas
 */

require_once dirname(__DIR__) . '/includes/database.php';

class Ruta {
    private $db;
    private $id;
    private $usuario_id;
    private $nombre_ruta;
    private $punto_partida;
    private $punto_destino;
    private $tipo_movilidad;
    private $coordenadas_partida;
    private $coordenadas_destino;
    private $distancia_km;
    private $tiempo_estimado_minutos;

    public function __construct($id = null) {
        $this->db = Database::getInstance()->getConnection();
        if ($id) {
            $this->loadById($id);
        }
    }

    /**
     * Cargar ruta por ID
     */
    public function loadById($id) {
        $stmt = $this->db->prepare(
            'SELECT * FROM rutas WHERE id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $this->assignProperties($row);
            return true;
        }
        return false;
    }

    /**
     * Asignar propiedades desde array
     */
    private function assignProperties($data) {
        $this->id = $data['id'] ?? null;
        $this->usuario_id = $data['usuario_id'] ?? null;
        $this->nombre_ruta = $data['nombre_ruta'] ?? null;
        $this->punto_partida = $data['punto_partida'] ?? null;
        $this->punto_destino = $data['punto_destino'] ?? null;
        $this->tipo_movilidad = $data['tipo_movilidad'] ?? null;
        $this->distancia_km = $data['distancia_km'] ?? null;
        $this->tiempo_estimado_minutos = $data['tiempo_estimado_minutos'] ?? null;
    }

    /**
     * Crear nueva ruta
     */
    public function crear(
        $usuario_id,
        $nombre_ruta,
        $punto_partida,
        $punto_destino,
        $tipo_movilidad,
        $lat_partida,
        $lng_partida,
        $lat_destino,
        $lng_destino,
        $distancia_km = null,
        $tiempo_estimado = null,
        $ruta_json = null
    ) {
        if (!isValidMobilityType($tipo_movilidad)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO rutas (
                usuario_id, nombre_ruta, punto_partida, punto_destino,
                tipo_movilidad, coordenadas_partida, coordenadas_destino,
                distancia_km, tiempo_estimado_minutos, ruta_json
            ) VALUES (?, ?, ?, ?, ?, ST_PointFromText(?), ST_PointFromText(?), ?, ?, ?)'
        );

        $point_partida = "POINT($lat_partida $lng_partida)";
        $point_destino = "POINT($lat_destino $lng_destino)";

        $stmt->bind_param(
            'issssssiii',
            $usuario_id,
            $nombre_ruta,
            $punto_partida,
            $punto_destino,
            $tipo_movilidad,
            $point_partida,
            $point_destino,
            $distancia_km,
            $tiempo_estimado,
            $ruta_json
        );

        if ($stmt->execute()) {
            $this->id = $this->db->insert_id;
            return $this->id;
        }
        return false;
    }

    /**
     * Obtener ID
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Obtener nombre de la ruta
     */
    public function getNombre() {
        return $this->nombre_ruta;
    }

    /**
     * Obtener punto de partida
     */
    public function getPuntoPartida() {
        return $this->punto_partida;
    }

    /**
     * Obtener punto destino
     */
    public function getPuntoDestino() {
        return $this->punto_destino;
    }

    /**
     * Obtener tipo de movilidad
     */
    public function getTipoMovilidad() {
        return $this->tipo_movilidad;
    }

    /**
     * Obtener distancia en km
     */
    public function getDistancia() {
        return $this->distancia_km;
    }

    /**
     * Obtener tiempo estimado en minutos
     */
    public function getTiempoEstimado() {
        return $this->tiempo_estimado_minutos;
    }

    /**
     * Marcar como favorita
     */
    public function marcarFavorita($favorita = true) {
        $stmt = $this->db->prepare('UPDATE rutas SET favorita = ? WHERE id = ?');
        $stmt->bind_param('ii', $favorita, $this->id);
        return $stmt->execute();
    }

    /**
     * Actualizar ruta
     */
    public function actualizar($datos) {
        $campos = [];
        $values = [];
        $types = '';

        foreach ($datos as $key => $value) {
            if (in_array($key, ['nombre_ruta', 'descripcion'])) {
                $campos[] = "$key = ?";
                $values[] = $value;
                $types .= 's';
            }
        }

        if (empty($campos)) {
            return false;
        }

        $query = 'UPDATE rutas SET ' . implode(', ', $campos) . ' WHERE id = ?';
        $stmt = $this->db->prepare($query);
        $values[] = $this->id;
        $types .= 'i';

        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    /**
     * Eliminar ruta
     */
    public function eliminar() {
        $stmt = $this->db->prepare('DELETE FROM rutas WHERE id = ?');
        $stmt->bind_param('i', $this->id);
        return $stmt->execute();
    }

    /**
     * Obtener información completa de la ruta
     */
    public function getInfo() {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre_ruta,
            'punto_partida' => $this->punto_partida,
            'punto_destino' => $this->punto_destino,
            'tipo_movilidad' => $this->tipo_movilidad,
            'distancia_km' => $this->distancia_km,
            'tiempo_estimado_minutos' => $this->tiempo_estimado_minutos
        ];
    }

    /**
     * Obtener rutas cercanas
     */
    public static function rutasCercanas($lat, $lng, $distancia_km = 5, $limite = 10) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            'SELECT *, 
             ST_Distance_Sphere(coordenadas_partida, POINT(?, ?)) / 1000 as distancia
             FROM rutas 
             WHERE ST_Distance_Sphere(coordenadas_partida, POINT(?, ?)) / 1000 <= ?
             ORDER BY distancia
             LIMIT ?'
        );

        $stmt->bind_param('ddddi', $lat, $lng, $lat, $lng, $distancia_km, $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Buscar rutas por tipo de movilidad
     */
    public static function buscarPorTipo($tipo_movilidad, $usuario_id = null) {
        $db = Database::getInstance()->getConnection();

        if ($usuario_id) {
            $stmt = $db->prepare(
                'SELECT * FROM rutas WHERE tipo_movilidad = ? AND usuario_id = ? ORDER BY fecha_creacion DESC'
            );
            $stmt->bind_param('si', $tipo_movilidad, $usuario_id);
        } else {
            $stmt = $db->prepare(
                'SELECT * FROM rutas WHERE tipo_movilidad = ? ORDER BY fecha_creacion DESC'
            );
            $stmt->bind_param('s', $tipo_movilidad);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
