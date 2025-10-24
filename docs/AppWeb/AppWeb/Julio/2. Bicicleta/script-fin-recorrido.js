// ================================
// VARIABLES GLOBALES
// ================================
let textSizeIndex = 1;
const textSizes = ['14px', '16px', '18px'];

// ================================
// FUNCIÓN PARA MOSTRAR NOTIFICACIONES
// ================================
function showNotification(message) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.classList.add('show');
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

// ================================
// RELOJ EN TIEMPO REAL
// ================================
function updateTime() {
    const now = new Date();
    const timeElement = document.getElementById('current-time');
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    timeElement.textContent = `${hours}:${minutes}`;
}

// ================================
// FUNCIONALIDAD DE CONFIRMACIÓN DE RUTA
// ================================
function setupRouteConfirmation() {
    document.getElementById('confirm-route-btn').addEventListener('click', function() {
        // Mostrar notificación de confirmación
        showNotification('Ruta confirmada. Iniciando navegación...');
        
        // Simular proceso de confirmación
        setTimeout(() => {
            // Aquí normalmente se redirigiría a la pantalla de navegación
            // window.location.href = 'navegacion.html';
            
            // Por ahora, solo mostramos un mensaje en consola
            console.log('Navegación iniciada para la ruta seleccionada');
            
            // Podríamos agregar aquí lógica para iniciar la navegación GPS
            // iniciarNavegacionGPS();
        }, 2000);
    });
}

// ================================
// FUNCIONALIDAD DE LA BARRA SUPERIOR
// ================================
function setupTopBarFunctionality() {
    // Botón de modo oscuro/claro
    document.getElementById('dark-mode-btn').addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const icon = this.querySelector('i');
        
        if (document.body.classList.contains('dark-mode')) {
            icon.classList.replace('fa-moon', 'fa-sun');
            showNotification('Modo oscuro activado');
        } else {
            icon.classList.replace('fa-sun', 'fa-moon');
            showNotification('Modo claro activado');
        }
    });
    
    // Botón de tamaño de texto
    document.getElementById('text-size-btn').addEventListener('click', function() {
        textSizeIndex = (textSizeIndex + 1) % textSizes.length;
        document.querySelector('.route-container').style.fontSize = textSizes[textSizeIndex];
        showNotification(`Tamaño de texto: ${textSizeIndex + 1}/3`);
    });
    
    // Botón de idioma
    document.getElementById('language-btn').addEventListener('click', function() {
        showNotification('Funcionalidad de cambio de idioma activada');
    });
    
    // Botón de sonido
    document.getElementById('sound-btn').addEventListener('click', function() {
        const icon = this.querySelector('i');
        
        if (icon.classList.contains('fa-volume-up')) {
            icon.classList.replace('fa-volume-up', 'fa-volume-mute');
            showNotification('Sonido desactivado');
        } else {
            icon.classList.replace('fa-volume-mute', 'fa-volume-up');
            showNotification('Sonido activado');
        }
    });
}

// ================================
// ANIMACIÓN DEL MAPA
// ================================
function setupMapAnimation() {
    const mapRoute = document.querySelector('.map-route');
    
    // Agregar animación sutil a la línea de la ruta
    mapRoute.style.transition = 'all 0.5s ease';
    
    // Efecto de pulsación sutil
    setInterval(() => {
        mapRoute.style.transform = 'translateY(-50%) scaleX(1.05)';
        setTimeout(() => {
            mapRoute.style.transform = 'translateY(-50%) scaleX(1)';
        }, 500);
    }, 3000);
}

// ================================
// CARGA DE PREFERENCIAS DE RUTA
// ================================
function loadRoutePreferences() {
    // Simular carga de preferencias (en una app real, esto vendría de una API)
    const routePreferences = {
        bikeLanes: 65,
        flatRoutes: true,
        avoidBusyRoads: true,
        airQuality: 'Buena',
        lighting: 78
    };
    
    // Aquí podríamos actualizar la UI con las preferencias cargadas
    console.log('Preferencias de ruta cargadas:', routePreferences);
}

// ================================
// INICIALIZACIÓN
// ================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar reloj
    updateTime();
    setInterval(updateTime, 60000);
    
    // Configurar funcionalidades
    setupRouteConfirmation();
    setupTopBarFunctionality();
    setupMapAnimation();
    loadRoutePreferences();
    
    // Establecer hora específica para coincidir con el diseño (18:22)
    document.getElementById('current-time').textContent = '18:22';
    
    // Hacer el botón de confirmación enfocable para accesibilidad
    const confirmButton = document.getElementById('confirm-route-btn');
    confirmButton.setAttribute('tabindex', '0');
    
    confirmButton.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
        }
    });
    
    // Agregar información de accesibilidad a los elementos de preferencia
    document.querySelectorAll('.preference-item').forEach(item => {
        item.setAttribute('tabindex', '0');
        item.setAttribute('role', 'listitem');
    });
});