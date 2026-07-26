<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\OpportunityApplication>
 */
class OpportunityApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'user_id' => User::factory(),
            'opa_mensagem' => fake()->sentence(10),
            'opa_status' => 'PENDENTE',
            'opa_active' => true,
        ];
    }

    /**
     * Indicate that the application was accepted.
     */
    public function aceita(): static
    {
        return $this->state(fn (array $attributes) => [
            'opa_status' => 'ACEITO',
        ]);
    }

    /**
     * Indicate that the application was refused.
     */
    public function recusada(): static
    {
        return $this->state(fn (array $attributes) => [
            'opa_status' => 'RECUSADO',
        ]);
    }
}
