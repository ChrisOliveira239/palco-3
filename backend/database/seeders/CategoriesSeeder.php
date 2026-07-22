<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Música', 'Teatro', 'Dança', 'Artes Visuais',
            'Literatura', 'Cinema', 'Gastronomia Cultural', 'Circo',
        ];

        foreach ($categorias as $nome) {
            DB::table('categories')->insert([
                'cat_nome' => $nome,
                'cat_slug' => Str::slug($nome),
                'cat_icone' => null,
                'cat_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
