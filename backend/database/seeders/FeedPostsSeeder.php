<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedPostsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $autores = $this->autorPool();
        $tipos = ['ATUALIZACAO', 'FOTO', 'VIDEO', 'EVENTO'];

        foreach ($autores as $autor) {
            $quantidade = rand(2, 3);

            for ($i = 0; $i < $quantidade; $i++) {
                $tipo = fake()->randomElement($tipos);

                DB::table('feed_posts')->insert([
                    'autor_type' => $autor['type'],
                    'autor_id' => $autor['id'],
                    'fee_tipo' => $tipo,
                    'fee_conteudo' => fake()->sentence(15),
                    'fee_midia_url' => $tipo === 'ATUALIZACAO' ? null : fake()->imageUrl(800, 600, 'feed'),
                    'fee_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function autorPool(): array
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
