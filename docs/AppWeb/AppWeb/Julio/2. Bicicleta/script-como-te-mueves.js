// ================================
// FUNCIONES JAVASCRIPT
// ================================

// Variable para almacenar la opción seleccionada
let selectedOption = null;

// Función para seleccionar una opción de movilidad
function selectOption(element) {
    // Remover la clase 'selected' de todas las opciones
    document.querySelectorAll('.option-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Agregar la clase 'selected' a la opción clickeada
    element.classList.add('selected');
    selectedOption = element.getAttribute('data-value');
}

// Función para guardar preferencias
function savePreferences() {
    const selectedCard = document.querySelector('.option-card.selected');
    
    // Validar que se haya seleccionado una opción
    if (!selectedCard) {
        alert('Selecciona cómo te mueves normalmente');
        return;
    }
    
    // Obtener las preferencias
    const preferences = {
        movement: selectedOption,
        movementName: selectedCard.querySelector('.option-name').textContent,
        lighting: document.getElementById('lighting-toggle').checked,
        airQuality: document.getElementById('air-quality-toggle').checked
    };
    
    console.log('Preferencias guardadas:', preferences);
    alert('Preferencias guardadas correctamente');
    
    // Aquí podrías redirigir a otra pantalla o guardar en localStorage
    // window.location.href = 'siguiente-pantalla.html';
}

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
    alert('Funcionalidad de tamaño de texto activada');
});

// Botón de idioma
document.getElementById('language-btn').addEventListener('click', function() {
    alert('Funcionalidad de idioma activada');
});

// Botón de sonido
document.getElementById('sound-btn').addEventListener('click', function() {
    alert('Funcionalidad de sonido activada');
});

// ================================
// RELOJ EN TIEMPO REAL
// ================================
function updateTime() {
    const now = new Date();
    const timeElement = document.querySelector('.time');
    timeElement.textContent = now.toLocaleTimeString('es-ES', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
}

// Actualizar cada minuto
setInterval(updateTime, 60000);
// Inicializar
updateTime();

// ================================
// INICIALIZACIÓN
// ================================
document.addEventListener('DOMContentLoaded', function() {
    // Agregar event listeners a las opciones de movilidad
    document.querySelectorAll('.option-card').forEach(card => {
        card.addEventListener('click', function() {
            selectOption(this);
        });
    });
    
    // Agregar event listener al botón de guardar
    document.getElementById('save-preferences-btn').addEventListener('click', savePreferences);
    
    // Agregar funcionalidad de teclado para accesibilidad
    document.querySelectorAll('.option-card').forEach(card => {
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectOption(this);
            }
        });
        
        // Hacer las tarjetas enfocables para accesibilidad
        card.setAttribute('tabindex', '0');
    });
});