{{-- resources/views/shared/components/loading.blade.php --}}
@props([
    'type' => 'spinner',
    'size' => 'md',
    'color' => 'primary',
    'text' => null,
    'overlay' => false,
    'fullscreen' => false
])

@php
$sizes = [
    'xs' => 'w-4 h-4',
    'sm' => 'w-6 h-6',
    'md' => 'w-8 h-8',
    'lg' => 'w-12 h-12',
    'xl' => 'w-16 h-16'
];

$colors = [
    'primary' => 'text-gestion-600',
    'secondary' => 'text-gray-600',
    'white' => 'text-white',
    'success' => 'text-green-600',
    'error' => 'text-red-600',
    'warning' => 'text-yellow-600'
];

$spinnerSize = $sizes[$size] ?? $sizes['md'];
$spinnerColor = $colors[$color] ?? $colors['primary'];
@endphp

@if($fullscreen)
    <div class="fixed inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="text-center">
            @if($type === 'spinner')
                <div class="inline-block {{ $spinnerSize }} {{ $spinnerColor }} animate-spin rounded-full border-4 border-solid border-current border-r-transparent motion-reduce:animate-[spin_1.5s_linear_infinite]"></div>
            @elseif($type === 'dots')
                <div class="flex space-x-2">
                    <div class="w-3 h-3 {{ $spinnerColor }} rounded-full animate-pulse"></div>
                    <div class="w-3 h-3 {{ $spinnerColor }} rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                    <div class="w-3 h-3 {{ $spinnerColor }} rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                </div>
            @elseif($type === 'bars')
                <div class="flex space-x-1">
                    <div class="w-2 h-8 {{ $spinnerColor }} rounded animate-pulse"></div>
                    <div class="w-2 h-8 {{ $spinnerColor }} rounded animate-pulse" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-8 {{ $spinnerColor }} rounded animate-pulse" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-8 {{ $spinnerColor }} rounded animate-pulse" style="animation-delay: 0.3s"></div>
                </div>
            @endif
            
            @if($text)
                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">{{ $text }}</p>
            @endif
        </div>
    </div>
@elseif($overlay)
    <div class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm z-40 flex items-center justify-center rounded-lg">
        <div class="text-center">
            @if($type === 'spinner')
                <div class="inline-block {{ $spinnerSize }} {{ $spinnerColor }} animate-spin rounded-full border-4 border-solid border-current border-r-transparent motion-reduce:animate-[spin_1.5s_linear_infinite]"></div>
            @elseif($type === 'dots')
                <div class="flex space-x-2">
                    <div class="w-3 h-3 {{ $spinnerColor }} rounded-full animate-pulse"></div>
                    <div class="w-3 h-3 {{ $spinnerColor }} rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                    <div class="w-3 h-3 {{ $spinnerColor }} rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
                </div>
            @elseif($type === 'bars')
                <div class="flex space-x-1">
                    <div class="w-2 h-6 {{ $spinnerColor }} rounded animate-pulse"></div>
                    <div class="w-2 h-6 {{ $spinnerColor }} rounded animate-pulse" style="animation-delay: 0.1s"></div>
                    <div class="w-2 h-6 {{ $spinnerColor }} rounded animate-pulse" style="animation-delay: 0.2s"></div>
                    <div class="w-2 h-6 {{ $spinnerColor }} rounded animate-pulse" style="animation-delay: 0.3s"></div>
                </div>
            @endif
            
            @if($text)
                <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">{{ $text }}</p>
            @endif
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
        @if($type === 'spinner')
            <div class="inline-block {{ $spinnerSize }} {{ $spinnerColor }} animate-spin rounded-full border-4 border-solid border-current border-r-transparent motion-reduce:animate-[spin_1.5s_linear_infinite]"></div>
        @elseif($type === 'dots')
            <div class="flex space-x-1">
                <div class="w-2 h-2 {{ $spinnerColor }} rounded-full animate-pulse"></div>
                <div class="w-2 h-2 {{ $spinnerColor }} rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
                <div class="w-2 h-2 {{ $spinnerColor }} rounded-full animate-pulse" style="animation-delay: 0.4s"></div>
            </div>
        @elseif($type === 'bars')
            <div class="flex space-x-0.5">
                <div class="w-1 h-4 {{ $spinnerColor }} rounded animate-pulse"></div>
                <div class="w-1 h-4 {{ $spinnerColor }} rounded animate-pulse" style="animation-delay: 0.1s"></div>
                <div class="w-1 h-4 {{ $spinnerColor }} rounded animate-pulse" style="animation-delay: 0.2s"></div>
                <div class="w-1 h-4 {{ $spinnerColor }} rounded animate-pulse" style="animation-delay: 0.3s"></div>
            </div>
        @elseif($type === 'pulse')
            <div class="{{ $spinnerSize }} {{ $spinnerColor }} rounded-full animate-pulse"></div>
        @endif
        
        @if($text)
            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $text }}</span>
        @endif
    </div>
@endif