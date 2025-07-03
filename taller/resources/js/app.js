// ===== ARCHIVO: resources/js/app.js =====
// Sistema JavaScript mejorado con Alpine.js y componentes modulares

// ===== IMPORTACIONES =====
import './bootstrap';
import Alpine from 'alpinejs';

// ===== CONFIGURACIÓN GLOBAL =====
window.Alpine = Alpine;

// ===== STORE GLOBAL DE APLICACIÓN =====
Alpine.store('app', {
    // Estado global
    user: null,
    theme: localStorage.getItem('theme') || 'light',
    loading: false,
    notifications: [],
    
    // Configuración
    config: {
        apiTimeout: 10000,
        toastDuration: 5000,
        debounceDelay: 300,
    },
    
    // Métodos globales
    init() {
        this.loadUser();
        this.initTheme();
        this.initNotifications();
    },
    
    loadUser() {
        // Cargar datos del usuario desde meta tag
        const userRole = document.querySelector('meta[name="user-role"]')?.content;
        const userPermissions = JSON.parse(document.querySelector('meta[name="user-permissions"]')?.content || '[]');
        
        this.user = {
            role: userRole,
            permissions: userPermissions,
            hasPermission: (permission) => userPermissions.includes(permission)
        };
    },
    
    initTheme() {
        document.documentElement.classList.toggle('dark', this.theme === 'dark');
    },
    
    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        localStorage.setItem('theme', this.theme);
        document.documentElement.classList.toggle('dark', this.theme === 'dark');
    },
    
    initNotifications() {
        // Cargar notificaciones iniciales
        this.loadNotifications();
        
        // Configurar polling para notificaciones
        setInterval(() => {
            this.loadNotifications();
        }, 30000); // Cada 30 segundos
    },
    
    async loadNotifications() {
        try {
            const response = await fetch('/api/notifications');
            const data = await response.json();
            this.notifications = data.notifications || [];
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    },
    
    showLoading() {
        this.loading = true;
    },
    
    hideLoading() {
        this.loading = false;
    }
});

// ===== COMPONENTES PRINCIPALES =====

// Componente de navegación mejorado
Alpine.data('navigation', () => ({
    sidebarOpen: false,
    collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
    
    init() {
        // Escuchar cambios de tamaño de ventana
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                this.sidebarOpen = false;
            }
        });
        
        // Restaurar estado del sidebar
        this.$watch('collapsed', (value) => {
            localStorage.setItem('sidebar-collapsed', value);
            this.$dispatch('sidebar-toggled', { collapsed: value });
        });
    },
    
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
    },
    
    closeSidebar() {
        this.sidebarOpen = false;
    },
    
    toggleCollapsed() {
        this.collapsed = !this.collapsed;
    }
}));

// Sistema de Toast mejorado
Alpine.data('toastSystem', () => ({
    toasts: [],
    nextId: 1,
    
    init() {
        // Función global para mostrar toasts
        window.showToast = (message, type = 'info', title = null, duration = 5000) => {
            this.show(message, type, title, duration);
        };
        
        // Función global para toast de éxito
        window.showSuccess = (message, title = 'Éxito') => {
            this.show(message, 'success', title);
        };
        
        // Función global para toast de error
        window.showError = (message, title = 'Error') => {
            this.show(message, 'error', title);
        };
        
        // Función global para toast de advertencia
        window.showWarning = (message, title = 'Advertencia') => {
            this.show(message, 'warning', title);
        };
    },
    
    show(message, type = 'info', title = null, duration = 5000) {
        const toast = {
            id: this.nextId++,
            message,
            type,
            title: title || this.getDefaultTitle(type),
            visible: true,
            progress: 100
        };
        
        this.toasts.push(toast);
        
        // Animar progreso
        const startTime = Date.now();
        const interval = setInterval(() => {
            const elapsed = Date.now() - startTime;
            const remaining = Math.max(0, duration - elapsed);
            toast.progress = (remaining / duration) * 100;
            
            if (remaining <= 0) {
                clearInterval(interval);
                this.remove(toast.id);
            }
        }, 50);
        
        toast.interval = interval;
    },
    
    remove(id) {
        const index = this.toasts.findIndex(t => t.id === id);
        if (index > -1) {
            const toast = this.toasts[index];
            if (toast.interval) {
                clearInterval(toast.interval);
            }
            
            toast.visible = false;
            setTimeout(() => {
                this.toasts.splice(index, 1);
            }, 300);
        }
    },
    
    getDefaultTitle(type) {
        const titles = {
            success: 'Éxito',
            error: 'Error',
            warning: 'Advertencia',
            info: 'Información'
        };
        return titles[type] || 'Notificación';
    },
    
    getToastClasses(type) {
        const classes = {
            success: 'border-green-400 bg-green-50 text-green-800',
            error: 'border-red-400 bg-red-50 text-red-800',
            warning: 'border-yellow-400 bg-yellow-50 text-yellow-800',
            info: 'border-blue-400 bg-blue-50 text-blue-800'
        };
        return classes[type] || classes.info;
    },
    
    getToastIcon(type) {
        const icons = {
            success: 'check',
            error: 'x',
            warning: 'exclamation',
            info: 'information-circle'
        };
        return icons[type] || icons.info;
    }
}));

// Modal system avanzado
Alpine.data('modal', (options = {}) => ({
    isOpen: false,
    title: options.title || '',
    size: options.size || 'md', // sm, md, lg, xl, full
    closable: options.closable !== false,
    
    open(title = null) {
        if (title) this.title = title;
        this.isOpen = true;
        document.body.style.overflow = 'hidden';
        this.$nextTick(() => {
            this.$refs.modal?.focus();
        });
    },
    
    close() {
        if (!this.closable) return;
        this.isOpen = false;
        document.body.style.overflow = 'auto';
    },
    
    handleEscape(event) {
        if (event.key === 'Escape' && this.closable) {
            this.close();
        }
    },
    
    getSizeClasses() {
        const sizes = {
            sm: 'max-w-md',
            md: 'max-w-lg',
            lg: 'max-w-2xl',
            xl: 'max-w-4xl',
            full: 'max-w-full mx-4'
        };
        return sizes[this.size] || sizes.md;
    }
}));

// Dropdown mejorado
Alpine.data('dropdown', (options = {}) => ({
    isOpen: false,
    placement: options.placement || 'bottom-end',
    
    toggle() {
        this.isOpen = !this.isOpen;
    },
    
    open() {
        this.isOpen = true;
    },
    
    close() {
        this.isOpen = false;
    },
    
    handleClickAway(event) {
        if (!this.$el.contains(event.target)) {
            this.close();
        }
    }
}));

// Formulario con validación avanzada
Alpine.data('form', (config = {}) => ({
    loading: false,
    errors: {},
    data: config.data || {},
    rules: config.rules || {},
    
    init() {
        // Observar cambios en los datos para limpiar errores
        Object.keys(this.data).forEach(field => {
            this.$watch(`data.${field}`, () => {
                this.clearError(field);
            });
        });
    },
    
    async submit(url, options = {}) {
        this.loading = true;
        this.errors = {};
        
        try {
            const response = await fetch(url, {
                method: options.method || 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    ...options.headers
                },
                body: JSON.stringify(this.data)
            });
            
            if (!response.ok) {
                if (response.status === 422) {
                    const errorData = await response.json();
                    this.errors = errorData.errors || {};
                    throw new Error('Validation failed');
                }
                throw new Error(`HTTP ${response.status}`);
            }
            
            const result = await response.json();
            
            // Callback de éxito
            if (options.onSuccess) {
                options.onSuccess(result);
            } else {
                showSuccess(result.message || 'Operación completada exitosamente');
            }
            
            return result;
            
        } catch (error) {
            console.error('Form submission error:', error);
            
            // Callback de error
            if (options.onError) {
                options.onError(error);
            } else if (error.message !== 'Validation failed') {
                showError('Ocurrió un error al procesar la solicitud');
            }
            
            throw error;
        } finally {
            this.loading = false;
        }
    },
    
    validate() {
        this.errors = {};
        let isValid = true;
        
        Object.keys(this.rules).forEach(field => {
            const value = this.data[field];
            const fieldRules = this.rules[field];
            
            fieldRules.forEach(rule => {
                if (typeof rule === 'string') {
                    // Regla simple como 'required'
                    if (!this.validateRule(value, rule)) {
                        this.setError(field, this.getErrorMessage(field, rule));
                        isValid = false;
                    }
                } else if (typeof rule === 'object') {
                    // Regla con parámetros como { min: 5 }
                    const ruleName = Object.keys(rule)[0];
                    const ruleValue = rule[ruleName];
                    
                    if (!this.validateRule(value, ruleName, ruleValue)) {
                        this.setError(field, this.getErrorMessage(field, ruleName, ruleValue));
                        isValid = false;
                    }
                }
            });
        });
        
        return isValid;
    },
    
    validateRule(value, rule, param = null) {
        const validators = {
            required: (val) => val !== null && val !== undefined && val !== '',
            email: (val) => !val || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
            min: (val, min) => !val || val.length >= min,
            max: (val, max) => !val || val.length <= max,
            numeric: (val) => !val || /^\d+$/.test(val),
            alpha: (val) => !val || /^[a-zA-Z]+$/.test(val),
            alphanumeric: (val) => !val || /^[a-zA-Z0-9]+$/.test(val),
        };
        
        const validator = validators[rule];
        return validator ? validator(value, param) : true;
    },
    
    getErrorMessage(field, rule, param = null) {
        const messages = {
            required: `El campo ${field} es requerido`,
            email: `El campo ${field} debe ser un email válido`,
            min: `El campo ${field} debe tener al menos ${param} caracteres`,
            max: `El campo ${field} debe tener máximo ${param} caracteres`,
            numeric: `El campo ${field} debe ser numérico`,
            alpha: `El campo ${field} debe contener solo letras`,
            alphanumeric: `El campo ${field} debe contener solo letras y números`,
        };
        
        return messages[rule] || `El campo ${field} no es válido`;
    },
    
    setError(field, message) {
        if (!this.errors[field]) {
            this.errors[field] = [];
        }
        this.errors[field].push(message);
    },
    
    clearError(field) {
        if (this.errors[field]) {
            delete this.errors[field];
        }
    },
    
    hasError(field) {
        return this.errors[field] && this.errors[field].length > 0;
    },
    
    getError(field) {
        return this.hasError(field) ? this.errors[field][0] : '';
    }
}));

// DataTable con funcionalidades avanzadas
Alpine.data('dataTable', (config = {}) => ({
    data: [],
    filteredData: [],
    currentPage: 1,
    itemsPerPage: config.itemsPerPage || 10,
    sortField: null,
    sortDirection: 'asc',
    searchQuery: '',
    loading: false,
    selected: [],
    filters: {},
    
    async init() {
        if (config.url) {
            await this.loadData();
        } else if (config.data) {
            this.data = config.data;
            this.applyFilters();
        }
    },
    
    async loadData() {
        this.loading = true;
        
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.itemsPerPage,
                search: this.searchQuery,
                sort_field: this.sortField || '',
                sort_direction: this.sortDirection,
                ...this.filters
            });
            
            const response = await fetch(`${config.url}?${params}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const result = await response.json();
            this.data = result.data || [];
            this.applyFilters();
            
        } catch (error) {
            console.error('Error loading data:', error);
            showError('Error al cargar los datos');
        } finally {
            this.loading = false;
        }
    },
    
    applyFilters() {
        let filtered = [...this.data];
        
        // Aplicar búsqueda
        if (this.searchQuery) {
            const query = this.searchQuery.toLowerCase();
            filtered = filtered.filter(item => 
                Object.values(item).some(value => 
                    String(value).toLowerCase().includes(query)
                )
            );
        }
        
        // Aplicar filtros adicionales
        Object.keys(this.filters).forEach(key => {
            const filterValue = this.filters[key];
            if (filterValue) {
                filtered = filtered.filter(item => 
                    String(item[key]).toLowerCase().includes(String(filterValue).toLowerCase())
                );
            }
        });
        
        // Aplicar ordenamiento
        if (this.sortField) {
            filtered.sort((a, b) => {
                const aVal = a[this.sortField];
                const bVal = b[this.sortField];
                
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        }
        
        this.filteredData = filtered;
        this.currentPage = 1; // Reset a primera página
    },
    
    sort(field) {
        if (this.sortField === field) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortField = field;
            this.sortDirection = 'asc';
        }
        this.applyFilters();
    },
    
    search: debounce(function() {
        this.applyFilters();
    }, 300),
    
    setFilter(key, value) {
        this.filters[key] = value;
        this.applyFilters();
    },
    
    clearFilters() {
        this.filters = {};
        this.searchQuery = '';
        this.applyFilters();
    },
    
    get paginatedData() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredData.slice(start, end);
    },
    
    get totalPages() {
        return Math.ceil(this.filteredData.length / this.itemsPerPage);
    },
    
    get totalItems() {
        return this.filteredData.length;
    },
    
    changePage(page) {
        if (page >= 1 && page <= this.totalPages) {
            this.currentPage = page;
        }
    },
    
    nextPage() {
        this.changePage(this.currentPage + 1);
    },
    
    prevPage() {
        this.changePage(this.currentPage - 1);
    },
    
    toggleSelection(item) {
        const index = this.selected.findIndex(s => s.id === item.id);
        if (index > -1) {
            this.selected.splice(index, 1);
        } else {
            this.selected.push(item);
        }
    },
    
    selectAll() {
        this.selected = [...this.paginatedData];
    },
    
    clearSelection() {
        this.selected = [];
    },
    
    get isAllSelected() {
        return this.paginatedData.length > 0 && 
               this.paginatedData.every(item => 
                   this.selected.some(s => s.id === item.id)
               );
    },
    
    get isPartiallySelected() {
        return this.selected.length > 0 && !this.isAllSelected;
    }
}));

// Sistema de confirmación
Alpine.data('confirmation', () => ({
    isOpen: false,
    title: '',
    message: '',
    confirmText: 'Confirmar',
    cancelText: 'Cancelar',
    type: 'warning', // success, warning, danger
    onConfirm: null,
    onCancel: null,
    
    show(options = {}) {
        this.title = options.title || '¿Confirmar acción?';
        this.message = options.message || '¿Está seguro de que desea continuar?';
        this.confirmText = options.confirmText || 'Confirmar';
        this.cancelText = options.cancelText || 'Cancelar';
        this.type = options.type || 'warning';
        this.onConfirm = options.onConfirm || null;
        this.onCancel = options.onCancel || null;
        this.isOpen = true;
    },
    
    confirm() {
        if (this.onConfirm) {
            this.onConfirm();
        }
        this.close();
    },
    
    cancel() {
        if (this.onCancel) {
            this.onCancel();
        }
        this.close();
    },
    
    close() {
        this.isOpen = false;
        this.onConfirm = null;
        this.onCancel = null;
    },
    
    getTypeClasses() {
        const classes = {
            success: 'text-green-600',
            warning: 'text-yellow-600',
            danger: 'text-red-600'
        };
        return classes[this.type] || classes.warning;
    }
}));

// ===== UTILIDADES GLOBALES =====

// Debounce utility
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle utility
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// Format currency
window.formatCurrency = function(amount, currency = 'PEN') {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: currency
    }).format(amount);
};

// Format date
window.formatDate = function(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return new Intl.DateTimeFormat('es-PE', { ...defaultOptions, ...options }).format(new Date(date));
};

// API Helper
window.api = {
    async get(url, options = {}) {
        return this.request(url, { method: 'GET', ...options });
    },
    
    async post(url, data, options = {}) {
        return this.request(url, { 
            method: 'POST', 
            body: JSON.stringify(data),
            ...options 
        });
    },
    
    async put(url, data, options = {}) {
        return this.request(url, { 
            method: 'PUT', 
            body: JSON.stringify(data),
            ...options 
        });
    },
    
    async delete(url, options = {}) {
        return this.request(url, { method: 'DELETE', ...options });
    },
    
    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        };
        
        const config = { ...defaultOptions, ...options };
        config.headers = { ...defaultOptions.headers, ...options.headers };
        
        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            }
            
            return await response.text();
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }
};

// ===== FUNCIONES GLOBALES =====

// Función global para confirmaciones
window.confirm = function(options = {}) {
    return new Promise((resolve) => {
        const confirmElement = document.querySelector('[x-data*="confirmation"]');
        if (confirmElement && confirmElement._x_dataStack) {
            const confirmData = confirmElement._x_dataStack[0];
            confirmData.show({
                ...options,
                onConfirm: () => resolve(true),
                onCancel: () => resolve(false)
            });
        } else {
            resolve(window.confirm(options.message || '¿Continuar?'));
        }
    });
};

// Validación de permisos
window.can = function(permission) {
    const store = Alpine.store('app');
    return store.user && store.user.hasPermission(permission);
};

// ===== INICIALIZACIÓN =====

// Inicializar Alpine cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
    
    // Inicializar store global
    Alpine.store('app').init();
    
    // Manejar errores globales
    window.addEventListener('error', (e) => {
        console.error('Global error:', e.error);
        showError('Ocurrió un error inesperado');
    });
    
    // Manejar errores de promesas no capturadas
    window.addEventListener('unhandledrejection', (e) => {
        console.error('Unhandled promise rejection:', e.reason);
        showError('Error en la comunicación con el servidor');
    });
});

// Performance monitoring
if ('performance' in window) {
    window.addEventListener('load', () => {
        setTimeout(() => {
            const perfData = performance.getEntriesByType('navigation')[0];
            console.log('Page load time:', perfData.loadEventEnd - perfData.loadEventStart, 'ms');
        }, 0);
    });
}

// Exportar Alpine globalmente
window.Alpine = Alpine;