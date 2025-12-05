-- =====================================================
-- Base de Datos Movilidad CDMX
-- Script de creación de tablas
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS movilidad_cdmx
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE movilidad_cdmx;

-- =====================================================
-- Tabla de Usuarios
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    tipo_movilidad ENUM('pie', 'bicicleta', 'silla_ruedas') DEFAULT NULL,
    foto_perfil VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_email (email),
    INDEX idx_tipo_movilidad (tipo_movilidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla de Rutas Guardadas
-- =====================================================
CREATE TABLE IF NOT EXISTS rutas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    nombre_ruta VARCHAR(100) NOT NULL,
    punto_partida VARCHAR(255) NOT NULL,
    punto_destino VARCHAR(255) NOT NULL,
    tipo_movilidad ENUM('pie', 'bicicleta', 'silla_ruedas') NOT NULL,
    coordenadas_partida POINT COMMENT 'Latitud y Longitud de partida',
    coordenadas_destino POINT COMMENT 'Latitud y Longitud de destino',
    distancia_km DECIMAL(8,2),
    tiempo_estimado_minutos INT,
    ruta_json LONGTEXT COMMENT 'Coordenadas completas de la ruta en JSON',
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    favorita BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo_movilidad (tipo_movilidad),
    INDEX idx_favorita (favorita),
    SPATIAL INDEX spx_partida (coordenadas_partida),
    SPATIAL INDEX spx_destino (coordenadas_destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla de Calidad del Aire
-- =====================================================
CREATE TABLE IF NOT EXISTS calidad_aire (
    id INT PRIMARY KEY AUTO_INCREMENT,
    estacion_id VARCHAR(50) UNIQUE NOT NULL,
    nombre_estacion VARCHAR(100) NOT NULL,
    ubicacion POINT NOT NULL,
    pm25 DECIMAL(5,2) COMMENT 'Partículas menores a 2.5 micras (µg/m³)',
    pm10 DECIMAL(5,2) COMMENT 'Partículas menores a 10 micras (µg/m³)',
    o3 DECIMAL(5,2) COMMENT 'Ozono (ppb)',
    no2 DECIMAL(5,2) COMMENT 'Dióxido de Nitrógeno (ppb)',
    so2 DECIMAL(5,2) COMMENT 'Dióxido de Azufre (ppb)',
    co DECIMAL(5,2) COMMENT 'Monóxido de Carbono (ppm)',
    fecha_medicion DATETIME NOT NULL,
    indice_calidad VARCHAR(50) COMMENT 'Buena, Aceptable, Mala, Muy Mala',
    valor_indice INT COMMENT 'Valor numérico del índice (0-500)',
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estacion (estacion_id),
    INDEX idx_fecha (fecha_medicion),
    INDEX idx_indice (indice_calidad),
    SPATIAL INDEX spx_ubicacion (ubicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla de Ciclovías
-- =====================================================
CREATE TABLE IF NOT EXISTS ciclovias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    longitud_km DECIMAL(6,2) NOT NULL,
    coordenadas LINESTRING NOT NULL COMMENT 'Ruta de la ciclovía',
    colonia VARCHAR(100),
    alcaldia VARCHAR(100),
    iluminada BOOLEAN DEFAULT FALSE,
    estado_conservacion ENUM('excelente', 'bueno', 'regular', 'malo') DEFAULT 'bueno',
    tipo_superficie ENUM('asfalto', 'concreto', 'adoquín', 'tierra') DEFAULT 'asfalto',
    bidireccional BOOLEAN DEFAULT TRUE,
    ancho_aproximado_metros DECIMAL(4,2),
    fecha_construccion DATE,
    ultimo_mantenimiento DATE,
    activa BOOLEAN DEFAULT TRUE,
    INDEX idx_alcaldia (alcaldia),
    INDEX idx_colonia (colonia),
    INDEX idx_estado (estado_conservacion),
    SPATIAL INDEX spx_coordenadas (coordenadas)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla de Alumbrado Público
-- =====================================================
CREATE TABLE IF NOT EXISTS alumbrado (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ubicacion POINT NOT NULL,
    calle VARCHAR(150) NOT NULL,
    numero_calle VARCHAR(20),
    colonia VARCHAR(100),
    alcaldia VARCHAR(100),
    tipo_luminaria VARCHAR(50) COMMENT 'LED, Sodio, Mercurio, Halógeno',
    potencia_watts INT,
    estado_funcionamiento ENUM('funcionando', 'parcial', 'no_funciona') DEFAULT 'funcionando',
    altura_metros DECIMAL(4,2),
    fecha_instalacion DATE,
    ultimo_mantenimiento DATE,
    proximo_mantenimiento DATE,
    folio_mantenimiento VARCHAR(50),
    observaciones TEXT,
    INDEX idx_alcaldia (alcaldia),
    INDEX idx_colonia (colonia),
    INDEX idx_estado (estado_funcionamiento),
    SPATIAL INDEX spx_ubicacion (ubicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla de Accesibilidad (Para Silla de Ruedas)
-- =====================================================
CREATE TABLE IF NOT EXISTS accesibilidad (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ubicacion POINT NOT NULL,
    tipo ENUM('rampa', 'paso_elevado', 'cruce_seguro', 'ascensor', 'escalera', 'banqueta') NOT NULL,
    calle VARCHAR(150),
    numero_calle VARCHAR(20),
    colonia VARCHAR(100),
    alcaldia VARCHAR(100),
    descripcion TEXT,
    condicion ENUM('accesible', 'parcialmente_accesible', 'inaccesible') DEFAULT 'accesible',
    ancho_minimo_metros DECIMAL(4,2),
    pendiente_aproximada DECIMAL(5,2) COMMENT 'Porcentaje',
    foto_url VARCHAR(255),
    reportado_por_usuario BOOLEAN DEFAULT FALSE,
    fecha_reporte DATE,
    verificado BOOLEAN DEFAULT FALSE,
    INDEX idx_alcaldia (alcaldia),
    INDEX idx_colonia (colonia),
    INDEX idx_tipo (tipo),
    INDEX idx_condicion (condicion),
    SPATIAL INDEX spx_ubicacion (ubicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla de Incidentes/Reportes
-- =====================================================
CREATE TABLE IF NOT EXISTS incidentes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    ubicacion POINT NOT NULL,
    tipo ENUM('bache', 'inundacion', 'escombro', 'obstaculo', 'acoso', 'robo', 'otro') NOT NULL,
    descripcion TEXT NOT NULL,
    severidad ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
    foto_url VARCHAR(255),
    estado ENUM('reportado', 'verificado', 'en_solucion', 'resuelto', 'rechazado') DEFAULT 'reportado',
    fecha_reporte TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion DATETIME,
    votos_util INT DEFAULT 0,
    visible_publico BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado),
    INDEX idx_severidad (severidad),
    SPATIAL INDEX spx_ubicacion (ubicacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla de Historial de Rutas
-- =====================================================
CREATE TABLE IF NOT EXISTS historial_rutas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    ruta_id INT,
    punto_partida VARCHAR(255) NOT NULL,
    punto_destino VARCHAR(255) NOT NULL,
    tipo_movilidad ENUM('pie', 'bicicleta', 'silla_ruedas') NOT NULL,
    fecha_consulta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (ruta_id) REFERENCES rutas(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_consulta)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla de Preferencias de Usuario
-- =====================================================
CREATE TABLE IF NOT EXISTS preferencias_usuario (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT UNIQUE NOT NULL,
    evitar_areas_peligrosas BOOLEAN DEFAULT TRUE,
    mostrar_calidad_aire BOOLEAN DEFAULT TRUE,
    mostrar_alumbrado BOOLEAN DEFAULT TRUE,
    mostrar_ciclovias BOOLEAN DEFAULT TRUE,
    mostrar_accesibilidad BOOLEAN DEFAULT TRUE,
    distancia_maxima_km INT DEFAULT 50,
    mostrar_incidentes BOOLEAN DEFAULT TRUE,
    notificaciones_activas BOOLEAN DEFAULT TRUE,
    tema_interfaz ENUM('claro', 'oscuro') DEFAULT 'claro',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Crear índices adicionales para optimización
-- =====================================================

CREATE INDEX idx_rutas_usuario_tipo ON rutas(usuario_id, tipo_movilidad);
CREATE INDEX idx_calidad_aire_fecha_estacion ON calidad_aire(fecha_medicion, estacion_id);
CREATE INDEX idx_incidentes_ubicacion_fecha ON incidentes(fecha_reporte);

-- =====================================================
-- Insertar datos de ejemplo (opcional)
-- =====================================================

-- Insertar estación de calidad del aire de ejemplo
INSERT IGNORE INTO calidad_aire 
(estacion_id, nombre_estacion, ubicacion, pm25, pm10, o3, no2, so2, co, fecha_medicion, indice_calidad, valor_indice)
VALUES
('CDMX_01', 'Xalostoc', POINT(19.4901, -99.1070), 45.0, 65.0, 120.0, 85.0, 15.0, 2.5, NOW(), 'Aceptable', 85),
('CDMX_02', 'La Merced', POINT(19.4395, -99.1350), 55.0, 75.0, 130.0, 95.0, 20.0, 3.0, NOW(), 'Mala', 120),
('CDMX_03', 'Pedregal', POINT(19.3503, -99.2250), 35.0, 50.0, 100.0, 65.0, 10.0, 1.8, NOW(), 'Buena', 65);

-- Insertar ciclovías de ejemplo
INSERT IGNORE INTO ciclovias
(nombre, longitud_km, coordenadas, colonia, alcaldia, iluminada, estado_conservacion, tipo_superficie)
VALUES
('Ciclovía Paseo de la Reforma', 15.50, 
 LINESTRING(POINT(19.4326, -99.1332), POINT(19.4400, -99.1200), POINT(19.4500, -99.1100)),
 'Juárez', 'Cuauhtémoc', TRUE, 'excelente', 'asfalto'),
('Ciclovía Centro Histórico', 8.30,
 LINESTRING(POINT(19.4326, -99.1332), POINT(19.4350, -99.1350), POINT(19.4380, -99.1380)),
 'Centro', 'Cuauhtémoc', TRUE, 'bueno', 'concreto');

-- Insertar alumbrado de ejemplo
INSERT IGNORE INTO alumbrado
(ubicacion, calle, numero_calle, colonia, alcaldia, tipo_luminaria, potencia_watts, estado_funcionamiento, altura_metros)
VALUES
(POINT(19.4326, -99.1332), 'Avenida Paseo de la Reforma', '100', 'Juárez', 'Cuauhtémoc', 'LED', 150, 'funcionando', 8.5),
(POINT(19.4340, -99.1350), 'Avenida Madero', '250', 'Centro', 'Cuauhtémoc', 'LED', 150, 'funcionando', 8.5),
(POINT(19.4350, -99.1360), 'Calle Francisco I. Madero', '300', 'Centro', 'Cuauhtémoc', 'LED', 120, 'parcial', 8.0);

-- Insertar accesibilidad de ejemplo
INSERT IGNORE INTO accesibilidad
(ubicacion, tipo, calle, colonia, alcaldia, descripcion, condicion, ancho_minimo_metros)
VALUES
(POINT(19.4326, -99.1332), 'rampa', 'Avenida Paseo de la Reforma', 'Juárez', 'Cuauhtémoc', 'Rampa de acceso a metro', 'accesible', 1.8),
(POINT(19.4340, -99.1350), 'cruce_seguro', 'Avenida Madero', 'Centro', 'Cuauhtémoc', 'Cruce peatonal señalizado', 'accesible', 2.0),
(POINT(19.4350, -99.1360), 'banqueta', 'Calle Francisco I. Madero', 'Centro', 'Cuauhtémoc', 'Banqueta aplanada', 'parcialmente_accesible', 1.5);

-- =====================================================
-- Verificar tablas creadas
-- =====================================================
SHOW TABLES;
