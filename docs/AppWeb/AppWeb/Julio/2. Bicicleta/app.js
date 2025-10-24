// Funcionalidad para cambiar entre pantallas
function showScreen(screenId) {
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
    document.getElementById(screenId).classList.add('active');
}

// Configuración de eventos para los botones
function setupEventListeners() {
    // Botón de inicio de sesión - sin funcionalidad de cambio
    document.getElementById('loginBtn').onclick = (e) => {
        e.preventDefault();
        // No hacer nada - mantener en la pantalla actual
    };
    
    // Botón de crear cuenta - sin funcionalidad de cambio
    document.getElementById('createAccountBtn').onclick = (e) => {
        e.preventDefault();
        // No hacer nada - mantener en la pantalla actual
    };
    
    // Navegación entre pantallas
    document.getElementById('saveContinueBtn').onclick = () => showScreen('screen3');
    
    document.getElementById('backBtn').onclick = (e) => {
        e.preventDefault();
        showScreen('screen1');
    };
    
    document.getElementById('backBtnMap').onclick = (e) => {
        e.preventDefault();
        showScreen('screen2');
    };

    // Funcionalidades de la barra superior
    document.getElementById('dark-mode').addEventListener('click', () => {
        document.body.classList.toggle('dark-mode');
    });

    document.getElementById('sound').addEventListener('click', (e) => {
        const i = e.currentTarget.querySelector('i');
        if(i.classList.contains('fa-volume-up')) {
            i.classList.remove('fa-volume-up'); 
            i.classList.add('fa-volume-xmark');
        } else {
            i.classList.remove('fa-volume-xmark'); 
            i.classList.add('fa-volume-up');
        }
    });
}

// Reloj en tiempo real
function updateClock(){
    const now = new Date();
    const hours = String(now.getHours()).padStart(2,'0');
    const mins  = String(now.getMinutes()).padStart(2,'0');
    document.getElementById('clock').textContent = `${hours}:${mins}`;
}

function initClock() {
    updateClock();
    setInterval(updateClock, 60000);
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    initClock();
});