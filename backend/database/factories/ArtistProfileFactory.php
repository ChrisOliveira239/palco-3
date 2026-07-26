<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ArtistProfile>
 */
class ArtistProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'art_nome_artistico' => fake()->name(),
            'art_bio' => fake()->paragraph(),
            'art_capa_url' => fake()->imageUrl(),
            'art_verificado' => false,
            'art_drt' => fake()->numerify('DRT-#####'),
            'art_telefone' => fake()->phoneNumber(),
            'art_email' => fake()->safeEmail(),
            'art_site' => fake()->url(),
            'art_active' => true,
        ];
    }

    /**
     * Indicate that the artist profile is verified.
     */
    public function verificado(): static
    {
        return $this->state(fn (array $attributes) => [
            'art_verificado' => true,
        ]);
    }
}
