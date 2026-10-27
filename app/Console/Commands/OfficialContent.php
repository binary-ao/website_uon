<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\MixedContentSeeder;
use Database\Seeders\OfficialContentSeeder;
use Illuminate\Console\Command;

/**
 * Carrega o conteúdo institucional recolhido do website oficial (uon.edu.ao).
 * Remove primeiro o conteúdo fictício de demonstração, para não misturar dados.
 * Com --with-examples, completa com exemplos o que o website oficial não publica.
 */
class OfficialContent extends Command
{
    protected $signature = 'uon:official-content
        {--with-examples : Manter conteúdo de exemplo onde o website oficial não tem informação}
        {--force : Não pedir confirmação}';

    protected $description = 'Substitui o conteúdo fictício pelo conteúdo institucional recolhido de uon.edu.ao';

    public function handle(): int
    {
        $hasDemo = SiteSetting::where('key', DemoContentSeeder::STATE_KEY)->exists() || \App\Models\News::where('is_demo', true)->exists();
        if ($hasDemo) {
            if (! $this->option('force') && ! $this->confirm('O conteúdo de exemplo actual será removido e recriado. Continuar?', true)) {
                return self::SUCCESS;
            }
            $this->call('uon:demo-content', ['--remove' => true, '--force' => true]);
        }
        $this->call('db:seed', ['--class' => $this->option('with-examples') ? MixedContentSeeder::class : OfficialContentSeeder::class, '--force' => true]);

        return self::SUCCESS;
    }
}
