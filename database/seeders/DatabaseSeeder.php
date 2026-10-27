<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Dados estruturais (seguros para re-executar): funções e permissões,
     * taxonomias, árvore de páginas do template e menus do Header/Footer.
     * Conteúdo demonstrativo é opcional: php artisan db:seed --class=DemoContentSeeder
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            TaxonomySeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
            InitialAdminSeeder::class,
        ]);
    }
}
