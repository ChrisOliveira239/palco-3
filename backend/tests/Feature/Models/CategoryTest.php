<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_persisted_category(): void
    {
        $category = Category::factory()->create([
            'cat_nome' => 'Música',
            'cat_slug' => 'musica',
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'cat_nome' => 'Música',
            'cat_slug' => 'musica',
            'cat_active' => true,
        ]);
    }
}
