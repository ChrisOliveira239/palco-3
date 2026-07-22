<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArtistProfileSkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $artistProfileIds = DB::table('artist_profiles')->pluck('id');
        $skillIds = DB::table('skills')->pluck('id')->all();

        foreach ($artistProfileIds as $artistProfileId) {
            $skills = collect($skillIds)->random(rand(1, 3));

            foreach ($skills as $skillId) {
                DB::table('artist_profile_skill')->insert([
                    'artist_profile_id' => $artistProfileId,
                    'skill_id' => $skillId,
                ]);
            }
        }
    }
}
