function selectOption(el){
    document.querySelectorAll('.option-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}

function savePreferences(){
    const sel = document.querySelector('.option-card.selected');
    if(!sel){
        alert('Selecciona cómo te mueves normalmente');
        return;
    }
    const prefs = {
        movement: sel.querySelector('.option-name').textContent,
        lighting: document.getElementById('lighting-toggle').checked,
        airQuality: document.getElementById('air-quality-toggle').checked
    };
    console.log('Preferencias guardadas:', prefs);
    alert('Preferencias guardadas correctamente');
}

// Botones barra superior
document.getElementById('dark-mode-btn').addEventListener('click', function(){
    document.body.classList.toggle('dark-mode');
    const icon = this.querySelector('i');
    if(document.body.classList.contains('dark-mode')){
        icon.classList.replace('fa-moon', 'fa-sun');
        document.body.style.background = 'linear-gradient(135deg, #1a1a2e 0%, #16213e 100%)';
    } else {
        icon.classList.replace('fa-sun', 'fa-moon');
        document.body.style.background = 'linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%)';
    }
});

document.getElementById('text-size-btn').addEventListener('click', () => alert('Funcionalidad de tamaño de texto activada'));
document.getElementById('language-btn').addEventListener('click', () => alert('Funcionalidad de idioma activada'));
document.getElementById('sound-btn').addEventListener('click', () => alert('Funcionalidad de sonido activada'));

// Reloj
function updateTime(){
    const now = new Date();
    document.querySelector('.time').textContent = now.toLocaleTimeString('es-ES', {
        hour: '2-digit', 
        minute: '2-digit', 
        hour12: false
    });
}
setInterval(updateTime, 60000);
updateTime();