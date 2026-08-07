<?php

namespace Database\Factories;

use App\Enums\Types;
use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AcceptedSupportType>
 */
class AcceptedSupportTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alvo_type' => Event::class,
            'alvo_id' => Event::factory(),
            'ast_tipo_apoio' => fake()->randomElement(Types::TIPO_APOIO),
        ];
    }

    /**
     * Indicate that the accepted support type targets an artist profile.
     */
    public function porArtistProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'alvo_type' => ArtistProfile::class,
            'alvo_id' => ArtistProfile::factory(),
        ]);
    }

    /**
     * Indicate that the accepted support type targets a group.
     */
    public function porGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'alvo_type' => Group::class,
            'alvo_id' => Group::factory(),
        ]);
    }
}
