/**
 * Validaciones JavaScript
 * Funciones para validación en cliente
 */

/**
 * Validar email
 */
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Validar contraseña
 */
function validarPassword(password, minLength = 8) {
    if (!password || password.length < minLength) {
        return {
            valido: false,
            error: `La contraseña debe tener al menos ${minLength} caracteres`
        };
    }

    // Validar criterios adicionales (opcional)
    const tieneNumeros = /\d/.test(password);
    const tieneMayusculas = /[A-Z]/.test(password);
    const tieneMinusculas = /[a-z]/.test(password);

    return {
        valido: true,
        fuerte: tieneNumeros && tieneMayusculas && tieneMinusculas
    };
}

/**
 * Validar URL
 */
function validarURL(url) {
    try {
        new URL(url);
        return true;
    } catch (error) {
        return false;
    }
}

/**
 * Validar número de teléfono
 */
function validarTelefono(telefono) {
    const regex = /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/;
    return regex.test(telefono.replace(/\s/g, ''));
}

/**
 * Validar coordenadas
 */
function validarCoordenadas(lat, lng) {
    const latNum = parseFloat(lat);
    const lngNum = parseFloat(lng);

    if (isNaN(latNum) || isNaN(lngNum)) {
        return false;
    }

    return latNum >= -90 && latNum <= 90 && lngNum >= -180 && lngNum <= 180;
}

/**
 * Validar que esté dentro de CDMX
 */
function validarEnCDMX(lat, lng) {
    const latNum = parseFloat(lat);
    const lngNum = parseFloat(lng);

    // Límites aproximados de CDMX
    const minLat = 19.0;
    const maxLat = 19.6;
    const minLng = -99.5;
    const maxLng = -98.9;

    return validarCoordenadas(lat, lng) &&
           latNum >= minLat && latNum <= maxLat &&
           lngNum >= minLng && lngNum <= maxLng;
}

/**
 * Validar fecha
 */
function validarFecha(fecha, formato = 'YYYY-MM-DD') {
    if (typeof fecha !== 'string') {
        return false;
    }

    // Validación básica para YYYY-MM-DD
    if (formato === 'YYYY-MM-DD') {
        const regex = /^\d{4}-\d{2}-\d{2}$/;
        if (!regex.test(fecha)) {
            return false;
        }

        const date = new Date(fecha);
        return date instanceof Date && !isNaN(date);
    }

    return false;
}

/**
 * Validar hora
 */
function validarHora(hora, formato = 'HH:MM') {
    if (typeof hora !== 'string') {
        return false;
    }

    if (formato === 'HH:MM') {
        const regex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
        return regex.test(hora);
    }

    return false;
}

/**
 * Validar numero
 */
function validarNumero(valor, min = null, max = null) {
    const num = parseFloat(valor);

    if (isNaN(num)) {
        return false;
    }

    if (min !== null && num < min) {
        return false;
    }

    if (max !== null && num > max) {
        return false;
    }

    return true;
}

/**
 * Validar entero
 */
function validarEntero(valor, min = null, max = null) {
    const num = parseInt(valor);

    if (isNaN(num) || num.toString() !== valor.toString()) {
        return false;
    }

    if (min !== null && num < min) {
        return false;
    }

    if (max !== null && num > max) {
        return false;
    }

    return true;
}

/**
 * Validar longitud de texto
 */
function validarLongitud(texto, minimo = 0, maximo = null) {
    const len = texto.length;

    if (len < minimo) {
        return false;
    }

    if (maximo !== null && len > maximo) {
        return false;
    }

    return true;
}

/**
 * Validar que dos campos coincidan
 */
function validarCoincidencia(campo1, campo2) {
    return campo1 === campo2;
}

/**
 * Validar campo requerido
 */
function validarRequerido(valor) {
    if (typeof valor === 'string') {
        return valor.trim().length > 0;
    }
    return valor !== null && valor !== undefined && valor !== '';
}

/**
 * Validar select (valor seleccionado)
 */
function validarSelect(selectElement) {
    return selectElement.value && selectElement.value !== '';
}

/**
 * Validar checkbox (debe estar marcado)
 */
function validarCheckbox(checkboxElement) {
    return checkboxElement.checked === true;
}

/**
 * Validar archivo
 */
function validarArchivo(inputElement, tiposPermitidos = [], tamanoMaxMB = 5) {
    const files = inputElement.files;

    if (files.length === 0) {
        return { valido: false, error: 'Por favor selecciona un archivo' };
    }

    const archivo = files[0];
    const tamanoMaxBytes = tamanoMaxMB * 1024 * 1024;

    // Validar tamaño
    if (archivo.size > tamanoMaxBytes) {
        return {
            valido: false,
            error: `El archivo no puede exceder ${tamanoMaxMB}MB`
        };
    }

    // Validar tipo
    if (tiposPermitidos.length > 0) {
        if (!tiposPermitidos.includes(archivo.type)) {
            return {
                valido: false,
                error: `Tipo de archivo no permitido. Permitidos: ${tiposPermitidos.join(', ')}`
            };
        }
    }

    return { valido: true };
}

/**
 * Validar formulario completo
 */
function validarFormularioCompleto(formElement) {
    const errores = {};
    const inputs = formElement.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        if (!input.name) return;

        // Saltar campos ocultos
        if (input.type === 'hidden' || input.style.display === 'none') {
            return;
        }

        // Validar campo requerido
        if (input.hasAttribute('required')) {
            if (!validarRequerido(input.value)) {
                errores[input.name] = 'Este campo es requerido';
                return;
            }
        }

        // Validar email
        if (input.type === 'email' && input.value) {
            if (!validarEmail(input.value)) {
                errores[input.name] = 'Email inválido';
            }
        }

        // Validar número
        if (input.type === 'number' && input.value) {
            if (!validarNumero(input.value)) {
                errores[input.name] = 'Ingresa un número válido';
            }
        }

        // Validar minlength
        if (input.hasAttribute('minlength') && input.value) {
            const min = parseInt(input.getAttribute('minlength'));
            if (!validarLongitud(input.value, min)) {
                errores[input.name] = `Mínimo ${min} caracteres`;
            }
        }

        // Validar maxlength
        if (input.hasAttribute('maxlength') && input.value) {
            const max = parseInt(input.getAttribute('maxlength'));
            if (!validarLongitud(input.value, 0, max)) {
                errores[input.name] = `Máximo ${max} caracteres`;
            }
        }

        // Validar pattern personalizado
        if (input.hasAttribute('pattern') && input.value) {
            const pattern = new RegExp(input.getAttribute('pattern'));
            if (!pattern.test(input.value)) {
                errores[input.name] = input.getAttribute('title') || 'Formato inválido';
            }
        }
    });

    return {
        valido: Object.keys(errores).length === 0,
        errores: errores
    };
}

/**
 * Mostrar errores en formulario
 */
function mostrarErroresFormulario(formElement, errores) {
    // Limpiar errores anteriores
    formElement.querySelectorAll('.error-message').forEach(el => el.remove());
    formElement.querySelectorAll('input.error, select.error, textarea.error').forEach(el => {
        el.classList.remove('error');
    });

    // Mostrar nuevos errores
    Object.keys(errores).forEach(fieldName => {
        const input = formElement.querySelector(`[name="${fieldName}"]`);
        if (input) {
            input.classList.add('error');

            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = errores[fieldName];

            input.parentElement.appendChild(errorDiv);
        }
    });
}

/**
 * Limpiar errores del formulario
 */
function limpiarErroresFormulario(formElement) {
    formElement.querySelectorAll('.error-message').forEach(el => el.remove());
    formElement.querySelectorAll('input.error, select.error, textarea.error').forEach(el => {
        el.classList.remove('error');
    });
}

/**
 * Sanitizar entrada
 */
function sanitizar(texto) {
    const textarea = document.createElement('textarea');
    textarea.textContent = texto;
    return textarea.innerHTML;
}

/**
 * Validar entrada contra XSS
 */
function validarContraXSS(texto) {
    const regex = /<script|<iframe|<img|onerror|onload|onclick/i;
    return !regex.test(texto);
}
