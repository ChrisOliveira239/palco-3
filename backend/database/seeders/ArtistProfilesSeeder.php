<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = DB::table('users')->inRandomOrder()->limit(8)->pluck('id');

        foreach ($userIds as $userId) {
            DB::table('artist_profiles')->insert([
                'user_id' => $userId,
                'art_nome_artistico' => fake()->userName(),
                'art_bio' => fake()->sentence(15),
                'art_capa_url' => fake()->imageUrl(800, 400, 'people'),
                'art_verificado' => fake()->boolean(30),
                'art_drt' => fake()->boolean(40) ? fake()->numerify('DRT-####') : null,
                'art_telefone' => fake()->phoneNumber(),
                'art_email' => fake()->safeEmail(),
                'art_site' => fake()->boolean(50) ? fake()->url() : null,
                'art_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
