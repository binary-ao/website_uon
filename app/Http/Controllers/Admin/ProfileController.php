<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('admin.profile', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users')->ignore($user->id)],
            'job_title' => ['nullable', 'string', 'max:120'],
            'current_password' => [Rule::requiredIf($request->input('email') !== $user->email), 'nullable', 'current_password'],
        ], [], ['current_password' => 'palavra-passe actual']);

        $user->fill(collect($data)->only(['name', 'email', 'job_title'])->all())->save();
        ActivityLogger::log('profile.updated', $user, 'Actualizou o perfil');

        return back()->with('success', 'Perfil actualizado.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ], [], ['current_password' => 'palavra-passe actual', 'password' => 'nova palavra-passe']);

        $user = $request->user();
        $user->forceFill([
            'password' => $request->input('password'),
            'password_changed_at' => now(),
            'remember_token' => Str::random(60),
        ])->save();

        // Termina as restantes sessões deste utilizador.
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())->delete();
        }
        ActivityLogger::log('profile.password_changed', $user, 'Alterou a palavra-passe');

        return back()->with('success', 'Palavra-passe alterada. As outras sessões foram terminadas.');
    }
}
