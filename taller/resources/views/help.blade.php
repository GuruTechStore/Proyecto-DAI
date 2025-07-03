@extends('layouts.app')

@section('title', 'Ayuda')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Centro de Ayuda</h1>
    <p>Página de ayuda en construcción...</p>
    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline">
        ← Volver al Dashboard
    </a>
</div>
@endsection