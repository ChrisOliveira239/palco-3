<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cidades = [
            ['use_city' => 'São Paulo', 'use_state' => 'SP'],
            ['use_city' => 'Rio de Janeiro', 'use_state' => 'RJ'],
            ['use_city' => 'Belo Horizonte', 'use_state' => 'MG'],
        ];

        for ($i = 0; $i < 19; $i++) {
            $cidade = fake()->randomElement($cidades);

            User::factory()->create([
                'use_city' => $cidade['use_city'],
                'use_state' => $cidade['use_state'],
            ]);
        }
    }
}
