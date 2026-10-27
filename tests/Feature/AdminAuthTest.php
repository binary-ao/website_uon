<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStructure();
    }

    public function test_admin_routes_require_authentication(): void
    {
        foreach (['/admin', '/admin/pages', '/admin/news/create', '/admin/configuracoes', '/admin/users', '/admin/media'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
        $this->post('/admin/pages', ['title' => 'x'])->assertRedirect(route('login'));
    }

    public function test_login_and_logout(): void
    {
        $user = $this->userWithRole('editor', ['password' => Hash::make('Segura12345')]);
        $this->get('/admin/login')->assertOk()->assertSee('Iniciar sessão');

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'errada'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => $user->email, 'password' => 'Segura12345'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'auth.login', 'user_id' => $user->id]);

        $this->get('/admin')->assertOk()->assertSee('Painel');
        $this->post('/admin/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = $this->userWithRole('editor');
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'errada'.$i]);
        }
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Demasiadas tentativas', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login_and_is_logged_out(): void
    {
        $user = $this->userWithRole('editor');
        $user->forceFill(['is_active' => false])->save();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($user)->get('/admin')->assertRedirect(route('login'));
    }

    public function test_user_without_role_cannot_access_cms(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_password_reset_flow(): void
    {
        Notification::fake();
        $user = $this->userWithRole('author');

        $this->post('/admin/recuperar-palavra-passe', ['email' => 'desconhecido@example.org'])->assertSessionHas('status');
        $this->post('/admin/recuperar-palavra-passe', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPasswordNotification::class);

        $token = Password::createToken($user);
        $this->post('/admin/redefinir-palavra-passe', ['token' => $token, 'email' => $user->email, 'password' => 'fraca', 'password_confirmation' => 'fraca'])->assertSessionHasErrors('password');
        $this->post('/admin/redefinir-palavra-passe', ['token' => $token, 'email' => $user->email, 'password' => 'NovaSegura123', 'password_confirmation' => 'NovaSegura123'])->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('NovaSegura123', $user->fresh()->password));
    }

    public function test_change_own_password(): void
    {
        $user = $this->userWithRole('author');
        $this->actingAs($user)->put('/admin/perfil/palavra-passe', ['current_password' => 'errada', 'password' => 'NovaSegura123', 'password_confirmation' => 'NovaSegura123'])->assertSessionHasErrors('current_password');
        $this->actingAs($user)->put('/admin/perfil/palavra-passe', ['current_password' => 'password', 'password' => 'NovaSegura123', 'password_confirmation' => 'NovaSegura123'])->assertSessionHas('success');
        $this->assertTrue(Hash::check('NovaSegura123', $user->fresh()->password));
    }

    public function test_admin_responses_are_not_indexable(): void
    {
        $this->get('/admin/login')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_create_admin_command_requires_strong_password(): void
    {
        $this->artisan('uon:create-admin', ['--name' => 'Admin', '--email' => 'admin@example.org'])
            ->expectsQuestion('Palavra-passe (mín. 10 caracteres, maiúsculas, minúsculas e números)', 'fraca')
            ->expectsQuestion('Confirme a palavra-passe', 'fraca')
            ->assertFailed();
        $this->artisan('uon:create-admin', ['--name' => 'Admin', '--email' => 'admin@example.org'])
            ->expectsQuestion('Palavra-passe (mín. 10 caracteres, maiúsculas, minúsculas e números)', 'MuitoSegura123')
            ->expectsQuestion('Confirme a palavra-passe', 'MuitoSegura123')
            ->assertSuccessful();
        $this->assertTrue(User::where('email', 'admin@example.org')->first()->isSuperAdmin());
    }
}
