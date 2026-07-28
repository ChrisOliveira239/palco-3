<?php

namespace Tests\Feature\Profiles;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/profile', [
            'use_name' => 'Nome Atualizado',
            'use_city' => 'Fortaleza',
            'use_state' => 'CE',
        ]);

        $response->assertOk()->assertJsonPath('user.use_name', 'Nome Atualizado');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'use_name' => 'Nome Atualizado',
            'use_city' => 'Fortaleza',
            'use_state' => 'CE',
        ]);
    }

    public function test_guest_cannot_update_profile(): void
    {
        $this->patchJson('/api/profile', ['use_name' => 'Alguém'])
            ->assertUnauthorized();
    }

    public function test_update_fails_with_invalid_latitude(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/profile', ['use_latitude' => 200])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('use_latitude');
    }

    public function test_update_ignores_sensitive_fields(): void
    {
        $user = User::factory()->create(['use_is_admin' => false]);
        Sanctum::actingAs($user);

        $this->patchJson('/api/profile', [
            'use_name' => 'Nome Novo',
            'use_is_admin' => true,
            'use_active' => false,
            'email' => 'outro@example.com',
            'password' => 'novasenha123',
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'use_name' => 'Nome Novo',
            'use_is_admin' => false,
            'use_active' => true,
            'email' => $user->email,
        ]);
    }
}