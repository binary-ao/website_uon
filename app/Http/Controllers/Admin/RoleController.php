<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Matriz de permissões por função (apenas superadministrador — roles.manage). */
class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get()->sortBy(fn ($r) => array_search($r->name, array_keys(User::ROLE_LABELS)));

        return view('admin.roles.index', ['roles' => $roles]);
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->name === User::ROLE_SUPERADMIN, 403, 'As permissões do superadministrador não são editáveis.');
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:'.implode(',', Permissions::all())],
        ]);
        // Permissões de gestão de utilizadores/funções ficam reservadas ao superadministrador.
        $permissions = array_values(array_diff($data['permissions'] ?? [], Permissions::SUPERADMIN_ONLY));
        $role->syncPermissions($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        ActivityLogger::log('roles.updated', $role, 'Alterou as permissões da função '.(User::ROLE_LABELS[$role->name] ?? $role->name), ['permissions' => $permissions]);

        return back()->with('success', 'Permissões actualizadas.');
    }
}
