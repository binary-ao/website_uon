<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Recuperação de palavra-passe com tokens de uso único (password broker do Laravel).
 * A resposta ao pedido é sempre a mesma, para não revelar se um email existe.
 * O envio requer um serviço de email configurado no .env (MAIL_*).
 */
class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'max:190']]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', 'Se o endereço indicado corresponder a uma conta activa, receberá um email com instruções para definir uma nova palavra-passe.');
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => (string) $request->query('email')]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'password_changed_at' => now(),
                    'remember_token' => Str::random(60),
                ])->save();
                event(new PasswordReset($user));
                ActivityLogger::log('auth.password_reset', $user, 'Redefiniu a palavra-passe através de email');
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', 'Palavra-passe redefinida. Já pode iniciar sessão.')
            : back()->withInput($request->only('email'))->withErrors(['email' => 'A ligação de recuperação é inválida ou expirou. Peça uma nova.']);
    }
}
