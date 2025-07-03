{{-- Widget de Seguridad --}}
@canany(['auditoria.ver', 'seguridad.ver'])
<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Estado de Seguridad</dt>
                    <dd class="flex items-baseline">
                        <div class="text-2xl font-semibold text-gray-900">
                            <span class="text-green-600">Seguro</span>
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    
    <div class="bg-gray-50 px-6 py-3">
        <div class="text-sm space-y-2">
            @php
                $recentLogs = \App\Models\SecurityLog::latest()->take(3)->get();
                $failedLogins = \App\Models\SecurityLog::where('tipo', 'failed_login')
                    ->where('created_at', '>=', now()->subHours(24))
                    ->count();
            @endphp
            
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Intentos fallidos (24h):</span>
                <span class="font-medium {{ $failedLogins > 5 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $failedLogins }}
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Usuarios activos:</span>
                <span class="font-medium text-gray-900">
                    {{ \App\Models\Usuario::where('last_activity', '>=', now()->subMinutes(15))->count() }}
                </span>
            </div>
            
            @if($recentLogs->count() > 0)
                <div class="mt-3 border-t pt-2">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Actividad Reciente
                    </h4>
                    <div class="space-y-1">
                        @foreach($recentLogs as $log)
                            <div class="flex items-center text-xs">
                                <div class="w-2 h-2 rounded-full mr-2 {{ 
                                    $log->severity >= 3 ? 'bg-red-400' : 
                                    ($log->severity == 2 ? 'bg-yellow-400' : 'bg-green-400') 
                                }}"></div>
                                <span class="text-gray-600 truncate">
                                    {{ $log->descripcion }}
                                </span>
                                <span class="text-gray-400 ml-auto">
                                    {{ $log->created_at->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        
        <div class="mt-3">
            <a href="{{ route('admin.security.dashboard') }}" 
               class="text-sm font-medium text-red-700 hover:text-red-900 transition-colors">
                Ver panel completo →
            </a>
        </div>
    </div>
</div>
@endcanany