<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcceptedSupportTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $alvos = collect($this->alvoPool())->random(10);

        $tiposApoio = [
            'dinheiro', 'equipamento', 'figurino', 'alimentacao',
            'transporte', 'hospedagem', 'fotografia', 'filmagem', 'iluminacao', 'som', 'outro',
        ];

        foreach ($alvos as $alvo) {
            $tipos = fake()->randomElements($tiposApoio, rand(2, 4));

            foreach ($tipos as $tipo) {
                DB::table('accepted_support_types')->insert([
                    'alvo_type' => $alvo['type'],
                    'alvo_id' => $alvo['id'],
                    'ast_tipo_apoio' => $tipo,
                    'created_at' => now(),
                ]);
            }
        }
    }

    private function alvoPool(): array
    {
        $pool = [];

        foreach (DB::table('events')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\Event', 'id' => $id];
        }

        foreach (DB::table('artist_profiles')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\ArtistProfile', 'id' => $id];
        }

        foreach (DB::table('groups')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\Group', 'id' => $id];
        }

        return $pool;
    }
}
