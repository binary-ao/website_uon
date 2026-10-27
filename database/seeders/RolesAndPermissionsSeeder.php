<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $new = [];
        foreach (Permissions::all() as $name) {
            if (Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web'])->wasRecentlyCreated) {
                $new[] = $name;
            }
        }

        foreach (Permissions::defaultsForRoles() as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            // Só atribui as permissões por omissão a funções recém-criadas,
            // para não reverter ajustes feitos no CMS.
            if ($role->wasRecentlyCreated || $roleName === User::ROLE_SUPERADMIN) {
                $role->syncPermissions($permissions);
            } elseif ($add = array_values(array_intersect($new, $permissions))) {
                // Permissões introduzidas numa actualização: atribuídas às funções que as têm por omissão.
                $role->givePermissionTo($add);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
