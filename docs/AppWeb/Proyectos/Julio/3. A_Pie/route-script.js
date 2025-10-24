// Actualizar hora en tiempo real
function updateTime() {
    const now = new Date();
    const timeElement = document.getElementById('current-time');
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    timeElement.textContent = `${hours}:${minutes}`;
}

// Inicializar y actualizar la hora cada minuto
updateTime();
setInterval(updateTime, 60000);

// Funcionalidad para los botones de GPS
document.querySelectorAll('.gps-btn').forEach(button => {
    button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('.route-input');
        showNotification('Buscando tu ubicación...');
        
        // Simulación de obtención de ubicación
        setTimeout(() => {
            input.value = 'Ubicación actual';
            showNotification('Ubicación establecida');
        }, 1500);
    });
});

// Funcionalidad para el botón de calcular ruta
document.getElementById('calculate-route').addEventListener('click', function() {
    const origin = document.getElementById('origin').value;
    const destination = document.getElementById('destination').value;
    
    if (!origin || !destination) {
        showNotification('Por favor, completa origen y destino');
        return;
    }
    
    showNotification('Calculando la mejor ruta accesible...');
    
    // Simulación de cálculo de ruta
    setTimeout(() => {
        showNotification('Ruta calculada con éxito');
    }, 2000);
});

// Funcionalidad para guardar preferencias
document.getElementById('save-preferences').addEventListener('click', function() {
    const avoidStairs = document.getElementById('avoid-stairs').checked;
    const elevators = document.getElementById('elevators').checked;
    const ramps = document.getElementById('ramps').checked;
    const widePaths = document.getElementById('wide-paths').checked;
    
    // Aquí normalmente se enviarían estas preferencias a un servidor
    showNotification('Preferencias guardadas correctamente');
});

// Función para mostrar notificaciones
function showNotification(message) {
    const notification = document.getElementById('notification');
    notification.textContent = message;
    notification.classList.add('show');
    
    setTimeout(() => {
        notification.classList.remove('show');
    }, 3000);
}

// Funcionalidad para el botón de modo oscuro
document.getElementById('dark-mode-btn').addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    const icon = this.querySelector('i');
    if (document.body.classList.contains('dark-mode')) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        showNotification('Modo oscuro activado');
    } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
        showNotification('Modo claro activado');
    }
});

// Funcionalidad para el botón de tamaño de texto
let textSizeIndex = 1;
const textSizes = ['14px', '16px', '18px'];
document.getElementById('text-size-btn').addEventListener('click', function() {
    textSizeIndex = (textSizeIndex + 1) % textSizes.length;
    document.querySelector('.route-container').style.fontSize = textSizes[textSizeIndex];
    showNotification(`Tamaño de texto: ${textSizeIndex + 1}/3`);
});

// Funcionalidad para el botón de idioma
document.getElementById('language-btn').addEventListener('click', function() {
    showNotification('Idioma cambiado');
});

// Funcionalidad para el botón de sonido
document.getElementById('sound-btn').addEventListener('click', function() {
    const icon = this.querySelector('i');
    if (icon.classList.contains('fa-volume-up')) {
        icon.classList.remove('fa-volume-up');
        icon.classList.add('fa-volume-mute');
        showNotification('Sonido desactivado');
    } else {
        icon.classList.remove('fa-volume-mute');
        icon.classList.add('fa-volume-up');
        showNotification('Sonido activado');
    }
});