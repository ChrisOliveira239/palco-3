<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gro_nome' => fake()->company(),
            'gro_descricao' => fake()->paragraph(),
            'gro_avatar_url' => fake()->imageUrl(),
            'gro_capa_url' => fake()->imageUrl(),
            'gro_cnpj' => fake()->numerify('##.###.###/0001-##'),
            'gro_telefone' => fake()->phoneNumber(),
            'gro_email' => fake()->safeEmail(),
            'gro_site' => fake()->url(),
            'user_id' => User::factory(),
            'gro_active' => true,
        ];
    }
}
