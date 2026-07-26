<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventMediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventIds = DB::table('events')->pluck('id');

        foreach ($eventIds as $eventId) {
            $quantidade = rand(2, 3);

            for ($ordem = 0; $ordem < $quantidade; $ordem++) {
                $tipo = fake()->randomElement(['FOTO', 'VIDEO']);

                DB::table('event_media')->insert([
                    'event_id' => $eventId,
                    'evm_tipo' => $tipo,
                    'evm_url' => $tipo === 'FOTO'
                        ? fake()->imageUrl(800, 600, 'events')
                        : 'https://example.com/videos/'.fake()->uuid().'.mp4',
                    'evm_ordem' => $ordem,
                    'evm_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
