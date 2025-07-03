@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Mi Perfil
                </h3>
                <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                    Editar Perfil
                </a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                    <p class="mt-1 text-sm text-gray-900">
                        {{ ($user->nombres ?? '') . ' ' . ($user->apellidos ?? '') }}
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->email ?? 'No definido' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Usuario</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->username ?? 'No definido' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $user->telefono ?? 'No definido' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Último acceso</label>
                    <p class="mt-1 text-sm text-gray-900">
                        {{ $user->ultimo_acceso ? $user->ultimo_acceso->format('d/m/Y H:i') : 'Nunca' }}
                    </p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex space-x-3">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        Editar Perfil
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-secondary">
                        Seguridad
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection