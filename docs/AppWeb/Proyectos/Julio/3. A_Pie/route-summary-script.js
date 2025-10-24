// Funcionalidad para los botones de la barra superior
document.getElementById('dark-mode-btn').addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    const icon = this.querySelector('i');
    if (document.body.classList.contains('dark-mode')) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
        document.body.style.background = 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)';
    } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
        document.body.style.background = 'linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%)';
    }
});

document.getElementById('text-size-btn').addEventListener('click', function() {
    alert('Funcionalidad de tamaño de texto activada');
});

document.getElementById('language-btn').addEventListener('click', function() {
    alert('Funcionalidad de idioma activada');
});

document.getElementById('sound-btn').addEventListener('click', function() {
    alert('Funcionalidad de sonido activada');
});

// Funcionalidad para el botón "Confirmar ruta"
document.querySelector('.confirm-button').addEventListener('click', function() {
    // Aquí iría la lógica para confirmar la ruta
    alert('Ruta confirmada. Iniciando navegación...');
    
    // Redirigir a la pantalla de navegación
    // window.location.href = 'navegacion.html';
});

// Actualizar la hora en tiempo real
function updateTime() {
    const now = new Date();
    const timeElement = document.querySelector('.time');
    timeElement.textContent = now.toLocaleTimeString('es-ES', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: false
    });
}

// Actualizar la hora cada minuto
setInterval(updateTime, 60000);
updateTime(); // Inicializar

// Establecer la hora específica de la imagen (18:22)
document.querySelector('.time').textContent = '18:22';