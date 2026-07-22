<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            'Guitarrista', 'Baterista', 'Vocalista', 'Iluminador', 'Fotógrafo',
            'Videomaker', 'Ator', 'Bailarino', 'Técnico de Som', 'Figurinista',
        ];

        foreach ($skills as $nome) {
            DB::table('skills')->insert([
                'ski_nome' => $nome,
                'ski_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
