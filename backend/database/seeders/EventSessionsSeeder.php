<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSessionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventIds = DB::table('events')->pluck('id');
        $venueIds = DB::table('venues')->pluck('id')->all();

        foreach ($eventIds as $eventId) {
            $sessoes = rand(1, 2);

            for ($i = 0; $i < $sessoes; $i++) {
                $inicio = fake()->boolean(50)
                    ? now()->subDays(rand(1, 90))
                    : now()->addDays(rand(1, 90));

                DB::table('event_sessions')->insert([
                    'event_id' => $eventId,
                    'venue_id' => fake()->randomElement($venueIds),
                    'evs_data_inicio' => $inicio,
                    'evs_data_fim' => (clone $inicio)->addHours(rand(2, 5)),
                    'evs_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
