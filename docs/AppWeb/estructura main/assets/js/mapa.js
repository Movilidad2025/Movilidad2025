/**
 * JavaScript para Mapas
 * Funciones específicas para manejo de mapas con Leaflet
 */

let map = null;
let routeLayer = null;
let markersLayer = null;
const CDMX_CENTER = [19.4326, -99.1332];

/**
 * Inicializar mapa
 */
function inicializarMapa(config, puntoPartida, puntoDestino, nombrePartida, nombreDestino) {
    // Crear mapa
    map = L.map('mapa').setView(
        [config.centro.lat, config.centro.lng],
        config.zoom
    );

    // Agregar tiles
    L.tileLayer(config.tiles, {
        attribution: config.atribuciones,
        maxZoom: 19
    }).addTo(map);

    // Crear capas
    routeLayer = L.featureGroup().addTo(map);
    markersLayer = L.featureGroup().addTo(map);

    // Agregar marcadores
    const partida = L.marker(
        [puntoPartida.lat, puntoPartida.lng],
        {
            icon: L.icon({
                iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="green"/></svg>',
                iconSize: [32, 32],
                popupAnchor: [0, -16]
            })
        }
    ).bindPopup(`<strong>Partida</strong><br>${nombrePartida}`).addTo(markersLayer);

    const destino = L.marker(
        [puntoDestino.lat, puntoDestino.lng],
        {
            icon: L.icon({
                iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" fill="red"/></svg>',
                iconSize: [32, 32],
                popupAnchor: [0, -16]
            })
        }
    ).bindPopup(`<strong>Destino</strong><br>${nombreDestino}`).addTo(markersLayer);

    // Dibujar ruta entre puntos
    const latlngs = [
        [puntoPartida.lat, puntoPartida.lng],
        [puntoDestino.lat, puntoDestino.lng]
    ];

    L.polyline(latlngs, {
        color: '#2ecc71',
        weight: 3,
        opacity: 0.7,
        dashArray: '5, 5'
    }).addTo(routeLayer);

    // Ajustar vista
    map.fitBounds(markersLayer.getBounds(), { padding: [50, 50] });

    // Agregar controles
    L.control.scale().addTo(map);

    return map;
}

/**
 * Agregar datos al mapa
 */
function agregarDatosAlMapa(tipo, datos) {
    if (!map) return;

    const colores = {
        calidad_aire: '#FF6B6B',
        ciclovias: '#4ECDC4',
        alumbrado: '#FFE66D',
        accesibilidad: '#95E1D3',
        incidente: '#FF6348'
    };

    switch (tipo) {
        case 'calidad_aire':
            agregarCalidadAire(datos, colores.calidad_aire);
            break;
        case 'ciclovias':
            agregarCiclovias(datos, colores.ciclovias);
            break;
        case 'alumbrado':
            agregarAlumbrado(datos, colores.alumbrado);
            break;
        case 'accesibilidad':
            agregarAccesibilidad(datos, colores.accesibilidad);
            break;
        case 'incidentes':
            agregarIncidentes(datos, colores.incidente);
            break;
    }
}

/**
 * Agregar puntos de calidad del aire
 */
function agregarCalidadAire(estaciones, color) {
    estaciones.forEach(estacion => {
        // Extraer coordenadas (si están en formato POINT)
        const coords = extraerCoordenadas(estacion.ubicacion);
        if (!coords) return;

        const circle = L.circleMarker([coords.lat, coords.lng], {
            radius: 8,
            fillColor: color,
            color: '#000',
            weight: 2,
            opacity: 0.8,
            fillOpacity: 0.6
        }).bindPopup(`
            <strong>${estacion.nombre_estacion}</strong><br>
            Índice: ${estacion.indice_calidad}<br>
            PM2.5: ${estacion.pm25} µg/m³<br>
            PM10: ${estacion.pm10} µg/m³
        `).addTo(markersLayer);
    });
}

/**
 * Agregar ciclovías
 */
function agregarCiclovias(ciclovias, color) {
    ciclovias.forEach(ciclovia => {
        const coords = extraerCoordenadas(ciclovia.coordenadas);
        if (!coords) return;

        const marker = L.marker([coords.lat, coords.lng], {
            icon: L.icon({
                iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><text x="2" y="18" font-size="16">🚴</text></svg>',
                iconSize: [24, 24]
            })
        }).bindPopup(`
            <strong>${ciclovia.nombre}</strong><br>
            Longitud: ${ciclovia.longitud_km} km<br>
            Estado: ${ciclovia.estado_conservacion}<br>
            ${ciclovia.iluminada ? '💡 Iluminada' : 'Sin iluminación'}
        `).addTo(markersLayer);
    });
}

/**
 * Agregar alumbrado
 */
function agregarAlumbrado(luminarias, color) {
    luminarias.forEach(lum => {
        const coords = extraerCoordenadas(lum.ubicacion);
        if (!coords) return;

        const iconColor = lum.estado_funcionamiento === 'funcionando' ? color : '#999';
        const circle = L.circleMarker([coords.lat, coords.lng], {
            radius: 5,
            fillColor: iconColor,
            color: '#000',
            weight: 1,
            opacity: 0.7,
            fillOpacity: 0.7
        }).bindPopup(`
            Alumbrado: ${lum.calle}<br>
            Estado: ${lum.estado_funcionamiento}<br>
            Potencia: ${lum.potencia_watts}W
        `).addTo(markersLayer);
    });
}

/**
 * Agregar puntos de accesibilidad
 */
function agregarAccesibilidad(puntos, color) {
    puntos.forEach(punto => {
        const coords = extraerCoordenadas(punto.ubicacion);
        if (!coords) return;

        const marker = L.marker([coords.lat, coords.lng], {
            icon: L.icon({
                iconUrl: 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><text x="2" y="18" font-size="16">♿</text></svg>',
                iconSize: [24, 24]
            })
        }).bindPopup(`
            <strong>${punto.tipo}</strong><br>
            Condición: ${punto.condicion}<br>
            ${punto.descripcion || 'Sin descripción'}
        `).addTo(markersLayer);
    });
}

/**
 * Agregar incidentes
 */
function agregarIncidentes(incidentes, color) {
    incidentes.forEach(incidente => {
        const coords = extraerCoordenadas(incidente.ubicacion);
        if (!coords) return;

        const severidadColor = {
            'baja': '#FFD700',
            'media': '#FFA500',
            'alta': '#FF6347',
            'critica': '#FF0000'
        };

        const circle = L.circleMarker([coords.lat, coords.lng], {
            radius: 10,
            fillColor: severidadColor[incidente.severidad] || color,
            color: '#000',
            weight: 2,
            opacity: 0.9,
            fillOpacity: 0.7
        }).bindPopup(`
            <strong>🚨 ${incidente.tipo}</strong><br>
            Severidad: ${incidente.severidad}<br>
            Estado: ${incidente.estado}<br>
            ${incidente.descripcion}
        `).addTo(markersLayer);
    });
}

/**
 * Extraer coordenadas de formato POINT
 */
function extraerCoordenadas(punto) {
    if (!punto) return null;

    let lat, lng;

    // Si es string (formato POINT de MySQL)
    if (typeof punto === 'string') {
        const match = punto.match(/POINT\(([0-9.-]+)\s+([0-9.-]+)\)/);
        if (match) {
            lng = parseFloat(match[1]);
            lat = parseFloat(match[2]);
        } else {
            const parts = punto.split(',');
            if (parts.length === 2) {
                lat = parseFloat(parts[0]);
                lng = parseFloat(parts[1]);
            }
        }
    }
    // Si es objeto con propiedades lat/lng
    else if (typeof punto === 'object') {
        lat = punto.lat || punto.latitude;
        lng = punto.lng || punto.longitude;
    }

    if (lat && lng) {
        return { lat: parseFloat(lat), lng: parseFloat(lng) };
    }

    return null;
}

/**
 * Limpiar marcadores del mapa
 */
function limpiarMarcadores() {
    if (markersLayer) {
        markersLayer.clearLayers();
    }
}

/**
 * Limpiar rutas del mapa
 */
function limpiarRutas() {
    if (routeLayer) {
        routeLayer.clearLayers();
    }
}

/**
 * Centrar mapa en punto
 */
function centrarEnPunto(lat, lng, zoom = 15) {
    if (map) {
        map.setView([lat, lng], zoom);
    }
}

/**
 * Hacer zoom en bounds
 */
function zoomEnBounds(bounds) {
    if (map) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }
}

/**
 * Agregar evento de click en mapa
 */
function onMapClick(callback) {
    if (map) {
        map.on('click', function(e) {
            callback(e.latlng.lat, e.latlng.lng);
        });
    }
}

/**
 * Remover evento de click en mapa
 */
function removeMapClick() {
    if (map) {
        map.off('click');
    }
}

/**
 * Obtener zoom actual
 */
function getMapZoom() {
    return map ? map.getZoom() : null;
}

/**
 * Establecer zoom
 */
function setMapZoom(zoom) {
    if (map) {
        map.setZoom(zoom);
    }
}

/**
 * Exportar mapa como imagen
 */
function exportarMapa() {
    if (!map) return;

    // Usar librería externa o captura HTML5
    alert('Función de exportar en desarrollo');
}

/**
 * Alternar capas
 */
function toggleLayer(layerName) {
    // Implementar según necesidad
    log('Toggle layer:', layerName);
}

/**
 * Buscar lugar en mapa
 */
function buscarLugarEnMapa(query) {
    // Usar Nominatim API
    const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1`;

    return fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                const punto = data[0];
                centrarEnPunto(parseFloat(punto.lat), parseFloat(punto.lon), 15);
                return { lat: punto.lat, lng: punto.lon };
            }
            return null;
        })
        .catch(error => {
            console.error('Error en búsqueda:', error);
            return null;
        });
}

/**
 * Dibujar círculo en mapa
 */
function dibujarCirculo(lat, lng, radio, opciones = {}) {
    const defaultOptions = {
        color: '#2ecc71',
        weight: 2,
        opacity: 0.5,
        fillOpacity: 0.2,
        fillColor: '#2ecc71'
    };

    const finalOptions = { ...defaultOptions, ...opciones };

    return L.circle([lat, lng], radio, finalOptions).addTo(map);
}

/**
 * Obtener distancia entre dos puntos (en metros)
 */
function distanciaEntrePuntos(lat1, lng1, lat2, lng2) {
    const R = 6371000; // Tierra en metros
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLng / 2) * Math.sin(dLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

/**
 * Mostrar información en popup
 */
function mostrarPopup(lat, lng, contenido) {
    L.popup()
        .setLatLng([lat, lng])
        .setContent(contenido)
        .openOn(map);
}
