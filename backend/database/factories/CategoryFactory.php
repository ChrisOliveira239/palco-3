<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nome = ucwords(fake()->unique()->words(2, true));

        return [
            'cat_nome' => $nome,
            'cat_slug' => Str::slug($nome),
            'cat_icone' => null,
            'cat_active' => true,
        ];
    }
}
