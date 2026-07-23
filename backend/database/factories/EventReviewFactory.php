<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\EventReview>
 */
class EventReviewFactory extends Factory
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
            'user_id' => User::factory(),
            'evr_nota' => fake()->numberBetween(1, 5),
            'evr_comentario' => fake()->optional(0.7)->sentence(12),
            'evr_active' => true,
        ];
    }
}
