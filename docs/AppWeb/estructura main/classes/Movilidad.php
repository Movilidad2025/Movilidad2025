<?php
/**
 * Clase Movilidad
 * Maneja datos específicos según tipo de movilidad
 */

require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

class Movilidad {
    private $db;
    private $tipo_movilidad;

    public function __construct($tipo_movilidad = null) {
        $this->db = Database::getInstance()->getConnection();
        $this->tipo_movilidad = $tipo_movilidad;
    }

    /**
     * Obtener datos de movilidad según tipo
     */
    public function obtenerDatos($lat1, $lng1, $lat2, $lng2) {
        if (!$this->tipo_movilidad) {
            return [];
        }

        $datos = [
            'tipo_movilidad' => $this->tipo_movilidad,
            'calidad_aire' => $this->obtenerCalidadAire($lat1, $lng1, $lat2, $lng2)
        ];

        switch ($this->tipo_movilidad) {
            case 'pie':
                $datos['alumbrado'] = $this->obtenerAlumbrado($lat1, $lng1, $lat2, $lng2);
                break;
            case 'bicicleta':
                $datos['ciclovias'] = $this->obtenerCiclovias($lat1, $lng1, $lat2, $lng2);
                break;
            case 'silla_ruedas':
                $datos['alumbrado'] = $this->obtenerAlumbrado($lat1, $lng1, $lat2, $lng2);
                $datos['accesibilidad'] = $this->obtenerAccesibilidad($lat1, $lng1, $lat2, $lng2);
                break;
        }

        return $datos;
    }

    /**
     * Obtener calidad del aire en ruta
     */
    public function obtenerCalidadAire($lat1, $lng1, $lat2, $lng2) {
        $stmt = $this->db->prepare(
            'SELECT * FROM calidad_aire 
             WHERE ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 10
             OR ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 10
             ORDER BY fecha_medicion DESC
             LIMIT 5'
        );

        $stmt->bind_param('dddd', $lat1, $lng1, $lat2, $lng2);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener ciclovías cercanas
     */
    public function obtenerCiclovias($lat1, $lng1, $lat2, $lng2) {
        $stmt = $this->db->prepare(
            'SELECT *, 
             ST_Distance_Sphere(ST_PointN(coordenadas, 1), POINT(?, ?)) / 1000 as distancia_partida
             FROM ciclovias
             WHERE activa = TRUE
             AND (ST_Distance_Sphere(ST_PointN(coordenadas, 1), POINT(?, ?)) / 1000 <= 15
             OR ST_Distance_Sphere(ST_PointN(coordenadas, 1), POINT(?, ?)) / 1000 <= 15)
             ORDER BY distancia_partida
             LIMIT 10'
        );

        $stmt->bind_param('dddddd', $lat1, $lng1, $lat1, $lng1, $lat2, $lng2);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener alumbrado público
     */
    public function obtenerAlumbrado($lat1, $lng1, $lat2, $lng2) {
        $stmt = $this->db->prepare(
            'SELECT * FROM alumbrado
             WHERE (ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 5
             OR ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 5)
             AND estado_funcionamiento IN ("funcionando", "parcial")
             LIMIT 20'
        );

        $stmt->bind_param('dddd', $lat1, $lng1, $lat2, $lng2);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener datos de accesibilidad
     */
    public function obtenerAccesibilidad($lat1, $lng1, $lat2, $lng2) {
        $stmt = $this->db->prepare(
            'SELECT * FROM accesibilidad
             WHERE (ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 5
             OR ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 5)
             AND condicion IN ("accesible", "parcialmente_accesible")
             ORDER BY condicion
             LIMIT 20'
        );

        $stmt->bind_param('dddd', $lat1, $lng1, $lat2, $lng2);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener incidentes en ruta
     */
    public function obtenerIncidentes($lat1, $lng1, $lat2, $lng2, $severidades = ['media', 'alta', 'critica']) {
        $placeholders = implode(',', array_fill(0, count($severidades), '?'));
        $query = "SELECT * FROM incidentes
                 WHERE (ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 5
                 OR ST_Distance_Sphere(ubicacion, POINT(?, ?)) / 1000 <= 5)
                 AND estado IN ('reportado', 'verificado')
                 AND severidad IN ($placeholders)
                 AND visible_publico = TRUE
                 ORDER BY fecha_reporte DESC
                 LIMIT 15";

        $stmt = $this->db->prepare($query);
        $params = array_merge([$lat1, $lng1, $lat2, $lng2], $severidades);
        $types = 'dddd' . str_repeat('s', count($severidades));

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtener estadísticas de rutas por tipo
     */
    public static function obtenerEstadisticas() {
        $db = Database::getInstance()->getConnection();
        $resultado = [];

        foreach (MOBILITY_TYPES as $tipo) {
            $stmt = $db->prepare(
                'SELECT COUNT(*) as total FROM rutas WHERE tipo_movilidad = ?'
            );
            $stmt->bind_param('s', $tipo);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $resultado[$tipo] = $result['total'];
        }

        return $resultado;
    }

    /**
     * Obtener recomendaciones de ruta segura
     */
    public function obtenerRutaSegura($lat1, $lng1, $lat2, $lng2) {
        $recomendaciones = [
            'seguridad' => [],
            'infraestructura' => [],
            'advertencias' => []
        ];

        // Obtener incidentes
        $incidentes = $this->obtenerIncidentes($lat1, $lng1, $lat2, $lng2);
        if (!empty($incidentes)) {
            $recomendaciones['advertencias'][] = 'Existen ' . count($incidentes) . ' incidentes reportados en la ruta';
        }

        // Validaciones según tipo de movilidad
        if ($this->tipo_movilidad === 'bicicleta') {
            $ciclovias = $this->obtenerCiclovias($lat1, $lng1, $lat2, $lng2);
            if (empty($ciclovias)) {
                $recomendaciones['advertencias'][] = 'No hay ciclovías disponibles en esta ruta';
            } else {
                $recomendaciones['seguridad'][] = 'Usa las ciclovías disponibles: ' . count($ciclovias) . ' encontradas';
            }
        }

        if ($this->tipo_movilidad === 'silla_ruedas') {
            $accesibilidad = $this->obtenerAccesibilidad($lat1, $lng1, $lat2, $lng2);
            if (count($accesibilidad) < 5) {
                $recomendaciones['advertencias'][] = 'Acceso limitado para silla de ruedas en esta ruta';
            } else {
                $recomendaciones['seguridad'][] = 'Buena accesibilidad en esta ruta';
            }
        }

        // Alumbrado
        $alumbrado = $this->obtenerAlumbrado($lat1, $lng1, $lat2, $lng2);
        if ($this->tipo_movilidad === 'pie' || $this->tipo_movilidad === 'silla_ruedas') {
            if (count($alumbrado) > 10) {
                $recomendaciones['seguridad'][] = 'Buena iluminación en la ruta';
            } else {
                $recomendaciones['advertencias'][] = 'Considere viajar de día o usar iluminación personal';
            }
        }

        return $recomendaciones;
    }
}
