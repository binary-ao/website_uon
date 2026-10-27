<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Cria o primeiro superadministrador APENAS se as credenciais estiverem
 * definidas no ambiente (UON_ADMIN_EMAIL / UON_ADMIN_PASSWORD).
 * Não existem contas nem palavras-passe por omissão no código.
 */
class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        $cfg = config('uon.initial_admin');
        if (empty($cfg['email']) || empty($cfg['password'])) {
            $this->command?->warn('Nenhum administrador criado. Use: php artisan uon:create-admin');

            return;
        }

        $validator = Validator::make($cfg, [
            'email' => ['required', 'email'],
            'password' => ['required', Password::defaults()],
        ]);
        if ($validator->fails()) {
            $this->command?->error('UON_ADMIN_* inválido: '.implode(' ', $validator->errors()->all()));

            return;
        }

        if (User::where('email', $cfg['email'])->exists()) {
            $this->command?->info('O utilizador '.$cfg['email'].' já existe — nada a fazer.');

            return;
        }

        $user = new User(['name' => $cfg['name'] ?: 'Superadministrador', 'email' => $cfg['email'], 'password' => $cfg['password']]);
        $user->is_active = true;
        $user->password_changed_at = now();
        $user->save();
        $user->assignRole(User::ROLE_SUPERADMIN);

        $this->command?->info('Superadministrador criado: '.$cfg['email'].'. Remova UON_ADMIN_PASSWORD do .env.');
    }
}
