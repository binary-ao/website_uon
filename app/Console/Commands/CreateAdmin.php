<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * Criação segura do primeiro superadministrador.
 * A palavra-passe é pedida de forma oculta e nunca fica em ficheiros.
 */
class CreateAdmin extends Command
{
    protected $signature = 'uon:create-admin {--name= : Nome} {--email= : Email} {--role=superadmin : superadmin|admin|editor|author}';

    protected $description = 'Cria uma conta de acesso ao CMS (por omissão, superadministrador)';

    public function handle(): int
    {
        if (! Role::where('name', $this->option('role'))->exists()) {
            $this->error('Função inexistente. Execute primeiro: php artisan db:seed');

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Nome');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->secret('Palavra-passe (mín. 10 caracteres, maiúsculas, minúsculas e números)');
        $confirm = $this->secret('Confirme a palavra-passe');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirm],
            [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:190', 'unique:users,email'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]
        );
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $e) {
                $this->error($e);
            }

            return self::FAILURE;
        }

        $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->is_active = true;
        $user->password_changed_at = now();
        $user->save();
        $user->assignRole($this->option('role'));

        $this->info("Conta criada: {$email} ({$this->option('role')}). Aceda a /admin/login");

        return self::SUCCESS;
    }
}
