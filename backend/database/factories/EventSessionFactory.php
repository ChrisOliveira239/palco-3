<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\EventSession>
 */
class EventSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-30 days', '+30 days');

        return [
            'event_id' => Event::factory(),
            'venue_id' => Venue::factory(),
            'evs_data_inicio' => $inicio,
            'evs_data_fim' => (clone $inicio)->modify('+'.rand(2, 5).' hours'),
            'evs_active' => true,
        ];
    }
}
