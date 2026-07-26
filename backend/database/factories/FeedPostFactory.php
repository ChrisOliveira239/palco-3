<?php

namespace Database\Factories;

use App\Enums\Types;
use App\Models\ArtistProfile;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\FeedPost>
 */
class FeedPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'autor_type' => ArtistProfile::class,
            'autor_id' => ArtistProfile::factory(),
            'fee_tipo' => fake()->randomElement(Types::FEED_POST_TYPE),
            'fee_conteudo' => fake()->sentence(15),
            'fee_midia_url' => null,
            'fee_active' => true,
        ];
    }

    /**
     * Indicate that the feed post's author is a group.
     */
    public function porGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'autor_type' => Group::class,
            'autor_id' => Group::factory(),
        ]);
    }
}
