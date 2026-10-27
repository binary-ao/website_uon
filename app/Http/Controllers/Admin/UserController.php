<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * Gestão de utilizadores (permissão users.manage).
 * Protecções contra escalada de privilégios:
 *  - só um superadministrador pode criar/editar superadministradores;
 *  - ninguém altera a própria função nem se desactiva a si próprio;
 *  - o último superadministrador activo não pode ser removido nem despromovido.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $items = User::query()->with('roles')
            ->when($request->filled('q'), function ($q) use ($request) {
                $like = '%'.addcslashes((string) $request->query('q'), '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($request->filled('role'), fn ($q) => $q->role($request->query('role')))
            ->when($request->query('state') === 'inactive', fn ($q) => $q->where('is_active', false))
            ->when($request->query('state') === 'active', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')->paginate(config('uon.pagination.admin'))->withQueryString();

        return view('admin.users.index', ['items' => $items, 'roles' => $this->assignableRoles()]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(), 'roles' => $this->assignableRoles(), 'currentRole' => User::ROLE_AUTHOR]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = DB::transaction(function () use ($data, $request) {
            $user = new User(['name' => $data['name'], 'email' => $data['email'], 'job_title' => $data['job_title'] ?? null, 'password' => $data['password']]);
            $user->is_active = $request->boolean('is_active');
            $user->password_changed_at = now();
            $user->save();
            $user->syncRoles([$data['role']]);

            return $user;
        });
        ActivityLogger::log('users.created', $user, 'Criou o utilizador '.$user->email.' ('.$data['role'].')');

        return redirect()->route('admin.users.index')->with('success', 'Utilizador criado.');
    }

    public function edit(User $user)
    {
        $this->guardSuperAdminTarget($user);

        return view('admin.users.form', ['user' => $user, 'roles' => $this->assignableRoles(), 'currentRole' => $user->roles->first()?->name]);
    }

    public function update(Request $request, User $user)
    {
        $this->guardSuperAdminTarget($user);
        $data = $this->validated($request, $user);
        $isSelf = $user->is($request->user());
        $newRole = $isSelf ? $user->roles->first()?->name : $data['role'];
        $active = $isSelf ? true : $request->boolean('is_active');

        if ($user->isSuperAdmin() && ($newRole !== User::ROLE_SUPERADMIN || ! $active) && $this->activeSuperAdmins() <= 1) {
            return back()->withInput()->with('error', 'Tem de existir pelo menos um superadministrador activo.');
        }

        DB::transaction(function () use ($user, $data, $newRole, $active) {
            $user->fill(['name' => $data['name'], 'email' => $data['email'], 'job_title' => $data['job_title'] ?? null]);
            if (! empty($data['password'])) {
                $user->password = $data['password'];
                $user->password_changed_at = now();
                $user->setRememberToken(Str::random(60));
            }
            $user->is_active = $active;
            $user->save();
            if ($newRole) {
                $user->syncRoles([$newRole]);
            }
            if (! $active) {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }
        });
        ActivityLogger::log('users.updated', $user, 'Editou o utilizador '.$user->email, ['role' => $newRole, 'active' => $active]);

        return redirect()->route('admin.users.edit', $user)->with('success', 'Utilizador guardado.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->guardSuperAdminTarget($user);
        if ($user->is($request->user())) {
            return back()->with('error', 'Não pode eliminar a sua própria conta.');
        }
        if ($user->isSuperAdmin() && $this->activeSuperAdmins() <= 1) {
            return back()->with('error', 'Não é possível eliminar o último superadministrador.');
        }
        // Os conteúdos do utilizador mantêm-se (author_id passa a nulo).
        $email = $user->email;
        DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        $user->syncRoles([]);
        $user->delete();
        ActivityLogger::log('users.deleted', null, 'Eliminou o utilizador '.$email);

        return redirect()->route('admin.users.index')->with('success', 'Utilizador eliminado. Os conteúdos criados foram preservados.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users')->ignore($user?->id)],
            'job_title' => ['nullable', 'string', 'max:120'],
            'role' => [$user && $user->is($request->user()) ? 'nullable' : 'required', Rule::in(array_keys($this->assignableRoles()))],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ], [], ['role' => 'função', 'password' => 'palavra-passe']);
    }

    /** Funções que o utilizador autenticado pode atribuir (nunca acima das suas). */
    private function assignableRoles(): array
    {
        $roles = Role::orderBy('id')->pluck('name')->all();
        if (! auth()->user()->isSuperAdmin()) {
            $roles = array_diff($roles, [User::ROLE_SUPERADMIN]);
        }

        return collect($roles)->mapWithKeys(fn ($r) => [$r => User::ROLE_LABELS[$r] ?? $r])->all();
    }

    private function guardSuperAdminTarget(User $target): void
    {
        abort_if($target->isSuperAdmin() && ! auth()->user()->isSuperAdmin(), 403, 'Apenas um superadministrador pode gerir outro superadministrador.');
    }

    private function activeSuperAdmins(): int
    {
        return User::role(User::ROLE_SUPERADMIN)->where('is_active', true)->count();
    }
}
