/**
 * JavaScript Principal
 * Funciones generales de la aplicación
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Inicializar aplicación
 */
function initializeApp() {
    // Configurar event listeners
    setupFormValidation();
    setupDynamicElements();
    setupNotifications();
}

/**
 * Configurar validación de formularios
 */
function setupFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Validar formulario
 */
function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            showError(input, 'Este campo es requerido');
            isValid = false;
        } else {
            clearError(input);
        }
        
        // Validaciones específicas
        if (input.type === 'email' && input.value) {
            if (!validateEmail(input.value)) {
                showError(input, 'Email inválido');
                isValid = false;
            }
        }
    });
    
    return isValid;
}

/**
 * Validar email
 */
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Mostrar error en campo
 */
function showError(input, message) {
    input.classList.add('error');
    input.setAttribute('data-error', message);
    
    let errorDiv = input.parentElement.querySelector('.error-message');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        input.parentElement.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
}

/**
 * Limpiar error
 */
function clearError(input) {
    input.classList.remove('error');
    const errorDiv = input.parentElement.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
}

/**
 * Configurar elementos dinámicos
 */
function setupDynamicElements() {
    // Event listeners para elementos dinámicos
    document.addEventListener('click', function(e) {
        // Botones de cerrar alerta
        if (e.target.classList.contains('alert-close')) {
            e.target.parentElement.remove();
        }
    });
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'info', duration = 3000) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = message + '<button class="alert-close">×</button>';
    
    const container = document.querySelector('main') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    if (duration > 0) {
        setTimeout(() => {
            alertDiv.remove();
        }, duration);
    }
}

/**
 * Configurar notificaciones
 */
function setupNotifications() {
    // Remover alertas después de cierto tiempo
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (!alert.classList.contains('alert-danger') && 
            !alert.classList.contains('alert-warning')) {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 4000);
        }
    });
}

/**
 * Hacer petición AJAX
 */
function fetchAPI(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    };

    const finalOptions = { ...defaultOptions, ...options };

    return fetch(url, finalOptions)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Error en petición AJAX:', error);
            showNotification('Error en la conexión', 'danger');
            throw error;
        });
}

/**
 * Formatear número
 */
function formatNumber(num, decimals = 2) {
    return Number(num).toLocaleString('es-MX', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    });
}

/**
 * Formatear distancia
 */
function formatDistance(km) {
    if (km < 1) {
        return Math.round(km * 1000) + ' m';
    }
    return formatNumber(km, 2) + ' km';
}

/**
 * Formatear tiempo
 */
function formatTime(minutes) {
    if (minutes < 60) {
        return minutes + ' min';
    }
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return hours + 'h ' + mins + 'min';
}

/**
 * Obtener ubicación actual
 */
function getCurrentLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject('Geolocalización no soportada');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            position => {
                resolve({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    accuracy: position.coords.accuracy
                });
            },
            error => {
                reject('Error al obtener ubicación: ' + error.message);
            }
        );
    });
}

/**
 * Copiar al portapapeles
 */
function copyToClipboard(text) {
    return navigator.clipboard.writeText(text)
        .then(() => {
            showNotification('Copiado al portapapeles', 'success', 2000);
        })
        .catch(err => {
            showNotification('Error al copiar', 'danger');
            console.error('Error:', err);
        });
}

/**
 * Guardar en localStorage
 */
function saveToStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (error) {
        console.error('Error al guardar en storage:', error);
        return false;
    }
}

/**
 * Obtener de localStorage
 */
function getFromStorage(key, defaultValue = null) {
    try {
        const value = localStorage.getItem(key);
        return value ? JSON.parse(value) : defaultValue;
    } catch (error) {
        console.error('Error al obtener de storage:', error);
        return defaultValue;
    }
}

/**
 * Limpiar localStorage
 */
function clearStorage(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (error) {
        console.error('Error al limpiar storage:', error);
        return false;
    }
}

/**
 * Debounce function
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function
 */
function throttle(func, limit = 300) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Mostrar/Ocultar elemento
 */
function toggleElement(selector, show = null) {
    const element = document.querySelector(selector);
    if (!element) return;

    if (show === null) {
        element.style.display = element.style.display === 'none' ? '' : 'none';
    } else {
        element.style.display = show ? '' : 'none';
    }
}

/**
 * Agregar clase
 */
function addClass(selector, className) {
    const elements = document.querySelectorAll(selector);
    elements.forEach(el => el.classList.add(className));
}

/**
 * Remover clase
 */
function removeClass(selector, className) {
    const elements = document.querySelectorAll(selector);
    elements.forEach(el => el.classList.remove(className));
}

/**
 * Verificar si existe clase
 */
function hasClass(selector, className) {
    const element = document.querySelector(selector);
    return element ? element.classList.contains(className) : false;
}

/**
 * Log condicional
 */
function log(...args) {
    if (typeof DEBUG !== 'undefined' && DEBUG) {
        console.log(...args);
    }
}

/**
 * Manejar errores globales
 */
window.addEventListener('error', function(event) {
    console.error('Error global:', event.error);
    showNotification('Ocurrió un error inesperado', 'danger');
});

/**
 * Manejar promesas rechazadas no capturadas
 */
window.addEventListener('unhandledrejection', function(event) {
    console.error('Promesa rechazada:', event.reason);
    showNotification('Error: ' + event.reason, 'danger');
});
