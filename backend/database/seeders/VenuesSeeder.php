<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VenuesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locais = [
            ['cidade' => 'São Paulo', 'estado' => 'SP'],
            ['cidade' => 'Rio de Janeiro', 'estado' => 'RJ'],
            ['cidade' => 'Belo Horizonte', 'estado' => 'MG'],
        ];

        foreach ($locais as $local) {
            for ($i = 1; $i <= 2; $i++) {
                DB::table('venues')->insert([
                    'ven_nome' => fake()->company().' '.($i === 1 ? 'Teatro' : 'Arena'),
                    'ven_endereco' => fake()->streetAddress(),
                    'ven_cidade' => $local['cidade'],
                    'ven_estado' => $local['estado'],
                    'ven_latitude' => fake()->latitude(-23.7, -19.9),
                    'ven_longitude' => fake()->longitude(-46.7, -43.2),
                    'ven_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
