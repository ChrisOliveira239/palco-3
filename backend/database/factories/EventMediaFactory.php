<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\EventMedia>
 */
class EventMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'evm_tipo' => 'FOTO',
            'evm_url' => fake()->imageUrl(800, 600, 'events'),
            'evm_ordem' => 0,
            'evm_active' => true,
        ];
    }

    /**
     * Indicate that the media is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'evm_tipo' => 'VIDEO',
            'evm_url' => 'https://example.com/videos/'.fake()->uuid().'.mp4',
        ]);
    }
}
