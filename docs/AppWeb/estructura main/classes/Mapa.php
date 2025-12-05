<?php
/**
 * Clase Mapa
 * Maneja funciones relacionadas con mapas y geocoding
 */

require_once dirname(__DIR__) . '/includes/functions.php';

class Mapa {
    private $nomitim_url = NOMINATIM_API_URL;
    private $google_api_key = GOOGLE_MAPS_API_KEY;

    /**
     * Geocodificar dirección (convertir dirección a coordenadas)
     */
    public function geocodificar($direccion, $ciudad = 'Ciudad de México') {
        $query = urlencode("$direccion, $ciudad");
        $url = "$this->nomitim_url/search?q=$query&format=json&limit=5&countrycodes=mx";

        $response = @file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 5, 'user_agent' => 'Movilidad-CDMX/1.0']
        ]));

        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);

        if (empty($data)) {
            return null;
        }

        // Retornar el resultado más relevante
        return [
            'latitude' => (float)$data[0]['lat'],
            'longitude' => (float)$data[0]['lon'],
            'address' => $data[0]['display_name'] ?? $direccion,
            'resultados' => array_slice($data, 0, 5)
        ];
    }

    /**
     * Reverse geocoding (convertir coordenadas a dirección)
     */
    public function reverseGeocode($lat, $lng) {
        $url = "$this->nomitim_url/reverse?format=json&lat=$lat&lon=$lng&zoom=18&addressdetails=1";

        $response = @file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 5, 'user_agent' => 'Movilidad-CDMX/1.0']
        ]));

        if ($response === false) {
            return null;
        }

        $data = json_decode($response, true);

        return [
            'address' => $data['display_name'] ?? null,
            'datos' => $data['address'] ?? null
        ];
    }

    /**
     * Generar URL de mapa estático
     */
    public function generarMapaEstatico($lat, $lng, $zoom = 15, $ancho = 600, $alto = 400, $marcadores = []) {
        $url = "https://maps.googleapis.com/maps/api/staticmap?";
        $params = [
            'center' => "$lat,$lng",
            'zoom' => $zoom,
            'size' => "{$ancho}x{$alto}",
            'maptype' => 'roadmap',
            'key' => $this->google_api_key
        ];

        // Agregar marcadores
        foreach ($marcadores as $marcador) {
            $params['markers'][] = "{$marcador['lat']},{$marcador['lng']}" . 
                                  (!empty($marcador['label']) ? "|label:{$marcador['label']}" : "");
        }

        return $url . http_build_query($params);
    }

    /**
     * Crear objeto de mapa para Leaflet
     */
    public function crearMapaLeaflet($lat = CDMX_LAT, $lng = CDMX_LNG, $zoom = CDMX_ZOOM) {
        return [
            'centro' => ['lat' => $lat, 'lng' => $lng],
            'zoom' => $zoom,
            'tiles' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'atribuciones' => '&copy; OpenStreetMap contributors'
        ];
    }

    /**
     * Validar si coordenadas están en CDMX
     */
    public static function estaDentroDelimiteCDMX($lat, $lng) {
        // Límites aproximados de CDMX
        $minLat = 19.0;
        $maxLat = 19.6;
        $minLng = -99.5;
        $maxLng = -98.9;

        return $lat >= $minLat && $lat <= $maxLat && 
               $lng >= $minLng && $lng <= $maxLng;
    }

    /**
     * Calcular puntos intermedios en una línea
     */
    public static function calcularPuntosIntermedios($lat1, $lng1, $lat2, $lng2, $cantidad = 10) {
        $puntos = [];
        
        for ($i = 0; $i <= $cantidad; $i++) {
            $lat = $lat1 + ($lat2 - $lat1) * ($i / $cantidad);
            $lng = $lng1 + ($lng2 - $lng1) * ($i / $cantidad);
            $puntos[] = ['lat' => $lat, 'lng' => $lng];
        }

        return $puntos;
    }

    /**
     * Crear GeoJSON de ruta
     */
    public static function crearGeoJSONRuta($coordenadas, $propiedades = []) {
        $coordinates = [];
        
        foreach ($coordenadas as $punto) {
            $coordinates[] = [(float)$punto['lng'], (float)$punto['lat']];
        }

        return [
            'type' => 'Feature',
            'properties' => $propiedades,
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $coordinates
            ]
        ];
    }

    /**
     * Crear GeoJSON de punto
     */
    public static function crearGeoJSONPunto($lat, $lng, $propiedades = []) {
        return [
            'type' => 'Feature',
            'properties' => $propiedades,
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float)$lng, (float)$lat]
            ]
        ];
    }

    /**
     * Crear marcador HTML para mapa
     */
    public static function crearMarcadorHTML($tipo, $propiedades = []) {
        $templates = [
            'partida' => '🟢',
            'destino' => '🔴',
            'incidente' => '🚨',
            'ciclovía' => '🚴',
            'alumbrado' => '💡',
            'accesibilidad' => '♿'
        ];

        return $templates[$tipo] ?? '📍';
    }

    /**
     * Estimar tiempo de viaje
     */
    public static function estimarTiempoViaje($distancia_km, $tipo_movilidad = 'pie') {
        // Velocidades promedio (km/h)
        $velocidades = [
            'pie' => 5,
            'bicicleta' => 15,
            'silla_ruedas' => 4
        ];

        $velocidad = $velocidades[$tipo_movilidad] ?? 5;
        $horas = $distancia_km / $velocidad;
        $minutos = (int)($horas * 60);

        return [
            'minutos' => $minutos,
            'horas' => (int)$horas,
            'formato' => formatTime($minutos * 60)
        ];
    }
}
