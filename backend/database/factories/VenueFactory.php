<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ven_nome' => fake()->company(),
            'ven_endereco' => fake()->streetAddress(),
            'ven_cidade' => fake()->city(),
            'ven_estado' => fake()->stateAbbr(),
            'ven_latitude' => fake()->latitude(-23.7, -19.9),
            'ven_longitude' => fake()->longitude(-46.7, -43.2),
            'ven_active' => true,
        ];
    }
}
