// ================================
// RELOJ
// ================================
// Función para actualizar la hora en la barra superior
function updateTime() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    document.getElementById('current-time').textContent = `${h}:${m}`;
}

// Actualizar hora inmediatamente y cada minuto
updateTime();
setInterval(updateTime, 60000);

// ================================
// BOTONES Y NOTIFICACIONES
// ================================
// Evento para guardar preferencias
document.getElementById('save-preferences').addEventListener('click', () => { 
    showNotification('Preferencias guardadas correctamente'); 
});

// Eventos para los botones de GPS
document.getElementById('origin-gps').addEventListener('click', () => {
    document.getElementById('origin').value = 'Ubicación actual'; 
    showNotification('Ubicación de origen establecida'); 
});

document.getElementById('destination-gps').addEventListener('click', () => {
    document.getElementById('destination').value = 'Ubicación actual'; 
    showNotification('Ubicación de destino establecida'); 
});

// Evento para calcular ruta
document.getElementById('calculate-route').addEventListener('click', () => {
    const origin = document.getElementById('origin').value;
    const destination = document.getElementById('destination').value;
    
    if (!origin || !destination) { 
        showNotification('Por favor, completa origen y destino'); 
        return; 
    }
    
    showNotification('Calculando la mejor ruta accesible...');
    setTimeout(() => showNotification('Ruta calculada con éxito'), 1200);
});

// Función para mostrar notificaciones
function showNotification(msg) { 
    const notification = document.getElementById('notification'); 
    notification.textContent = msg; 
    notification.classList.add('show'); 
    setTimeout(() => notification.classList.remove('show'), 2500); 
}

// ================================
// MODO OSCURO
// ================================
// Evento para alternar modo oscuro
document.getElementById('dark-mode-btn').addEventListener('click', () => { 
    document.body.classList.toggle('dark-mode');
    const icon = document.getElementById('dark-mode-btn').querySelector('i');
    
    // Cambiar entre iconos de luna y sol según el modo
    if (document.body.classList.contains('dark-mode')) {
        icon.classList.replace('fa-moon', 'fa-sun');
    } else {
        icon.classList.replace('fa-sun', 'fa-moon');
    }
});

// ================================
// TAMAÑO DE TEXTO
// ================================
// Función para manejar el cambio de tamaño de texto
(function() {
    const btn = document.getElementById('text-size-btn');
    const wrapper = document.querySelector('.center-wrapper');
    const sizes = ['small', 'normal', 'large']; 
    let currentSize = 1;
    
    // Función para aplicar el tamaño de texto seleccionado
    function applyTextSize() { 
        wrapper.classList.remove('text-small', 'text-large');
        
        if (sizes[currentSize] === 'small') {
            wrapper.classList.add('text-small');
        } else if (sizes[currentSize] === 'large') {
            wrapper.classList.add('text-large');
        }
    }
    
    // Evento para cambiar tamaño de texto
    btn.addEventListener('click', () => {
        currentSize = (currentSize + 1) % sizes.length; 
        applyTextSize(); 
        showNotification(`Tamaño de texto: ${sizes[currentSize]}`);
    });
})();

// ================================
// SONIDO
// ================================
// Evento para alternar entre sonido activado y silenciado
document.getElementById('sound-btn').addEventListener('click', (e) => {
    const icon = e.currentTarget.querySelector('i');
    if (icon.classList.contains('fa-volume-up')) { 
        icon.classList.replace('fa-volume-up', 'fa-volume-mute'); 
        showNotification('Sonido silenciado'); 
    } else { 
        icon.classList.replace('fa-volume-mute', 'fa-volume-up'); 
        showNotification('Sonido activado'); 
    }
});

// ================================
// IDIOMA (placeholder)
// ================================
document.getElementById('language-btn').addEventListener('click', () => {
    showNotification('Funcionalidad de idioma activada');
});