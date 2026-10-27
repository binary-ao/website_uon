<?php

namespace Tests;

use App\Enums\PublicationStatus;
use App\Models\User;
use App\Support\Settings;
use Database\Seeders\MenuSeeder;
use Database\Seeders\PageSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\TaxonomySeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    /** Estrutura base do website (funções, taxonomias, páginas e menus do template). */
    protected function seedStructure(): void
    {
        Settings::flush();
        $this->seed([RolesAndPermissionsSeeder::class, TaxonomySeeder::class, PageSeeder::class, MenuSeeder::class]);
        Storage::fake('uploads');
        Storage::fake('documents');
    }

    protected function userWithRole(string $role, array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $user->assignRole($role);

        return $user;
    }

    protected function published(): array
    {
        return ['status' => PublicationStatus::Published, 'published_at' => now()->subHour()];
    }
}
