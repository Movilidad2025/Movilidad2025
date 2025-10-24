// ================================
// VARIABLES GLOBALES
// ================================
let textSizeIndex = 1;
const textSizes = ['14px', '16px', '18px'];

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
// FUNCIONALIDAD DE BOTONES GPS
// ================================
function setupGPSButtons() {
    document.querySelectorAll('.gps-btn').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.route-input');
            const pointType = this.id === 'origin-gps-btn' ? 'origen' : 'destino';
            
            showNotification(`Buscando tu ubicación para ${pointType}...`);
            
            // Simulación de obtención de ubicación
            setTimeout(() => {
                input.value = 'Ubicación actual';
                showNotification(`Ubicación de ${pointType} establecida`);
            }, 1500);
        });
    });
}

// ================================
// FUNCIONALIDAD DE CÁLCULO DE RUTA
// ================================
function setupRouteCalculation() {
    document.getElementById('calculate-route').addEventListener('click', function() {
        const origin = document.getElementById('origin').value;
        const destination = document.getElementById('destination').value;
        
        if (!origin || !destination) {
            showNotification('Por favor, completa origen y destino');
            return;
        }
        
        // Obtener preferencias seleccionadas
        const bikeLanes = document.getElementById('bike-lanes').checked;
        const flatRoutes = document.getElementById('flat-routes').checked;
        const avoidBusyRoads = document.getElementById('avoid-busy-roads').checked;
        const widePaths = document.getElementById('wide-paths').checked;
        
        showNotification('Calculando la mejor ruta accesible...');
        
        // Simulación de cálculo de ruta
        setTimeout(() => {
            const routePreferences = {
                origin,
                destination,
                bikeLanes,
                flatRoutes,
                avoidBusyRoads,
                widePaths
            };
            
            console.log('Ruta calculada con preferencias:', routePreferences);
            showNotification('Ruta calculada con éxito');
            
            // Aquí normalmente se redirigiría a la pantalla del mapa
            // window.location.href = 'mapa-ruta.html';
        }, 2000);
    });
}

// ================================
// FUNCIONALIDAD DE GUARDAR PREFERENCIAS
// ================================
function setupPreferencesSave() {
    document.getElementById('save-preferences').addEventListener('click', function() {
        const preferences = {
            bikeLanes: document.getElementById('bike-lanes').checked,
            flatRoutes: document.getElementById('flat-routes').checked,
            avoidBusyRoads: document.getElementById('avoid-busy-roads').checked,
            widePaths: document.getElementById('wide-paths').checked
        };
        
        // Guardar en localStorage (simulación)
        localStorage.setItem('routePreferences', JSON.stringify(preferences));
        
        showNotification('Preferencias guardadas correctamente');
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
// INICIALIZACIÓN
// ================================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar reloj
    updateTime();
    setInterval(updateTime, 60000);
    
    // Configurar funcionalidades
    setupGPSButtons();
    setupRouteCalculation();
    setupPreferencesSave();
    setupTopBarFunctionality();
    
    // Cargar preferencias guardadas (si existen)
    const savedPreferences = localStorage.getItem('routePreferences');
    if (savedPreferences) {
        const preferences = JSON.parse(savedPreferences);
        document.getElementById('bike-lanes').checked = preferences.bikeLanes;
        document.getElementById('flat-routes').checked = preferences.flatRoutes;
        document.getElementById('avoid-busy-roads').checked = preferences.avoidBusyRoads;
        document.getElementById('wide-paths').checked = preferences.widePaths;
    }
    
    // Hacer los inputs de ruta enfocables para accesibilidad
    document.querySelectorAll('.route-input').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('calculate-route').click();
            }
        });
    });
});