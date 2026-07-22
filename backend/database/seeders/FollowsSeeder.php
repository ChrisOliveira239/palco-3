<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FollowsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->all();
        $seguiveis = $this->seguivelPool();

        $usados = [];
        $criados = 0;
        $tentativas = 0;

        while ($criados < 30 && $tentativas < 200) {
            $tentativas++;

            $userId = fake()->randomElement($userIds);
            $seguivel = fake()->randomElement($seguiveis);
            $chave = $userId.'|'.$seguivel['type'].'|'.$seguivel['id'];

            if (isset($usados[$chave])) {
                continue;
            }

            $usados[$chave] = true;
            $criados++;

            DB::table('follows')->insert([
                'user_id' => $userId,
                'seguivel_type' => $seguivel['type'],
                'seguivel_id' => $seguivel['id'],
                'created_at' => now(),
            ]);
        }
    }

    private function seguivelPool(): array
    {
        $pool = [];

        foreach (DB::table('artist_profiles')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\ArtistProfile', 'id' => $id];
        }

        foreach (DB::table('groups')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\Group', 'id' => $id];
        }

        foreach (DB::table('events')->pluck('id') as $id) {
            $pool[] = ['type' => 'App\\Models\\Event', 'id' => $id];
        }

        return $pool;
    }
}
