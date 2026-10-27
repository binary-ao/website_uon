<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Degree;
use Illuminate\Database\Seeder;

/**
 * Taxonomias iniciais genéricas (editáveis no CMS). Não são dados institucionais:
 * são categorias de organização editorial e os graus académicos do ensino superior.
 */
class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'news' => ['Institucional', 'Ensino', 'Investigação', 'Extensão'],
            'event' => ['Académico', 'Institucional', 'Científico', 'Cultural'],
            'document' => [
                'documentos-institucionais' => 'Documentos Institucionais',
                'regulamentos' => 'Regulamentos',
                'formularios' => 'Documentos e Formulários',
                'protocolos' => 'Protocolos',
            ],
            'gallery' => ['Institucional'],
        ];

        foreach ($categories as $type => $names) {
            $i = 0;
            foreach ($names as $slug => $name) {
                $slug = is_string($slug) ? $slug : \Illuminate\Support\Str::slug($name);
                Category::firstOrCreate(['type' => $type, 'slug' => $slug], ['name' => $name, 'sort_order' => $i++]);
            }
        }

        $degrees = [
            ['Licenciatura', 'Licenciaturas', 'licenciatura'],
            ['Mestrado', 'Mestrados', 'mestrado'],
            ['Doutoramento', 'Doutoramentos', 'doutoramento'],
            ['Pós-Graduação', 'Pós-Graduações', 'pos-graduacao'],
        ];
        foreach ($degrees as $i => [$name, $plural, $slug]) {
            Degree::firstOrCreate(['slug' => $slug], ['name' => $name, 'plural_name' => $plural, 'sort_order' => $i]);
        }
    }
}
