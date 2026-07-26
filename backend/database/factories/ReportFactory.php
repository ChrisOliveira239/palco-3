<?php

namespace Database\Factories;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\FeedPost;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'denunciante_id' => User::factory(),
            'alvo_type' => Event::class,
            'alvo_id' => Event::factory(),
            'rep_motivo' => fake()->randomElement([
                'Conteúdo ofensivo', 'Spam', 'Informação falsa', 'Golpe/fraude', 'Assédio',
            ]),
            'rep_status' => 'PENDENTE',
            'rep_active' => true,
        ];
    }

    /**
     * Indicate that the report targets an artist profile.
     */
    public function porArtistProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'alvo_type' => ArtistProfile::class,
            'alvo_id' => ArtistProfile::factory(),
        ]);
    }

    /**
     * Indicate that the report targets a group.
     */
    public function porGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'alvo_type' => Group::class,
            'alvo_id' => Group::factory(),
        ]);
    }

    /**
     * Indicate that the report targets a feed post.
     */
    public function porFeedPost(): static
    {
        return $this->state(fn (array $attributes) => [
            'alvo_type' => FeedPost::class,
            'alvo_id' => FeedPost::factory(),
        ]);
    }
}
