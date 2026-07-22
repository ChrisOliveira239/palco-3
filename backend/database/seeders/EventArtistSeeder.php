<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventArtistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventIds = DB::table('events')->inRandomOrder()->pluck('id');
        $artistProfileIds = DB::table('artist_profiles')->pluck('id')->all();

        $selecionados = $eventIds->take((int) ceil($eventIds->count() / 2));

        foreach ($selecionados as $eventId) {
            $artistas = collect($artistProfileIds)->random(min(rand(1, 2), count($artistProfileIds)));

            foreach ($artistas as $artistProfileId) {
                DB::table('event_artist')->insert([
                    'event_id' => $eventId,
                    'artist_profile_id' => $artistProfileId,
                ]);
            }
        }
    }
}
