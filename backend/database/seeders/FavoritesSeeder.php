<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoritesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->all();
        $eventIds = DB::table('events')->pluck('id')->all();

        $usados = [];
        $criados = 0;
        $tentativas = 0;

        while ($criados < 30 && $tentativas < 200) {
            $tentativas++;

            $userId = fake()->randomElement($userIds);
            $eventId = fake()->randomElement($eventIds);
            $chave = $userId.'|'.$eventId;

            if (isset($usados[$chave])) {
                continue;
            }

            $usados[$chave] = true;
            $criados++;

            DB::table('favorites')->insert([
                'user_id' => $userId,
                'event_id' => $eventId,
            ]);
        }
    }
}
