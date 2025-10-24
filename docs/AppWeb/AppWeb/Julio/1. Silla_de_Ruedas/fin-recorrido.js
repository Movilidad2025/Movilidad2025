// ================================
// FUNCIONALIDAD DE LA BARRA SUPERIOR
// ================================

// Botón de modo oscuro/claro
document.getElementById('dark-mode-btn').addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    const icon = this.querySelector('i');
    if (document.body.classList.contains('dark-mode')) {
        icon.classList.replace('fa-moon', 'fa-sun');
    } else {
        icon.classList.replace('fa-sun', 'fa-moon');
    }
});

// Botón de tamaño de texto
document.getElementById('text-size-btn').addEventListener('click', function() {
    const wrapper = document.querySelector('.center-wrapper');
    const sizes = ['small', 'normal', 'large'];
    let currentSize = 1;
    
    if (wrapper.classList.contains('text-small')) {
        currentSize = 0;
    } else if (wrapper.classList.contains('text-large')) {
        currentSize = 2;
    }
    
    currentSize = (currentSize + 1) % sizes.length;
    
    wrapper.classList.remove('text-small', 'text-large');
    if (sizes[currentSize] !== 'normal') {
        wrapper.classList.add(`text-${sizes[currentSize]}`);
    }
    
    // Mostrar notificación del tamaño actual
    const sizeNames = {
        'small': 'Pequeño',
        'normal': 'Normal', 
        'large': 'Grande'
    };
    
    showNotification(`Tamaño de texto: ${sizeNames[sizes[currentSize]]}`);
});

// Botón de idioma
document.getElementById('language-btn').addEventListener('click', function() {
    showNotification('Funcionalidad de idioma activada');
});

// Botón de sonido
document.getElementById('sound-btn').addEventListener('click', function() {
    const icon = this.querySelector('i');
    if (icon.classList.contains('fa-volume-up')) {
        icon.classList.replace('fa-volume-up', 'fa-volume-mute');
        showNotification('Sonido silenciado');
    } else {
        icon.classList.replace('fa-volume-mute', 'fa-volume-up');
        showNotification('Sonido activado');
    }
});

// ================================
// FUNCIONALIDAD DEL BOTÓN CONFIRMAR
// ================================
document.getElementById('confirm-route-btn').addEventListener('click', function() {
    // Aquí iría la lógica para confirmar la ruta
    showNotification('Ruta confirmada. Iniciando navegación...');
    
    // Simular redirección después de un breve delay
    setTimeout(() => {
        // Redirigir a la pantalla de navegación
        // window.location.href = 'navegacion.html';
        console.log('Navegando a la pantalla de navegación...');
    }, 1500);
});

// ================================
// RELOJ EN TIEMPO REAL
// ================================
// Función para actualizar la hora en la barra superior
function updateTime() {
    const now = new Date();
    const timeElement = document.getElementById('current-time');
    timeElement.textContent = now.toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: false
    });
}

// Actualizar la hora cada minuto
setInterval(updateTime, 60000);
updateTime(); // Inicializar

// ================================
// NOTIFICACIONES
// ================================
function showNotification(message) {
    // Crear elemento de notificación si no existe
    let notification = document.getElementById('custom-notification');
    
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'custom-notification';
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #2ecc71;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
            max-width: 80%;
            text-align: center;
        `;
        document.body.appendChild(notification);
    }
    
    notification.textContent = message;
    notification.style.opacity = '1';
    
    // Ocultar notificación después de 3 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
    }, 3000);
}

// ================================
// INICIALIZACIÓN
// ================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Página de fin de recorrido cargada correctamente');
});