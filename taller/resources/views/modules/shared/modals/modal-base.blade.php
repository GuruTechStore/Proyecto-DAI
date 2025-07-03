{{-- resources/views/shared/components/modal-base.blade.php --}}
@props([
    'id' => 'modal',
    'size' => 'md',
    'closable' => true,
    'backdrop' => true,
    'keyboard' => true,
    'static' => false,
    'centered' => true,
    'scrollable' => false,
    'fullscreen' => false
])

@php
$sizeClasses = [
    'xs' => 'max-w-xs',
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '3xl' => 'max-w-3xl',
    '4xl' => 'max-w-4xl',
    '5xl' => 'max-w-5xl',
    '6xl' => 'max-w-6xl',
    '7xl' => 'max-w-7xl',
    'full' => 'max-w-full'
];

$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];

$containerClasses = [
    'flex',
    'min-h-full',
    'text-center',
    $centered ? 'items-center' : 'items-start',
    'justify-center',
    'p-4',
    'sm:p-6',
    'lg:p-8'
];

$modalClasses = [
    'relative',
    'transform',
    'overflow-hidden',
    'rounded-lg',
    'bg-white',
    'dark:bg-gray-800',
    'text-left',
    'shadow-xl',
    'transition-all',
    'w-full',
    $sizeClass
];

if ($fullscreen) {
    $modalClasses = array_merge($modalClasses, ['h-full', 'max-w-none', 'max-h-none', 'm-0', 'rounded-none']);
    $containerClasses = array_merge($containerClasses, ['p-0']);
}

if ($scrollable) {
    $modalClasses[] = 'max-h-full';
}

$containerClass = implode(' ', $containerClasses);
$modalClass = implode(' ', $modalClasses);
@endphp

<!-- Modal -->
<div x-data="{ 
    show: false,
    
    open() {
        this.show = true;
        document.body.style.overflow = 'hidden';
        this.$nextTick(() => {
            this.focusFirstElement();
        });
    },
    
    close() {
        this.show = false;
        document.body.style.overflow = '';
        this.$dispatch('modal-closed', { id: '{{ $id }}' });
    },
    
    focusFirstElement() {
        const focusable = this.$el.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex=\"-1\"])'
        );
        if (focusable.length > 0) {
            focusable[0].focus();
        }
    },
    
    handleKeydown(event) {
        if (!{{ $keyboard ? 'true' : 'false' }}) return;
        
        if (event.key === 'Escape') {
            event.preventDefault();
            this.close();
        }
        
        // Trap focus within modal
        if (event.key === 'Tab') {
            const focusable = this.$el.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex=\"-1\"])'
            );
            const firstElement = focusable[0];
            const lastElement = focusable[focusable.length - 1];
            
            if (event.shiftKey) {
                if (document.activeElement === firstElement) {
                    event.preventDefault();
                    lastElement.focus();
                }
            } else {
                if (document.activeElement === lastElement) {
                    event.preventDefault();
                    firstElement.focus();
                }
            }
        }
    }
}" 
     x-show="show" 
     x-cloak
     id="{{ $id }}"
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.window="handleKeydown($event)"
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true"
     {{ $attributes }}>
    
    <!-- Background backdrop -->
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
         @if($backdrop && $closable && !$static)@click="close()"@endif></div>
    
    <!-- Modal panel -->
    <div class="{{ $containerClass }}">
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @if(!$static)@click.away="close()"@endif
             class="{{ $modalClass }}">
            
            {{ $slot }}
        </div>
    </div>
</div>

<script>
// Función global para controlar modales
window.modalControls = {
    open: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal && modal.__x) {
            modal.__x.$data.open();
        }
    },
    
    close: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal && modal.__x) {
            modal.__x.$data.close();
        }
    },
    
    toggle: function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal && modal.__x) {
            const isOpen = modal.__x.$data.show;
            if (isOpen) {
                modal.__x.$data.close();
            } else {
                modal.__x.$data.open();
            }
        }
    }
};

// Event listener para manejar eventos de modal
document.addEventListener('modal-closed', function(event) {
    console.log('Modal cerrado:', event.detail.id);
});
</script>