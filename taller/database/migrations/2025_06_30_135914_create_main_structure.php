<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== TABLAS MAESTRAS ==========
        
        // 1. Clientes
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100)->nullable();
            $table->string('telefono', 20);
            $table->string('email', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['nombre', 'apellido']);
            $table->index('telefono');
            $table->unique('email');
        });

        // 2. Categorías (corregido: eliminados campos duplicados y añadido softDeletes)
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
            $table->softDeletes(); // Añadido para resolver el error del seeder
        });

        // 3. Proveedores
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ruc', 20)->nullable()->unique();
            $table->string('contacto', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('direccion')->nullable();
            $table->string('banco', 100)->nullable();
            $table->string('numero_cuenta', 50)->nullable();
            $table->string('tipo_cuenta', 20)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('nombre');
            $table->index('ruc');
        });

        // 4. Empleados
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('dni', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->string('direccion')->nullable();
            $table->string('especialidad', 100)->nullable();
            $table->date('fecha_contratacion')->default(now());
            $table->decimal('salario', 10, 2)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('dni');
            $table->index(['nombres', 'apellidos']);
            $table->index('activo');
        });

        // 5. Productos (corregido el índice de stock_minimo)
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre', 255);
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->string('unidad_medida', 20)->default('unidad');
            $table->string('ubicacion', 100)->nullable();
            $table->string('imagen_url')->nullable();
            $table->integer('garantia_dias')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('codigo');
            $table->index('nombre');
            $table->index(['categoria_id', 'activo']);
            $table->index(['stock', 'stock_minimo']); // Corregido el índice compuesto
        });

        // ========== TABLAS DE USUARIOS Y SEGURIDAD ==========
        
        // 6. Usuarios (para el sistema)
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->onDelete('cascade');
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->string('tipo_usuario', 50)->default('empleado');
            $table->boolean('activo')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->datetime('ultimo_login')->nullable();
            $table->integer('intentos_fallidos')->default(0);
            $table->boolean('bloqueado')->default(false);
            $table->datetime('bloqueado_hasta')->nullable();
            $table->boolean('force_password_change')->default(false);
            $table->string('remember_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('username');
            $table->index('email');
            $table->index('empleado_id');
            $table->index('activo');
        });

        // ========== TABLAS DE INVENTARIO ==========
        
        // 7. Entradas de inventario
        Schema::create('entradas_inventario', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_entrada', 20)->unique();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->onDelete('set null');
            $table->datetime('fecha');
            $table->string('tipo_movimiento', 50)->default('compra');
            $table->decimal('total', 10, 2);
            $table->string('numero_factura', 50)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('creado_por')->constrained('usuarios')->onDelete('restrict');
            $table->timestamps();
            
            $table->index('codigo_entrada');
            $table->index('fecha');
            $table->index('proveedor_id');
        });

        // 8. Detalle de entradas
        Schema::create('detalle_entradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrada_id')->constrained('entradas_inventario')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->timestamps();
            
            $table->index(['entrada_id', 'producto_id']);
        });

        // 9. Ajustes de inventario
        Schema::create('ajustes_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->string('tipo_ajuste', 20)->nullable();
            $table->integer('cantidad_anterior');
            $table->integer('diferencia');
            $table->text('observaciones')->nullable();
            $table->foreignId('realizado_por')->constrained('usuarios')->onDelete('restrict');
            $table->timestamps();
            
            $table->index('producto_id');
            $table->index('tipo_ajuste');
            $table->index('created_at');
        });

        // ========== TABLAS DE EQUIPOS Y REPARACIONES ==========
        
        // 10. Equipos
        Schema::create('equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('tipo', 50);
            $table->string('marca', 50);
            $table->string('modelo', 50);
            $table->string('imei', 20)->nullable();
            $table->text('caracteristicas')->nullable();
            $table->string('altavoz', 150)->nullable();
            $table->string('microfono', 150)->nullable();
            $table->string('zocalo', 150)->nullable();
            $table->string('camara', 150)->nullable();
            $table->string('pantalla', 150)->nullable();
            $table->timestamps();
            
            $table->index('cliente_id');
            $table->index(['tipo', 'marca', 'modelo']);
            $table->index('imei');
        });

        // 11. Reparaciones
        Schema::create('reparaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_ticket', 20)->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('restrict');
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->onDelete('set null');
            $table->foreignId('empleado_id')->nullable()->constrained('empleados')->onDelete('set null');
            $table->string('estado', 20)->default('recibido');
            $table->text('problema_reportado');
            $table->text('diagnostico')->nullable();
            $table->text('solucion')->nullable();
            $table->text('observaciones')->nullable();
            $table->decimal('costo_estimado', 10, 2)->nullable();
            $table->decimal('costo_final', 10, 2)->nullable();
            $table->foreignId('creado_por')->constrained('usuarios')->onDelete('restrict');
            $table->datetime('fecha_ingreso')->default(now());
            $table->datetime('fecha_entrega')->nullable();
            $table->timestamps();
            
            $table->index('codigo_ticket');
            $table->index('cliente_id');
            $table->index('empleado_id');
            $table->index('estado');
            $table->index('fecha_ingreso');
        });

        // 12. Componentes de reparación
        Schema::create('componentes_reparacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reparacion_id')->constrained('reparaciones')->onDelete('cascade');
            $table->string('nombre_componente', 100);
            $table->string('estado', 100);
            $table->text('accion_realizada')->nullable();
            $table->timestamps();
            
            $table->index('reparacion_id');
        });

        // 13. Productos en reparación
        Schema::create('productos_reparacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reparacion_id')->constrained('reparaciones')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            $table->index(['reparacion_id', 'producto_id']);
        });

        // ========== TABLAS DE VENTAS ==========
        
        // 14. Ventas
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_venta', 20)->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('empleado_id')->constrained('empleados')->onDelete('restrict');
            $table->datetime('fecha')->default(now());
            $table->string('tipo_documento', 10)->nullable();
            $table->string('numero_boleta', 20)->nullable()->unique();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('metodo_pago', 50);
            $table->string('estado', 20)->default('completada');
            $table->foreignId('creado_por')->constrained('usuarios')->onDelete('restrict');
            $table->timestamps();
            
            $table->index('codigo_venta');
            $table->index('cliente_id');
            $table->index('empleado_id');
            $table->index('fecha');
            $table->index('estado');
        });

        // 15. Detalle de ventas
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->integer('garantia_dias')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
            
            $table->index(['venta_id', 'producto_id']);
        });

        // ========== TABLAS DE GARANTÍAS ==========
        
        // 16. Garantías (corregido formato de onDelete)
        Schema::create('garantias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_garantia', 20)->unique();
            $table->string('tipo_garantia', 20);
            $table->foreignId('reparacion_id')->nullable()->constrained('reparaciones')->onDelete('set null');
            $table->foreignId('producto_id')->nullable()->constrained('productos')->onDelete('set null');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('descripcion')->nullable();
            $table->text('condiciones')->nullable();
            $table->string('estado', 20)->default('vigente');
            $table->timestamps();
            
            $table->index('codigo_garantia');
            $table->index('estado');
            $table->index(['fecha_inicio', 'fecha_fin']);
        });

        // ========== TABLAS DE CONFIGURACIÓN Y AUDITORÍA ==========
        
        // 17. Configuraciones
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100)->unique();
            $table->text('valor');
            $table->string('tipo', 50)->default('string');
            $table->text('descripcion')->nullable();
            $table->timestamps();
            
            $table->index('clave');
        });

        // 18. Auditoría
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('operacion', 50);
            $table->string('tabla', 100);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ruta')->nullable();
            $table->string('controlador')->nullable();
            $table->timestamps();
            
            $table->index('usuario_id');
            $table->index('operacion');
            $table->index('tabla');
            $table->index('created_at');
        });

        // 19. Actividad de usuarios
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('accion', 100);
            $table->string('modulo', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->json('datos_adicionales')->nullable();
            $table->timestamps();
            
            $table->index(['usuario_id', 'created_at']);
            $table->index('modulo');
            $table->index('accion');
        });

        // 20. Logs de seguridad
        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 50);
            $table->text('descripcion');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('severity', 20)->default('info');
            $table->json('datos_adicionales')->nullable();
            $table->timestamps();
            
            $table->index(['tipo', 'created_at']);
            $table->index('usuario_id');
            $table->index('severity');
        });
            // 21. Movimientos de inventario
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('restrict');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->onDelete('set null');
            $table->foreignId('entrada_inventario_id')->nullable()->constrained('entradas_inventario')->onDelete('set null');
            $table->foreignId('reparacion_id')->nullable()->constrained('reparaciones')->onDelete('set null');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste', 'transferencia']);
            $table->integer('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->string('motivo', 100);
            $table->text('observaciones')->nullable();
            $table->string('documento_referencia', 50)->nullable();
            $table->decimal('costo_unitario', 10, 2)->nullable();
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->string('lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('ubicacion_origen', 100)->nullable();
            $table->string('ubicacion_destino', 100)->nullable();
            $table->string('referencia_tipo', 50)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->datetime('fecha_movimiento')->default(now());
            $table->timestamps();
            
            $table->index(['producto_id', 'fecha_movimiento']);
            $table->index(['tipo', 'fecha_movimiento']);
            $table->index('usuario_id');
            $table->index(['referencia_tipo', 'referencia_id']);
        });

        // 22. Notificaciones
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_notificacion', 50);
            $table->foreignId('usuario_destino_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('titulo', 255);
            $table->text('mensaje');
            $table->string('enlace_accion')->nullable();
            $table->string('entidad_relacionada', 100)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->boolean('leida')->default(false);
            $table->boolean('resuelta')->default(false);
            $table->datetime('fecha_creacion')->default(now());
            $table->datetime('fecha_lectura')->nullable();
            $table->datetime('fecha_resolucion')->nullable();
            $table->tinyInteger('prioridad')->default(2); // 1=Baja, 2=Normal, 3=Alta, 4=Crítica, 5=Urgente
            $table->timestamps();
            
            $table->index(['usuario_destino_id', 'leida']);
            $table->index('tipo_notificacion');
            $table->index(['prioridad', 'fecha_creacion']);
            $table->index(['entidad_relacionada', 'entidad_id']);
        });

        // 23. Historial de contraseñas
        Schema::create('password_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->string('password_hash');
            $table->timestamps();
            
            $table->index(['usuario_id', 'created_at']);
        });

        // 24. Configuraciones del sistema (Settings)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 255)->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['string', 'integer', 'boolean', 'json', 'float'])->default('string');
            $table->string('category', 100)->default('general');
            $table->boolean('is_public')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('category');
            $table->index('is_public');
            $table->index(['category', 'is_public']);
        });

    }


    public function down(): void
    {
        // Eliminar en orden inverso para evitar problemas de foreign keys
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('auditoria');
        Schema::dropIfExists('configuraciones');
        Schema::dropIfExists('garantias');
        Schema::dropIfExists('detalle_ventas');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('productos_reparacion');
        Schema::dropIfExists('componentes_reparacion');
        Schema::dropIfExists('reparaciones');
        Schema::dropIfExists('equipos');
        Schema::dropIfExists('ajustes_inventario');
        Schema::dropIfExists('detalle_entradas');
        Schema::dropIfExists('entradas_inventario');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('password_history');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('movimientos_inventario');

    }
};