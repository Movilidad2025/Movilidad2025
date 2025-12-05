# Movilidad CDMX - README

Aplicación web para mejorar la movilidad en Ciudad de México, mostrando rutas seguras y datos relevantes según el tipo de desplazamiento del usuario.

## 🚀 Características Principales

### Autenticación y Usuarios
- ✓ Sistema de registro e inicio de sesión seguro
- ✓ Gestión de sesiones con timeout automático
- ✓ Contraseñas con hash seguro (bcrypt)
- ✓ Protección contra CSRF

### Tipos de Movilidad
- ✓ **A Pie**: Rutas con mejor iluminación y calidad del aire
- ✓ **Bicicleta**: Ciclovías seguras y bien mantenidas
- ✓ **Silla de Ruedas**: Rutas con infraestructura accesible

### Funcionalidades de Mapas
- ✓ Mapa interactivo con Leaflet
- ✓ Geocodificación de direcciones
- ✓ Cálculo de rutas y distancias
- ✓ Visualización de datos específicos por tipo de movilidad
- ✓ Capas de información personalizable

### Datos Disponibles
- ✓ Calidad del aire en tiempo real
- ✓ Ubicación de ciclovías
- ✓ Alumbrado público
- ✓ Puntos de accesibilidad
- ✓ Reportes de incidentes

### Dashboard
- ✓ Visualización de rutas guardadas
- ✓ Historial de búsquedas
- ✓ Rutas favoritas
- ✓ Información del perfil

## 📋 Requisitos

- PHP 7.4 o superior
- MySQL 8.0 o superior
- Servidor web (Apache o Nginx)
- Navegador moderno

## 🔧 Instalación Rápida

### 1. Clonar el repositorio
```bash
cd /var/www/html
git clone https://github.com/tu-usuario/movilidad-cdmx.git
cd movilidad-cdmx
```

### 2. Configurar base de datos
```bash
mysql -u root -p < database/schema.sql
```

### 3. Configurar aplicación
Editar `includes/config.php` con tus credenciales:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'movilidad_cdmx');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
```

### 4. Acceder
```
http://localhost/movilidad-cdmx/
```

Ver [INSTALACION.md](INSTALACION.md) para instrucciones detalladas.

## 📁 Estructura del Proyecto

```
movilidad-cdmx/
├── index.php                 # Página de inicio
├── login.php                 # Login
├── registro.php              # Registro
├── dashboard.php             # Panel del usuario
├── seleccion-movilidad.php   # Elegir tipo de movilidad
├── configurar-ruta.php       # Configurar puntos
├── mapa-ruta.php            # Visualizar ruta
├── logout.php               # Cerrar sesión
│
├── includes/                # Configuración e incluye
│   ├── config.php           # Constantes
│   ├── database.php         # Conexión BD
│   ├── session.php          # Sesiones
│   ├── auth.php             # Autenticación
│   └── functions.php        # Funciones auxiliares
│
├── classes/                 # Clases principales
│   ├── Usuario.php
│   ├── Ruta.php
│   ├── Movilidad.php
│   └── Mapa.php
│
├── api/                     # Endpoints API
│   ├── rutas.php
│   ├── usuarios.php
│   └── datos-movilidad.php
│
├── assets/                  # Recursos estáticos
│   ├── css/
│   ├── js/
│   └── images/
│
└── database/
    └── schema.sql           # Esquema de BD
```

## 🎯 Flujo de Uso

1. **Inicio**: Visitante ve página de inicio
2. **Registro/Login**: Se autentica en la plataforma
3. **Selección**: Elige su tipo de movilidad (pie, bicicleta, silla de ruedas)
4. **Configuración**: Ingresa punto de partida y destino
5. **Visualización**: Ve la ruta en mapa con datos relevantes
6. **Guardar**: Guarda rutas frecuentes como favoritas

## 🔌 API Endpoints

### Rutas
- `POST /api/rutas.php?accion=calcular` - Calcular ruta
- `POST /api/rutas.php?accion=guardar` - Guardar ruta
- `GET /api/rutas.php?accion=listar` - Listar rutas del usuario

### Datos de Movilidad
- `GET /api/datos-movilidad.php?tipo=calidad-aire&lat=X&lng=Y`
- `GET /api/datos-movilidad.php?tipo=ciclovias&lat=X&lng=Y`
- `GET /api/datos-movilidad.php?tipo=alumbrado&lat=X&lng=Y`

### Usuarios
- `GET /api/usuarios.php?accion=perfil` - Obtener perfil
- `POST /api/usuarios.php?accion=actualizar-movilidad` - Cambiar tipo

## 🛡️ Seguridad

- Validación de entrada en cliente y servidor
- Prepared statements contra inyecciones SQL
- Hash de contraseñas con bcrypt
- Tokens CSRF en todos los formularios
- Sesiones seguras con timeout
- Sanitización de output contra XSS

## 📱 Responsive

La aplicación es totalmente responsive:
- Desktop (> 1024px)
- Tablet (768px - 1024px)
- Mobile (< 768px)

## 🗺️ Tecnologías

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript Vanilla
- **Mapas**: Leaflet.js
- **Geocoding**: Nominatim (OpenStreetMap)

## 📊 Base de Datos

Tablas principales:
- `usuarios` - Registro de usuarios
- `rutas` - Rutas guardadas
- `calidad_aire` - Estaciones de monitoreo
- `ciclovias` - Red de ciclovías
- `alumbrado` - Puntos de iluminación
- `accesibilidad` - Puntos accesibles
- `incidentes` - Reportes de usuarios

## 🐛 Conocidos

- Nominatim puede tener límites de rate (usar cache)
- Mapa requiere conexión a internet
- GPS no funciona en localhost HTTP

## 🚀 Próximas Mejoras

- [ ] Autenticación con Google/Facebook
- [ ] Aplicación móvil nativa
- [ ] Notificaciones en tiempo real
- [ ] Ruta recomendada basada en IA
- [ ] Integración con transporte público
- [ ] Sistema de reportes avanzado
- [ ] Análisis de datos y estadísticas

## 📝 Licencia

Proyecto desarrollado para Hackaton Movilidad 2025 - CDMX

## 👥 Autores

- Equipo de Desarrollo

## 📧 Contacto

Para reportar bugs o sugerencias: support@movilidad-cdmx.app

---

**Hecho con ❤️ para mejorar la movilidad en CDMX**
