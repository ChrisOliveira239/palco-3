<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->all();
        $alvos = $this->alvoPool();
        $motivos = ['Conteúdo ofensivo', 'Spam', 'Informação falsa', 'Golpe/fraude', 'Assédio'];
        $statusOpcoes = ['PENDENTE', 'ANALISADO', 'RESOLVIDO'];

        for ($i = 0; $i < 8; $i++) {
            $alvo = fake()->randomElement($alvos);

            DB::table('reports')->insert([
                'denunciante_id' => fake()->randomElement($userIds),
                'alvo_type' => $alvo['type'],
                'alvo_id' => $alvo['id'],
                'rep_motivo' => fake()->randomElement($motivos),
                'rep_status' => fake()->randomElement($statusOpcoes),
                'rep_active' => true,
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

        foreach (DB::table('feed_posts')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\FeedPost', 'id' => $id];
        }

        return $pool;
    }
}
