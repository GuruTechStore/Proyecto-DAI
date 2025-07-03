<?php
// database/migrations/2025_07_03_000000_fix_clientes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Agregar campos faltantes
            $table->string('tipo_documento', 20)->default('DNI')->after('apellido');
            $table->string('documento', 20)->nullable()->after('tipo_documento');
            $table->text('direccion')->nullable()->after('email');
            
            // Agregar índices
            $table->index('documento');
            $table->index('tipo_documento');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex(['documento']);
            $table->dropIndex(['tipo_documento']);
            $table->dropColumn(['tipo_documento', 'documento', 'direccion']);
        });
    }
};