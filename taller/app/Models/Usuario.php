<?php
// app/Models/Usuario.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $table = 'usuarios';

    protected $fillable = [
        'empleado_id',
        'username',
        'email',
        'password',
        'tipo_usuario',
        'activo',
        'email_verified_at',
        'ultimo_login',
        'intentos_fallidos',
        'bloqueado',
        'bloqueado_hasta',
        'force_password_change',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'bloqueado' => 'boolean',
        'force_password_change' => 'boolean',
        'email_verified_at' => 'datetime',
        'ultimo_login' => 'datetime',
        'bloqueado_hasta' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Variable temporal para manejo de historial de contraseñas (NO es campo de BD)
    protected $password_to_save;

    /**
     * RELACIONES
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    public function loginAttempts()
    {
        return $this->hasMany(LoginAttempt::class);
    }

    public function securityLogs()
    {
        return $this->hasMany(SecurityLog::class);
    }

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function userActivities()
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * SCOPES
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeBloqueados($query)
    {
        return $query->where('bloqueado', true);
    }

    public function scopeRequierenCambioPassword($query)
    {
        return $query->where('force_password_change', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_usuario', $tipo);
    }

    /**
     * MUTATORS
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            // Si ya está hasheado, no volver a hashear
            if (Hash::needsRehash($value)) {
                $hashedPassword = Hash::make($value);
                
                // Guardar temporalmente para historial (solo si PasswordHistory existe)
                if (class_exists(PasswordHistory::class)) {
                    $this->password_to_save = $value;
                }
                
                $this->attributes['password'] = $hashedPassword;
            } else {
                $this->attributes['password'] = $value;
            }
        }
    }

    /**
     * ACCESSORS
     */
    public function getNombreCompletoAttribute()
    {
        if ($this->empleado) {
            return $this->empleado->nombre_completo;
        }
        return $this->username;
    }

    public function getNombresAttribute()
    {
        return $this->empleado?->nombres ?? '';
    }

    public function getApellidosAttribute()
    {
        return $this->empleado?->apellidos ?? '';
    }

    public function getTelefonoAttribute()
    {
        return $this->empleado?->telefono ?? '';
    }

    public function getEspecialidadAttribute()
    {
        return $this->empleado?->especialidad ?? '';
    }

    public function getNameAttribute()
    {
        return $this->nombre_completo;
    }

    /**
     * MÉTODOS DE INSTANCIA
     */
    public function estaActivo(): bool
    {
        return $this->activo && !$this->trashed();
    }

    public function estaBloqueado(): bool
    {
        if (!$this->bloqueado) {
            return false;
        }

        // Verificar si el bloqueo temporal ha expirado
        if ($this->bloqueado_hasta && $this->bloqueado_hasta->isPast()) {
            $this->update([
                'bloqueado' => false,
                'bloqueado_hasta' => null,
                'intentos_fallidos' => 0
            ]);
            return false;
        }

        return true;
    }

    public function incrementarIntentosFallidos(): void
    {
        $this->increment('intentos_fallidos');

        // Bloquear después de 5 intentos fallidos
        if ($this->intentos_fallidos >= 5) {
            $this->update([
                'bloqueado' => true,
                'bloqueado_hasta' => now()->addMinutes(15)
            ]);
        }
    }

    public function resetearIntentosFallidos(): void
    {
        $this->update([
            'intentos_fallidos' => 0,
            'bloqueado' => false,
            'bloqueado_hasta' => null
        ]);
    }

    public function actualizarUltimoLogin(): void
    {
        $this->update(['ultimo_login' => now()]);
    }

    public function requiresCambioPassword(): bool
    {
        return $this->force_password_change;
    }

    /**
     * MÉTODOS ESTÁTICOS
     */
    public static function crearUsuario(array $data): self
    {
        // Crear empleado si se proporciona
        $empleadoId = null;
        if (isset($data['empleado_data'])) {
            $empleado = Empleado::create($data['empleado_data']);
            $empleadoId = $empleado->id;
        }

        // Crear usuario
        $usuario = self::create([
            'empleado_id' => $empleadoId ?? $data['empleado_id'] ?? null,
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'], // Se procesará por el mutator
            'tipo_usuario' => $data['tipo_usuario'] ?? 'empleado',
            'activo' => $data['activo'] ?? true,
            'force_password_change' => $data['force_password_change'] ?? true,
        ]);

        // Asignar rol si se especifica
        if (isset($data['rol'])) {
            $usuario->assignRole($data['rol']);
        }

        return $usuario;
    }

    public static function findByLogin(string $login): ?self
    {
        return self::where('email', $login)
                   ->orWhere('username', $login)
                   ->first();
    }

    /**
     * BOOT METHOD
     */
    protected static function boot()
    {
        parent::boot();

        // Registrar contraseña en historial al crear usuario (si la clase existe)
        static::created(function ($usuario) {
            if (isset($usuario->password_to_save) && class_exists(PasswordHistory::class)) {
                PasswordHistory::registrarPassword($usuario->id, $usuario->password_to_save);
                unset($usuario->password_to_save);
            }
        });

        // Registrar cambio de contraseña en historial (si la clase existe)
        static::updated(function ($usuario) {
            if (isset($usuario->password_to_save) && class_exists(PasswordHistory::class)) {
                PasswordHistory::registrarPassword($usuario->id, $usuario->password_to_save);
                unset($usuario->password_to_save);
            }
        });
    }
}