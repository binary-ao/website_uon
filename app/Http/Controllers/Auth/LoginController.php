<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $key = 'login:'.Str::lower($credentials['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);
            ActivityLogger::log('auth.lockout', null, 'Bloqueio temporário de autenticação para '.$credentials['email']);
            throw ValidationException::withMessages([
                'email' => "Demasiadas tentativas de autenticação. Tente novamente dentro de {$seconds} segundos.",
            ]);
        }

        // Ataques de força bruta também por IP (várias contas).
        $ipKey = 'login-ip:'.$request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 20)) {
            throw ValidationException::withMessages(['email' => 'Demasiadas tentativas a partir deste endereço. Aguarde alguns minutos.']);
        }

        if (! Auth::attempt([...$credentials, 'is_active' => true], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            RateLimiter::hit($ipKey, 600);
            ActivityLogger::log('auth.failed', null, 'Tentativa de autenticação falhada para '.$credentials['email']);
            throw ValidationException::withMessages(['email' => 'As credenciais indicadas não são válidas.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();
        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->saveQuietly();
        ActivityLogger::log('auth.login', $user, 'Iniciou sessão');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('auth.logout', $request->user(), 'Terminou sessão');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Sessão terminada com sucesso.');
    }
}
