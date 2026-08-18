<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class SyncLegacyRoles extends Command
{
    protected $signature = 'legacy:sync-roles';

    protected $description = 'Asigna a cada usuario el rol de Spatie correspondiente a su role_id legacy (tabla role/users.role_id del sistema original)';

    public function handle(): int
    {
        if (! Schema::hasTable('role') || ! Schema::hasColumn('users', 'role_id')) {
            $this->info('No hay esquema legacy (tabla `role` o `users.role_id`) en esta base de datos — nada que sincronizar.');

            return self::SUCCESS;
        }

        $legacyRoles = DB::table('role')->pluck('description', 'id');

        if ($legacyRoles->isEmpty()) {
            $this->info('La tabla `role` legacy está vacía — nada que sincronizar.');

            return self::SUCCESS;
        }

        foreach ($legacyRoles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        $synced = 0;

        User::query()->select('id', 'role_id')->chunkById(200, function ($users) use ($legacyRoles, &$synced) {
            foreach ($users as $user) {
                $roleName = $legacyRoles->get($user->role_id);

                if ($roleName === null) {
                    $this->warn("Usuario #{$user->id}: role_id={$user->role_id} no existe en la tabla `role` legacy, se omite.");

                    continue;
                }

                $user->syncRoles([$roleName]);
                $synced++;
            }
        });

        $this->info("Roles sincronizados para {$synced} usuario(s).");

        return self::SUCCESS;
    }
}
