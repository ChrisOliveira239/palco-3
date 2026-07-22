<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = DB::table('users')->inRandomOrder()->limit(5)->pluck('id');

        foreach ($userIds as $userId) {
            DB::table('groups')->insert([
                'gro_nome' => fake()->company(),
                'gro_descricao' => fake()->sentence(20),
                'gro_avatar_url' => fake()->imageUrl(400, 400, 'abstract'),
                'gro_capa_url' => fake()->imageUrl(800, 400, 'abstract'),
                'gro_cnpj' => fake()->boolean(50) ? fake()->numerify('##.###.###/0001-##') : null,
                'gro_telefone' => fake()->phoneNumber(),
                'gro_email' => fake()->companyEmail(),
                'gro_site' => fake()->boolean(50) ? fake()->url() : null,
                'user_id' => $userId,
                'gro_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
