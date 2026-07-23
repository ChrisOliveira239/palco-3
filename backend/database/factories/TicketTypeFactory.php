<?php

namespace Database\Factories;

use App\Models\EventSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TicketType>
 */
class TicketTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_session_id' => EventSession::factory(),
            'tit_nome' => fake()->randomElement(['Meia-entrada', 'Inteira', 'VIP']),
            'tit_preco' => fake()->randomFloat(2, 20, 250),
            'tit_quantidade_total' => fake()->numberBetween(20, 100),
            'tit_quantidade_vendida' => 0,
            'tit_venda_inicio' => now()->subDays(fake()->numberBetween(10, 30)),
            'tit_venda_fim' => now()->addDays(fake()->numberBetween(1, 30)),
            'tit_active' => true,
        ];
    }
}
