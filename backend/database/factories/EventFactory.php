<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\ArtistProfile;
use App\Models\Category;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eve_titulo' => fake()->sentence(4),
            'eve_descricao' => fake()->paragraph(4),
            'category_id' => Category::factory(),
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => ArtistProfile::factory(),
            'eve_status' => EventStatus::RASCUNHO,
            'eve_gratuito' => fake()->boolean(30),
            'eve_cartaz_url' => fake()->imageUrl(600, 800),
            'eve_links_externos' => null,
            'aprovado_por_id' => null,
            'eve_aprovado_em' => null,
            'eve_active' => true,
        ];
    }

    /**
     * Indicate that the event is organized by a Group instead of an ArtistProfile.
     */
    public function porGrupo(): static
    {
        return $this->state(fn (array $attributes) => [
            'organizador_type' => Group::class,
            'organizador_id' => Group::factory(),
        ]);
    }

    /**
     * Indicate that the event is published.
     */
    public function publicado(): static
    {
        return $this->state(fn (array $attributes) => [
            'eve_status' => EventStatus::PUBLICADO,
        ]);
    }
}
