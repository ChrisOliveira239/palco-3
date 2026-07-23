<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = DB::table('categories')->pluck('id')->all();
        $adminIds = DB::table('users')->where('use_is_admin', true)->pluck('id')->all();
        $organizadores = $this->organizadorPool();
        $statusOpcoes = ['RASCUNHO', 'PENDENTE', 'APROVADO', 'REJEITADO'];

        for ($i = 0; $i < 12; $i++) {
            $organizador = fake()->randomElement($organizadores);
            $status = fake()->boolean(60) ? 'PUBLICADO' : fake()->randomElement($statusOpcoes);

            $aprovadoPorId = null;
            $aprovadoEm = null;

            if (in_array($status, ['APROVADO', 'PUBLICADO'], true)) {
                $aprovadoPorId = fake()->randomElement($adminIds);
                $aprovadoEm = now()->subDays(rand(1, 60));
            }

            DB::table('events')->insert([
                'eve_titulo' => fake()->sentence(4),
                'eve_descricao' => fake()->paragraph(4),
                'category_id' => fake()->randomElement($categoryIds),
                'organizador_type' => $organizador['type'],
                'organizador_id' => $organizador['id'],
                'eve_status' => $status,
                'eve_gratuito' => fake()->boolean(30),
                'eve_cartaz_url' => fake()->imageUrl(600, 800, 'events'),
                'eve_links_externos' => fake()->boolean(70) ? json_encode(['instagram' => fake()->url()]) : null,
                'aprovado_por_id' => $aprovadoPorId,
                'eve_aprovado_em' => $aprovadoEm,
                'eve_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function organizadorPool(): array
    {
        $pool = [];

        foreach (DB::table('users')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\User', 'id' => $id];
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
