@extends('layouts.app')

@section('title', 'Actividad del Usuario')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                Actividad Reciente
            </h3>
            
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @forelse($activities as $index => $activity)
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3">
                                <div>
                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                    <div>
                                        <p class="text-sm text-gray-500">
                                            <span class="font-medium text-gray-900">{{ $activity['action'] }}</span>
                                        </p>
                                        <p class="text-sm text-gray-500">{{ $activity['description'] }}</p>
                                        <p class="text-xs text-gray-400">IP: {{ $activity['ip'] }}</p>
                                    </div>
                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                        {{ $activity['created_at']->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="text-center py-6">
                        <p class="text-gray-500">No hay actividad registrada</p>
                    </li>
                    @endforelse
                </ul>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                    ← Volver al Perfil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection