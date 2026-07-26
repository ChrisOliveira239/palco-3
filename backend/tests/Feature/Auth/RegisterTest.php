<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'use_name' => 'Maria Teste',
            'email' => 'maria@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'maria@example.com')
            ->assertJsonStructure(['user', 'token']);

        $this->assertDatabaseHas('users', ['email' => 'maria@example.com']);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicado@example.com']);

        $response = $this->postJson('/api/register', [
            'use_name' => 'Outro Usuário',
            'email' => 'duplicado@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_register_fails_without_password_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'use_name' => 'Sem Confirmação',
            'email' => 'semconfirmacao@example.com',
            'password' => 'password123',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_register_defaults_use_tipo_conta_to_pessoa_when_omitted(): void
    {
        $this->postJson('/api/register', [
            'use_name' => 'Pessoa Padrão',
            'email' => 'pessoa@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'pessoa@example.com',
            'use_tipo_conta' => 'PESSOA',
        ]);
    }

    public function test_register_ignores_use_is_admin_input(): void
    {
        $this->postJson('/api/register', [
            'use_name' => 'Tentando Admin',
            'email' => 'tentandoadmin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'use_is_admin' => true,
        ])->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'tentandoadmin@example.com',
            'use_is_admin' => false,
        ]);
    }
}
