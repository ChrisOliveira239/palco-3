<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sessionIds = DB::table('event_sessions')
            ->join('events', 'events.id', '=', 'event_sessions.event_id')
            ->where('events.eve_gratuito', false)
            ->pluck('event_sessions.id');

        $nomes = ['Meia-entrada', 'Inteira', 'VIP'];

        foreach ($sessionIds as $sessionId) {
            $tipos = fake()->randomElements($nomes, rand(1, 3));

            foreach ($tipos as $nome) {
                DB::table('ticket_types')->insert([
                    'event_session_id' => $sessionId,
                    'tit_nome' => $nome,
                    'tit_preco' => fake()->randomFloat(2, 20, 250),
                    'tit_quantidade_total' => rand(20, 100),
                    'tit_quantidade_vendida' => 0,
                    'tit_venda_inicio' => now()->subDays(rand(10, 30)),
                    'tit_venda_fim' => now()->addDays(rand(1, 30)),
                    'tit_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
