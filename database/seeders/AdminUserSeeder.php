<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea el primer usuario Super Admin si aún no existe ninguno con ese username.
     *
     * No hay registro público (decisión de producto): este es el único punto de
     * entrada para tener un primer usuario con el que administrar el resto desde
     * el módulo de Seguridad (Fase 5). Sin hash fijo en el código (hallazgo 6.3):
     * usa ADMIN_USERNAME/ADMIN_PASSWORD del entorno, o genera una contraseña
     * aleatoria si no se definieron.
     */
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');

        if (User::where('username', $username)->exists()) {
            return;
        }

        $password = env('ADMIN_PASSWORD');
        $generated = $password === null;
        $password ??= Str::password(16);

        $user = User::create([
            'username' => $username,
            'password' => $password,
        ]);

        $user->assignRole(RoleName::SuperAdmin->value);

        if ($generated) {
            $this->command?->warn("Usuario admin creado: {$username}");
            $this->command?->warn("Contraseña generada: {$password}");
            $this->command?->warn('Guárdala ahora — no se vuelve a mostrar. Cámbiala después del primer login.');
        }
    }
}
