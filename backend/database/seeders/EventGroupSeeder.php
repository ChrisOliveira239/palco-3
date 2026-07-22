<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventIds = DB::table('events')->inRandomOrder()->pluck('id');
        $groupIds = DB::table('groups')->pluck('id')->all();

        $selecionados = $eventIds->take((int) ceil($eventIds->count() / 2));

        foreach ($selecionados as $eventId) {
            DB::table('event_group')->insert([
                'event_id' => $eventId,
                'group_id' => fake()->randomElement($groupIds),
            ]);
        }
    }
}
