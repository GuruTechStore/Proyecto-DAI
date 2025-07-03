// resources/js/global-functions.js
// Funciones globales para manejo de notificaciones y validaciones

/**
 * Mostrar notificación de éxito
 */
window.showSuccessNotification = function(message, timer = 3000) {
    Swal.fire({
        title: '¡Éxito!',
        text: message,
        icon: 'success',
        timer: timer,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
};

/**
 * Mostrar notificación de error
 */
window.showErrorNotification = function(message, confirmText = 'Entendido') {
    Swal.fire({
        title: 'Error',
        text: message,
        icon: 'error',
        confirmButtonColor: '#ef4444',
        confirmButtonText: confirmText
    });
};

/**
 * Mostrar notificación de advertencia
 */
window.showWarningNotification = function(message, confirmText = 'Entendido') {
    Swal.fire({
        title: 'Advertencia',
        text: message,
        icon: 'warning',
        confirmButtonColor: '#f59e0b',
        confirmButtonText: confirmText
    });
};

/**
 * Mostrar notificación de información
 */
window.showInfoNotification = function(message, confirmText = 'Entendido') {
    Swal.fire({
        title: 'Información',
        text: message,
        icon: 'info',
        confirmButtonColor: '#3b82f6',
        confirmButtonText: confirmText
    });
};

/**
 * Mostrar diálogo de confirmación
 */
window.showConfirmDialog = function(title, text, confirmText = 'Sí, continuar', cancelText = 'Cancelar') {
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true
    });
};

/**
 * Mostrar diálogo de confirmación para eliminación
 */
window.showDeleteConfirmDialog = function(itemName = 'este elemento') {
    return Swal.fire({
        title: '¿Está seguro?',
        text: `¿Desea eliminar ${itemName}? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    });
};

/**
 * Mostrar loader/spinner global
 */
window.showLoader = function(message = 'Procesando...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

/**
 * Ocultar loader/spinner global
 */
window.hideLoader = function() {
    Swal.close();
};

/**
 * Validar formato de email
 */
window.validateEmail = function(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
};

/**
 * Validar formato de teléfono
 */
window.validatePhone = function(phone) {
    const re = /^[\d\-\+\(\)\s]{9,}$/;
    return re.test(phone);
};

/**
 * Validar DNI peruano (8 dígitos)
 */
window.validateDNI = function(dni) {
    const cleanDNI = dni.replace(/[^0-9]/g, '');
    return cleanDNI.length === 8;
};

/**
 * Validar RUC peruano (11 dígitos)
 */
window.validateRUC = function(ruc) {
    const cleanRUC = ruc.replace(/[^0-9]/g, '');
    return cleanRUC.length === 11;
};

/**
 * Formatear número de documento
 */
window.formatDocument = function(value, type) {
    const cleanValue = value.replace(/[^0-9]/g, '');
    
    switch(type) {
        case 'DNI':
            return cleanValue.slice(0, 8);
        case 'RUC':
            return cleanValue.slice(0, 11);
        default:
            return value;
    }
};

/**
 * Formatear teléfono
 */
window.formatPhone = function(phone) {
    const cleanPhone = phone.replace(/[^0-9]/g, '');
    
    if (cleanPhone.length >= 9) {
        return cleanPhone.replace(/(\d{3})(\d{3})(\d{3})/, '$1-$2-$3');
    }
    
    return phone;
};

/**
 * Manejar errores de red
 */
window.handleNetworkError = function(error) {
    console.error('Network error:', error);
    
    if (error.name === 'TypeError' && error.message.includes('fetch')) {
        showErrorNotification('Error de conexión. Verifique su conexión a internet.');
    } else {
        showErrorNotification('Error inesperado. Por favor, inténtelo nuevamente.');
    }
};

/**
 * Manejar errores de validación del servidor
 */
window.handleValidationErrors = function(errors, formComponent = null) {
    if (typeof errors === 'object' && errors !== null) {
        // Si se proporciona el componente del formulario, actualizar sus errores
        if (formComponent && typeof formComponent.errors !== 'undefined') {
            formComponent.errors = errors;
        }
        
        // Mostrar el primer error en una notificación
        const firstError = Object.values(errors)[0];
        if (Array.isArray(firstError)) {
            showErrorNotification(firstError[0]);
        } else {
            showErrorNotification(firstError);
        }
    } else {
        showErrorNotification('Error de validación');
    }
};

/**
 * Debounce function para optimizar búsquedas
 */
window.debounce = function(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
};

/**
 * Scroll suave a un elemento
 */
window.smoothScrollTo = function(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
};

/**
 * Copiar texto al portapapeles
 */
window.copyToClipboard = function(text) {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text).then(() => {
            showSuccessNotification('Texto copiado al portapapeles');
        }).catch(() => {
            showErrorNotification('Error al copiar al portapapeles');
        });
    } else {
        // Fallback para navegadores más antiguos
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showSuccessNotification('Texto copiado al portapapeles');
        } catch (error) {
            showErrorNotification('Error al copiar al portapapeles');
        } finally {
            textArea.remove();
        }
    }
};

/**
 * Formatear fecha para mostrar
 */
window.formatDate = function(dateString, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-PE', finalOptions);
    } catch (error) {
        return dateString;
    }
};

/**
 * Formatear moneda peruana
 */
window.formatCurrency = function(amount) {
    try {
        return new Intl.NumberFormat('es-PE', {
            style: 'currency',
            currency: 'PEN'
        }).format(amount);
    } catch (error) {
        return `S/ ${amount}`;
    }
};

/**
 * Validar y limpiar campos de formulario automáticamente
 */
window.setupFormValidation = function(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    
    // Agregar validación en tiempo real a campos de entrada
    form.querySelectorAll('input[type="email"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !validateEmail(this.value)) {
                this.classList.add('border-red-300');
                this.classList.remove('border-gray-300');
            } else {
                this.classList.remove('border-red-300');
                this.classList.add('border-gray-300');
            }
        });
    });
    
    form.querySelectorAll('input[type="tel"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value && !validatePhone(this.value)) {
                this.classList.add('border-red-300');
                this.classList.remove('border-gray-300');
            } else {
                this.classList.remove('border-red-300');
                this.classList.add('border-gray-300');
            }
        });
    });
};

// Inicializar funciones globales cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Configurar validación automática para todos los formularios
    setupFormValidation('form');
    
    // Manejar errores globales de fetch
    window.addEventListener('unhandledrejection', function(event) {
        if (event.reason && event.reason.name === 'TypeError') {
            handleNetworkError(event.reason);
            event.preventDefault();
        }
    });
});

// Exportar funciones para uso en módulos ES6 si es necesario
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showSuccessNotification,
        showErrorNotification,
        showWarningNotification,
        showInfoNotification,
        showConfirmDialog,
        showDeleteConfirmDialog,
        validateEmail,
        validatePhone,
        validateDNI,
        validateRUC,
        formatDocument,
        formatPhone,
        handleNetworkError,
        handleValidationErrors,
        debounce,
        copyToClipboard,
        formatDate,
        formatCurrency
    };
}