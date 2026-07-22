<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventReviewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventIds = DB::table('events')
            ->join('event_sessions', 'event_sessions.event_id', '=', 'events.id')
            ->where('event_sessions.evs_data_inicio', '<', now())
            ->distinct()
            ->pluck('events.id')
            ->all();

        $userIds = DB::table('users')->pluck('id')->all();

        if (empty($eventIds)) {
            return;
        }

        $usados = [];
        $criados = 0;
        $tentativas = 0;

        while ($criados < 20 && $tentativas < 200) {
            $tentativas++;

            $eventId = fake()->randomElement($eventIds);
            $userId = fake()->randomElement($userIds);
            $chave = $eventId.'|'.$userId;

            if (isset($usados[$chave])) {
                continue;
            }

            $usados[$chave] = true;
            $criados++;

            DB::table('event_reviews')->insert([
                'event_id' => $eventId,
                'user_id' => $userId,
                'evr_nota' => rand(1, 5),
                'evr_comentario' => fake()->boolean(70) ? fake()->sentence(12) : null,
                'evr_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
