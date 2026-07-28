<?php

namespace Tests\Feature\Events;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_active_categories(): void
    {
        $active = Category::factory()->create();
        Category::factory()->create(['cat_active' => false]);

        $response = $this->getJson('/api/categories')->assertOk();

        $ids = collect($response->json('categories'))->pluck('id');

        $this->assertTrue($ids->contains($active->id));
        $this->assertCount(1, $ids);
    }

    public function test_admin_can_create_category_with_auto_generated_slug(): void
    {
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $response = $this->postJson('/api/categories', ['cat_nome' => 'Música Popular']);

        $response->assertCreated()->assertJsonPath('category.cat_slug', 'musica-popular');

        $this->assertDatabaseHas('categories', [
            'cat_nome' => 'Música Popular',
            'cat_slug' => 'musica-popular',
        ]);
    }

    public function test_non_admin_cannot_create_category(): void
    {
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => false]));

        $this->postJson('/api/categories', ['cat_nome' => 'Teatro'])->assertForbidden();
    }

    public function test_guest_cannot_create_category(): void
    {
        $this->postJson('/api/categories', ['cat_nome' => 'Teatro'])->assertUnauthorized();
    }

    public function test_admin_can_update_category(): void
    {
        $category = Category::factory()->create(['cat_nome' => 'Antigo']);
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $this->patchJson("/api/categories/{$category->id}", ['cat_nome' => 'Novo Nome'])
            ->assertOk()
            ->assertJsonPath('category.cat_slug', 'novo-nome');
    }

    public function test_admin_can_deactivate_category(): void
    {
        $category = Category::factory()->create();
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $this->deleteJson("/api/categories/{$category->id}")->assertOk();

        $this->assertDatabaseHas('categories', ['id' => $category->id, 'cat_active' => false]);
    }

    public function test_duplicate_category_name_fails_validation(): void
    {
        Category::factory()->create(['cat_nome' => 'Repetido']);
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $this->postJson('/api/categories', ['cat_nome' => 'Repetido'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('cat_nome');
    }
}
