<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class TestPassword extends Command
{
    protected $signature = 'test:password';
    protected $description = 'Test password functionality';

    public function handle()
    {
        $this->info('=== TESTING PASSWORD FUNCTIONALITY ===');
        
        // Obtener usuario existente
        $user = Usuario::first();
        $this->info('Usuario: ' . $user->username);
        $this->info('Email: ' . $user->email);
        
        // Verificar accessors
        $this->info('Password hash: ' . substr($user->password_hash, 0, 20) . '...');
        $this->info('Password accessor: ' . substr($user->password, 0, 20) . '...');
        
        // Probar contraseñas comunes
        $passwords = ['password', 'tecnico', '123456', 'password123', 'admin'];
        
        foreach ($passwords as $pwd) {
            $check = Hash::check($pwd, $user->password);
            $this->info("Testing '$pwd': " . ($check ? 'TRUE ✅' : 'FALSE ❌'));
        }
        
        // Crear usuario nuevo
        $this->info("\n=== CREATING NEW USER ===");
        
        try {
            $newUser = new Usuario();
            $newUser->username = 'test_' . time();
            $newUser->email = 'test_' . time() . '@test.com';
            $newUser->password = 'test123';
            $newUser->tipo_usuario = 'Empleado';
            $newUser->activo = true;
            $newUser->save();
            
            $this->info('New user created: ' . $newUser->username);
            $this->info('New user password hash: ' . substr($newUser->password_hash, 0, 20) . '...');
            
            // Verificar nuevo usuario
            $check = Hash::check('test123', $newUser->password);
            $this->info("Testing 'test123' on new user: " . ($check ? 'TRUE ✅' : 'FALSE ❌'));
            
            // Verificar mutator
            $this->info('Mutator working: ' . ($newUser->password_hash ? 'YES ✅' : 'NO ❌'));
            
        } catch (\Exception $e) {
            $this->error('Error creating user: ' . $e->getMessage());
        }
    }
}