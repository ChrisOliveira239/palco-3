<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpportunitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $criadores = $this->criadorPool();
        $skillIds = DB::table('skills')->pluck('id')->all();
        $cidades = ['São Paulo', 'Rio de Janeiro', 'Belo Horizonte'];

        for ($i = 0; $i < 10; $i++) {
            $criador = fake()->randomElement($criadores);

            DB::table('opportunities')->insert([
                'criador_type' => $criador['type'],
                'criador_id' => $criador['id'],
                'opp_titulo' => 'Procura-se '.fake()->jobTitle(),
                'opp_descricao' => fake()->paragraph(3),
                'skill_id' => fake()->boolean(70) ? fake()->randomElement($skillIds) : null,
                'opp_cidade' => fake()->randomElement($cidades),
                'opp_status' => fake()->randomElement(['aberta', 'aberta', 'aberta', 'fechada']),
                'opp_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function criadorPool(): array
    {
        $pool = [];

        foreach (DB::table('artist_profiles')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\ArtistProfile', 'id' => $id];
        }

        foreach (DB::table('groups')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\Group', 'id' => $id];
        }

        return $pool;
    }
}
