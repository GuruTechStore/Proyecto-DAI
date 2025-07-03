{{-- Widget de Actividad --}}
@canany(['auditoria.ver', 'seguridad.ver'])
<div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Actividad del Sistema</dt>
                    <dd class="flex items-baseline">
                        <div class="text-2xl font-semibold text-gray-900">
                            @php
                                $todayActivity = \App\Models\UserActivity::whereDate('fecha', today())->count();
                            @endphp
                            {{ $todayActivity }}
                        </div>
                        <div class="ml-2 text-sm text-gray-500">
                            eventos hoy
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
    
    <div class="bg-gray-50 px-6 py-3">
        <div class="text-sm space-y-2">
            @php
                $recentActivities = \App\Models\UserActivity::with('usuario')
                    ->latest('ultima_actividad')
                    ->take(5)
                    ->get();
                    
                $activeUsers = \App\Models\Usuario::where('last_activity', '>=', now()->subMinutes(15))->count();
                $totalSessions = \App\Models\UserActivity::whereDate('fecha', today())->sum('contador_accesos');
            @endphp
            
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Usuarios activos:</span>
                <span class="font-medium text-green-600">{{ $activeUsers }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Sesiones hoy:</span>
                <span class="font-medium text-gray-900">{{ $totalSessions }}</span>
            </div>
            
            @if($recentActivities->count() > 0)
                <div class="mt-3 border-t pt-2">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Actividad Reciente
                    </h4>
                    <div class="space-y-1 max-h-24 overflow-y-auto">
                        @foreach($recentActivities as $activity)
                            <div class="flex items-center text-xs">
                                <div class="w-2 h-2 rounded-full bg-blue-400 mr-2"></div>
                                <span class="text-gray-600 truncate flex-1">
                                    <span class="font-medium">{{ $activity->usuario->name ?? 'Usuario' }}</span>
                                    en {{ $activity->modulo }}
                                </span>
                                <span class="text-gray-400 ml-2">
                                    {{ $activity->ultima_actividad->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        
        <div class="mt-3">
            <a href="{{ route('admin.activity.index') }}" 
               class="text-sm font-medium text-blue-700 hover:text-blue-900 transition-colors">
                Ver actividad completa →
            </a>
        </div>
    </div>
</div>
@endcanany