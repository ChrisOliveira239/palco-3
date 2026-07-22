<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id');
        $tipos = ['novo_seguidor', 'evento_aprovado', 'patrocinio_recebido', 'candidatura_recebida', 'ingresso_comprado'];

        foreach ($userIds as $userId) {
            $quantidade = rand(2, 3);

            for ($i = 0; $i < $quantidade; $i++) {
                DB::table('notifications')->insert([
                    'user_id' => $userId,
                    'not_tipo' => fake()->randomElement($tipos),
                    'not_conteudo' => json_encode(['mensagem' => fake()->sentence(8)]),
                    'not_lida' => fake()->boolean(50),
                    'not_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
