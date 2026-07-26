<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
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
            'not_tipo' => fake()->randomElement([
                'novo_seguidor', 'evento_aprovado', 'patrocinio_recebido', 'candidatura_recebida', 'ingresso_comprado',
            ]),
            'not_conteudo' => ['mensagem' => fake()->sentence(8)],
            'not_lida' => false,
            'not_active' => true,
        ];
    }
}
