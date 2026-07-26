<?php

namespace Database\Factories;

use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_type_id' => TicketType::factory(),
            'user_id' => User::factory(),
            'tic_codigo_qr' => (string) Str::uuid(),
            'tic_status' => 'VALIDO',
            'tic_comprado_em' => now()->subDays(fake()->numberBetween(1, 30)),
            'tic_usado_em' => null,
            'tic_active' => true,
        ];
    }

    /**
     * Indicate that the ticket has already been used.
     */
    public function usado(): static
    {
        return $this->state(fn (array $attributes) => [
            'tic_status' => 'USADO',
            'tic_usado_em' => now(),
        ]);
    }

    /**
     * Indicate that the ticket has been cancelled.
     */
    public function cancelado(): static
    {
        return $this->state(fn (array $attributes) => [
            'tic_status' => 'CANCELADO',
        ]);
    }
}
