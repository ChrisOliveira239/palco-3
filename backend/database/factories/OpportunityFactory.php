<?php

namespace Database\Factories;

use App\Models\ArtistProfile;
use App\Models\Group;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'criador_type' => ArtistProfile::class,
            'criador_id' => ArtistProfile::factory(),
            'opp_titulo' => 'Procura-se '.fake()->jobTitle(),
            'opp_descricao' => fake()->paragraph(3),
            'skill_id' => Skill::factory(),
            'opp_cidade' => fake()->randomElement(['São Paulo', 'Rio de Janeiro', 'Belo Horizonte']),
            'opp_status' => 'ABERTA',
            'opp_active' => true,
        ];
    }

    /**
     * Indicate that the opportunity's creator is a group.
     */
    public function porGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'criador_type' => Group::class,
            'criador_id' => Group::factory(),
        ]);
    }
}
