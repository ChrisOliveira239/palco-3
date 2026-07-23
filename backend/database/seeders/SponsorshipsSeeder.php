<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SponsorshipsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->all();
        $alvos = $this->alvoPool();

        $tiposApoio = [
            'DINHEIRO', 'EQUIPAMENTO', 'FIGURINO', 'ALIMENTACAO',
            'TRANSPORTE', 'HOSPEDAGEM', 'FOTOGRAFIA', 'FILMAGEM', 'ILUMINACAO', 'SOM', 'OUTRO',
        ];
        $statusOpcoes = ['PROPOSTO', 'ACEITO', 'RECUSADO', 'CONCLUIDO'];

        for ($i = 0; $i < 15; $i++) {
            $alvo = fake()->randomElement($alvos);
            $tipoApoio = fake()->randomElement($tiposApoio);

            DB::table('sponsorships')->insert([
                'sponsor_type' => 'App\\Models\\User',
                'sponsor_id' => fake()->randomElement($userIds),
                'alvo_type' => $alvo['type'],
                'alvo_id' => $alvo['id'],
                'spo_tipo_apoio' => $tipoApoio,
                'spo_valor' => $tipoApoio === 'DINHEIRO' ? fake()->randomFloat(2, 100, 5000) : null,
                'spo_descricao' => $tipoApoio === 'DINHEIRO' ? null : fake()->sentence(10),
                'spo_status' => fake()->randomElement($statusOpcoes),
                'spo_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
