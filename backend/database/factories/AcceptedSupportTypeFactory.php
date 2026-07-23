<?php

namespace Database\Factories;

use App\Enums\Types;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AcceptedSupportType>
 */
class AcceptedSupportTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alvo_type' => Event::class,
            'alvo_id' => Event::factory(),
            'ast_tipo_apoio' => fake()->randomElement(Types::TIPO_APOIO),
        ];
    }
}
