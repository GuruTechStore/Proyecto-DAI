{{-- resources/views/shared/components/breadcrumb.blade.php --}}
@props([
    'items' => [],
    'separator' => 'chevron',
    'showHome' => true,
    'homeUrl' => '/',
    'homeLabel' => 'Inicio'
])

@php
$separators = [
    'chevron' => '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>',
    'slash' => '<span class="text-gray-400">/</span>',
    'arrow' => '<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>',
    'dot' => '<span class="text-gray-400">•</span>'
];

$separatorHtml = $separators[$separator] ?? $separators['chevron'];
@endphp

<nav class="flex" aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        @if($showHome)
            <li class="inline-flex items-center">
                <a href="{{ $homeUrl }}" 
                   class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gestion-600 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    {{ $homeLabel }}
                </a>
            </li>
            
            @if(count($items) > 0)
                <li>
                    <div class="flex items-center">
                        {!! $separatorHtml !!}
                    </div>
                </li>
            @endif
        @endif
        
        @foreach($items as $index => $item)
            @php
                $itemConfig = is_array($item) ? $item : ['label' => $item];
                $label = $itemConfig['label'] ?? '';
                $url = $itemConfig['url'] ?? null;
                $icon = $itemConfig['icon'] ?? null;
                $active = $itemConfig['active'] ?? false;
                $isLast = $index === count($items) - 1;
            @endphp
            
            <li class="inline-flex items-center">
                @if($url && !$active && !$isLast)
                    <a href="{{ $url }}" 
                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gestion-600 dark:text-gray-400 dark:hover:text-white transition-colors">
                        @if($icon)
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $icon !!}
                            </svg>
                        @endif
                        {{ $label }}
                    </a>
                @else
                    <span class="inline-flex items-center text-sm font-medium {{ $active || $isLast ? 'text-gestion-600 dark:text-gestion-400' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($icon)
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $icon !!}
                            </svg>
                        @endif
                        {{ $label }}
                    </span>
                @endif
            </li>
            
            @if(!$isLast)
                <li>
                    <div class="flex items-center">
                        {!! $separatorHtml !!}
                    </div>
                </li>
            @endif
        @endforeach
    </ol>
</nav>

{{-- Breadcrumb con esquema JSON-LD para SEO --}}
@if(count($items) > 0)
    @push('meta')
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "BreadcrumbList",
            "itemListElement": [
                @if($showHome)
                {
                    "@type": "ListItem",
                    "position": 1,
                    "name": "{{ $homeLabel }}",
                    "item": "{{ url($homeUrl) }}"
                }{{ count($items) > 0 ? ',' : '' }}
                @endif
                @foreach($items as $index => $item)
                    @php
                        $itemConfig = is_array($item) ? $item : ['label' => $item];
                        $label = $itemConfig['label'] ?? '';
                        $url = $itemConfig['url'] ?? null;
                        $position = ($showHome ? 2 : 1) + $index;
                    @endphp
                    {
                        "@type": "ListItem",
                        "position": {{ $position }},
                        "name": "{{ $label }}"@if($url),
                        "item": "{{ url($url) }}"@endif
                    }{{ $index < count($items) - 1 ? ',' : '' }}
                @endforeach
            ]
        }
        </script>
    @endpush
@endif