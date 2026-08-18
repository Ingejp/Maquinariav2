<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Set de permisos por módulo (no por URL+verbo como el sistema legacy, que
 * generaba bugs cuando el seeder se olvidaba de un caso — ver hallazgo 5.5
 * del análisis técnico). Cinco permisos simples, uno por módulo/acción.
 *
 * SUPER ADMIN los tiene todos asignados explícitamente (además del bypass
 * de Gate::before) solo para que la pantalla de Seguridad lo muestre
 * correctamente marcado, no como un rol sin permisos.
 */
class PermissionSeeder extends Seeder
{
    public const PERMISSIONS = [
        'catalogs.view',
        'catalogs.manage',
        'report.create',
        'dashboard.view',
        'security.manage',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $roleDefaults = [
            RoleName::SuperAdmin->value => self::PERMISSIONS,
            RoleName::Administrador->value => self::PERMISSIONS,
            RoleName::Supervisor->value => ['catalogs.view', 'report.create', 'dashboard.view'],
            RoleName::SupervisorEsticasa->value => ['catalogs.view', 'report.create', 'dashboard.view'],
        ];

        foreach ($roleDefaults as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }
    }
}
