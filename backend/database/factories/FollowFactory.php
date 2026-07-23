<?php

namespace Database\Factories;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Follow>
 */
class FollowFactory extends Factory
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
            'seguivel_type' => ArtistProfile::class,
            'seguivel_id' => ArtistProfile::factory(),
        ];
    }

    /**
     * Indicate that the follow targets a group.
     */
    public function porGroup(): static
    {
        return $this->state(fn (array $attributes) => [
            'seguivel_type' => Group::class,
            'seguivel_id' => Group::factory(),
        ]);
    }

    /**
     * Indicate that the follow targets an event.
     */
    public function porEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'seguivel_type' => Event::class,
            'seguivel_id' => Event::factory(),
        ]);
    }
}
