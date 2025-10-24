// Animación para los íconos al pasar el mouse
document.querySelectorAll('.icon').forEach(icon => {
    icon.addEventListener('mouseover', () => {
        icon.style.transform = 'translateY(-5px)';
    });
    icon.addEventListener('mouseout', () => {
        icon.style.transform = 'translateY(0)';
    });
});

// Validación básica del formulario al enviar
document.getElementById('recovery-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevenir envío por defecto
    
    // Obtener valor del campo de email
    const email = document.getElementById('email').value;
    
    // Verificar que el campo no esté vacío
    if (!email) {
        alert('Por favor, introduce tu correo electrónico');
        return;
    }
    
    // Validar formato de email con expresión regular
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        alert('Por favor, introduce un correo electrónico válido');
        return;
    }
    
    // Simular envío exitoso
    alert('Se ha enviado un enlace de recuperación a tu correo electrónico');
});

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
document.getElementById('text-size-btn').addEventListener('click', function() {
    alert('Funcionalidad de tamaño de texto activada');
});

// Funcionalidad de idioma (placeholder)
document.getElementById('language-btn').addEventListener('click', function() {
    alert('Funcionalidad de idioma activada');
});

// Funcionalidad de sonido (placeholder)
document.getElementById('sound-btn').addEventListener('click', function() {
    alert('Funcionalidad de sonido activada');
});

// Función para actualizar el reloj en tiempo real
function updateClock() {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    document.getElementById('clock').textContent = hours + ':' + minutes;
}

// Actualizar el reloj cada segundo y establecer valor inicial
setInterval(updateClock, 1000);
updateClock();