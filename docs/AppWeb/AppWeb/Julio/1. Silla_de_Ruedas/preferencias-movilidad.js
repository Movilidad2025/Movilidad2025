// Función para seleccionar una opción de movilidad
function selectOption(el) {
    // Deseleccionar todas las opciones
    document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
    // Seleccionar la opción clickeada
    el.classList.add('selected');
}

// Función para guardar las preferencias
function savePreferences() {
    // Obtener la opción seleccionada
    const sel = document.querySelector('.option-card.selected');
    
    // Validar que se haya seleccionado una opción
    if (!sel) {
        alert('Selecciona cómo te mueves normalmente');
        return;
    }
    
    // Recopilar todas las preferencias
    const prefs = {
        movement: sel.querySelector('.option-name').textContent,
        movementValue: sel.getAttribute('data-value'),
        lighting: document.getElementById('lighting-toggle').checked,
        airQuality: document.getElementById('air-quality-toggle').checked
    };
    
    // Registrar las preferencias en consola (para depuración)
    console.log('Preferencias guardadas:', prefs);
    
    // Mostrar mensaje de confirmación al usuario
    alert('Preferencias guardadas correctamente');
    
    // Aquí podrías redirigir a otra página o realizar otra acción
    // window.location.href = 'mapa.html';
}

// Inicializar eventos después de que el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    // Agregar eventos de clic a las opciones de movilidad
    document.querySelectorAll('.option-card').forEach(card => {
        card.addEventListener('click', function() {
            selectOption(this);
        });
    });
    
    // Evento para el botón de guardar
    document.getElementById('save-preferences-btn').addEventListener('click', savePreferences);
    
    // Funcionalidad de alternar modo oscuro
    document.getElementById('dark-mode-btn').addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        const icon = this.querySelector('i');
        
        // Cambiar entre iconos de luna y sol según el modo
        if (document.body.classList.contains('dark-mode')) {
            icon.classList.replace('fa-moon', 'fa-sun');
        } else {
            icon.classList.replace('fa-sun', 'fa-moon');
        }
    });
    
    // Funcionalidad de tamaño de texto (placeholder)
    document.getElementById('text-size-btn').addEventListener('click', () => {
        alert('Funcionalidad de tamaño de texto activada');
    });
    
    // Funcionalidad de idioma (placeholder)
    document.getElementById('language-btn').addEventListener('click', () => {
        alert('Funcionalidad de idioma activada');
    });
    
    // Funcionalidad de sonido (placeholder)
    document.getElementById('sound-btn').addEventListener('click', () => {
        alert('Funcionalidad de sonido activada');
    });
});

// Función para actualizar el reloj en tiempo real
function updateTime() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
}

// Actualizar la hora cada minuto y establecer valor inicial
setInterval(updateTime, 60000);
updateTime();