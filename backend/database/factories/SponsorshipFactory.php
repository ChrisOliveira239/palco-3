<?php

namespace Database\Factories;

use App\Enums\SponsorshipStatus;
use App\Enums\TipoApoio;
use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Sponsorship>
 */
class SponsorshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sponsor_type' => User::class,
            'sponsor_id' => User::factory(),
            'alvo_type' => Event::class,
            'alvo_id' => Event::factory(),
            'spo_tipo_apoio' => fake()->randomElement(TipoApoio::cases()),
            'spo_valor' => null,
            'spo_descricao' => fake()->sentence(10),
            'spo_status' => SponsorshipStatus::PROPOSTO,
            'spo_active' => true,
        ];
    }

    /**
     * Indicate that the sponsorship targets an artist profile.
     */
    public function porArtistProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'alvo_type' => ArtistProfile::class,
            'alvo_id' => ArtistProfile::factory(),
        ]);
    }

    /**
     * Indicate that the sponsorship targets a group.
     */
    public function porGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'alvo_type' => Group::class,
            'alvo_id' => Group::factory(),
        ]);
    }

    /**
     * Indicate that the sponsorship is a monetary contribution.
     */
    public function dinheiro(): static
    {
        return $this->state(fn (array $attributes) => [
            'spo_tipo_apoio' => TipoApoio::DINHEIRO,
            'spo_valor' => fake()->randomFloat(2, 100, 5000),
            'spo_descricao' => null,
        ]);
    }
}
