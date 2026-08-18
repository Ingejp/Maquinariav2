<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Usuario fijo para pruebas manuales/QA (navegador, verificación de
     * features) — credenciales conocidas y estables, a diferencia de
     * AdminUserSeeder que genera una contraseña aleatoria de un solo uso.
     *
     * Nunca corre fuera de local/testing: no queremos esta cuenta en un
     * entorno con datos reales de producción.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->warn('TestUserSeeder solo corre en local/testing — omitido.');

            return;
        }

        $username = env('TEST_USERNAME', 'qa_test');
        $password = env('TEST_PASSWORD', 'QaTest#2026!');

        $user = User::updateOrCreate(
            ['username' => $username],
            ['password' => $password],
        );

        $user->syncRoles([RoleName::SuperAdmin->value]);

        $this->command?->info("Usuario de prueba listo: {$username} / {$password}");
    }
}
