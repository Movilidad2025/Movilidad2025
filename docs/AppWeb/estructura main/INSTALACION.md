# Movilidad CDMX - Documentación de Instalación

## Requisitos del Sistema

### Software Requerido
- **PHP**: 7.4 o superior
- **MySQL**: 8.0 o superior  
- **Servidor Web**: Apache 2.4+ (con mod_rewrite) o Nginx
- **Navegador**: Chrome, Firefox, Safari o Edge (versiones recientes)

### Extensiones PHP Necesarias
```
- mysqli (para MySQL)
- json (para JSON encoding/decoding)
- filter (para validación)
- session (para manejo de sesiones)
- openssl (para HTTPS)
```

## Instalación Paso a Paso

### 1. Preparar el Servidor

```bash
# Clonar o descargar el proyecto
cd /var/www/html  # En Linux/Mac
# o C:\xampp\htdocs  # En Windows con XAMPP

# Establecer permisos
chmod -R 755 movilidad-cdmx/
chmod -R 777 movilidad-cdmx/logs/  # Carpeta para logs
```

### 2. Crear Base de Datos

```bash
# Conectar a MySQL
mysql -u root -p

# Crear base de datos y usuario
mysql> CREATE DATABASE movilidad_cdmx CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> CREATE USER 'movilidad_user'@'localhost' IDENTIFIED BY 'tu_contraseña_segura';
mysql> GRANT ALL PRIVILEGES ON movilidad_cdmx.* TO 'movilidad_user'@'localhost';
mysql> FLUSH PRIVILEGES;
mysql> EXIT;

# Importar schema
mysql -u movilidad_user -p movilidad_cdmx < database/schema.sql
```

### 3. Configurar Variables de Entorno

Crear archivo `.env` en la raíz del proyecto:

```bash
# Base de Datos
DB_HOST=localhost
DB_NAME=movilidad_cdmx
DB_USER=movilidad_user
DB_PASS=tu_contraseña_segura

# APIs
GOOGLE_MAPS_API_KEY=tu_api_key_aqui
NOMINATIM_API_URL=https://nominatim.openstreetmap.org

# Configuración
ENVIRONMENT=development  # development|production
BASE_URL=http://localhost/movilidad-cdmx/
JWT_SECRET=tu_secreto_super_seguro_aqui

# Configuración de Sesión
SESSION_TIMEOUT=3600
```

O editar `includes/config.php` directamente.

### 4. Configurar Servidor Web

#### Para Apache:

Crear `.htaccess` en la raíz:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /movilidad-cdmx/
    
    # Redirigir directamente archivos y carpetas existentes
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Redirigir todo a index.php
    RewriteRule ^(.*)$ index.php [L]
</IfModule>

# Configuración de seguridad
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Denegar acceso a archivos sensibles
<FilesMatch "\.php$|\.env$|\.sql$">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

#### Para Nginx:

En el bloque de servidor:

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/movilidad-cdmx;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Denegar acceso a archivos sensibles
    location ~ /\. {
        deny all;
    }

    location ~ \.env$ {
        deny all;
    }
}
```

### 5. Crear Carpeta de Logs

```bash
mkdir -p logs/
chmod 777 logs/
```

### 6. Verificar Instalación

Abre tu navegador:

```
http://localhost/movilidad-cdmx/
```

Deberías ver la página de inicio. Si no:

1. Verifica permisos de carpetas
2. Verifica conexión a base de datos en `includes/config.php`
3. Revisa logs del servidor PHP

## Configuración de APIs Externas

### Google Maps API

1. Ir a [Google Cloud Console](https://console.cloud.google.com/)
2. Crear un nuevo proyecto
3. Habilitar APIs:
   - Maps JavaScript API
   - Geocoding API
   - Places API
4. Crear credenciales (API Key)
5. Agregar la clave en `.env` o `includes/config.php`

### Nominatim (OpenStreetMap)

Nominatim es gratuito y no requiere API key. Los datos de ejemplo en `schema.sql` utilizan coordenadas de CDMX.

## Estructura de Carpetas

```
movilidad-cdmx/
├── index.php                    # Home/Landing
├── login.php                    # Login
├── registro.php                 # Registro
├── dashboard.php                # Dashboard del usuario
├── seleccion-movilidad.php      # Selección de tipo de movilidad
├── configurar-ruta.php          # Configuración de ruta
├── mapa-ruta.php                # Visualización de ruta en mapa
├── logout.php                   # Cerrar sesión
│
├── includes/
│   ├── config.php               # Configuración general
│   ├── database.php             # Conexión a BD
│   ├── session.php              # Gestión de sesiones
│   ├── auth.php                 # Autenticación
│   └── functions.php            # Funciones auxiliares
│
├── classes/
│   ├── Usuario.php              # Clase Usuario
│   ├── Ruta.php                 # Clase Ruta
│   ├── Movilidad.php            # Clase Movilidad
│   └── Mapa.php                 # Clase Mapa
│
├── api/
│   ├── rutas.php                # API de rutas
│   ├── usuarios.php             # API de usuarios
│   └── datos-movilidad.php      # API de datos
│
├── assets/
│   ├── css/
│   │   ├── styles.css           # Estilos principales
│   │   └── responsive.css       # Estilos responsivos
│   ├── js/
│   │   ├── main.js              # JavaScript principal
│   │   ├── mapa.js              # Funciones de mapa
│   │   └── validaciones.js      # Validaciones
│   └── images/                  # Imágenes
│
├── database/
│   └── schema.sql               # Schema de BD
│
├── logs/                        # Archivos de log
└── .htaccess                    # Configuración Apache
```

## Funcionalidades Principales

### 1. Sistema de Autenticación
- Registro seguro con validación
- Login con sesiones
- Recuperación de contraseña (implementar)
- Tokens CSRF para protección

### 2. Selección de Movilidad
Tipos soportados:
- A pie: Rutas con iluminación y calidad del aire
- Bicicleta: Ciclovías disponibles
- Silla de ruedas: Infraestructura accesible

### 3. Configuración de Rutas
- Geocodificación de direcciones
- Cálculo de distancia
- Estimación de tiempo
- Historial de búsquedas

### 4. Visualización en Mapa
- Mapa interactivo con Leaflet
- Capas según tipo de movilidad
- Información de calidad del aire
- Reportes de incidentes
- Puntos de accesibilidad

### 5. APIs REST

#### Rutas
- `POST /api/rutas.php?accion=calcular` - Calcular ruta
- `POST /api/rutas.php?accion=guardar` - Guardar ruta
- `GET /api/rutas.php?accion=listar` - Listar rutas

#### Datos de Movilidad
- `GET /api/datos-movilidad.php?tipo=calidad-aire` - Calidad del aire
- `GET /api/datos-movilidad.php?tipo=ciclovias` - Ciclovías
- `GET /api/datos-movilidad.php?tipo=alumbrado` - Alumbrado público

#### Usuarios
- `GET /api/usuarios.php?accion=perfil` - Obtener perfil
- `POST /api/usuarios.php?accion=actualizar-movilidad` - Cambiar tipo

## Seguridad

### Implementado
✓ Hash de contraseñas con password_hash()
✓ Validación de entrada (sanitize)
✓ Protección contra inyecciones SQL (prepared statements)
✓ Tokens CSRF en formularios
✓ Sesiones seguras (HttpOnly, Secure en HTTPS)
✓ Limitación de timeout de sesión

### Recomendaciones
- Implementar HTTPS en producción
- Usar variables de entorno para credenciales
- Implementar rate limiting en APIs
- Auditoría de cambios importantes
- Backups automáticos de BD
- Monitoreo de errores

## Mantenimiento

### Backups
```bash
# Backup automático diario
0 2 * * * mysqldump -u usuario -p contraseña movilidad_cdmx > /backups/movilidad_$(date +\%Y\%m\%d).sql
```

### Logs
Los errores se guardan en `logs/activity_YYYY-MM-DD.log` si DEBUG está habilitado.

### Actualización de Datos
- Actualizar calidad del aire regularmente
- Importar nuevas ciclovías
- Reportes de incidentes de usuarios

## Troubleshooting

### "Conexión rechazada"
- Verificar MySQL está ejecutándose
- Verificar credenciales en config.php
- Verificar que la BD existe

### "Permiso denegado"
- Verificar permisos de carpetas: `chmod -R 755 movilidad-cdmx/`
- Verificar carpeta logs: `chmod -R 777 logs/`

### "Página en blanco"
- Habilitar `display_errors` en `php.ini`
- Ver logs del servidor
- Verificar memoria disponible

### Mapa no carga
- Verificar API key de Google Maps
- Verificar conexión a internet
- Verificar librería Leaflet está incluida

## Soporte y Documentación

Para más información:
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Leaflet Documentation](https://leafletjs.com/)
- [OpenStreetMap Nominatim](https://nominatim.org/)

## Licencia

Proyecto desarrollado para Hackaton Movilidad 2025
