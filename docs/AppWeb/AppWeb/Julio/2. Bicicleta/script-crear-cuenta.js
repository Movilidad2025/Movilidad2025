// ================================
// VALIDACIÓN DEL FORMULARIO
// ================================
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    // Verificar que las contraseñas coincidan
    if (password !== confirmPassword) {
        alert('Las contraseñas no coinciden');
        return;
    }
    
    // Mensaje de éxito
    alert('¡Registro exitoso!');
});

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

// Actualizar la hora cada minuto
setInterval(updateTime, 60000);
updateTime(); // Inicializar