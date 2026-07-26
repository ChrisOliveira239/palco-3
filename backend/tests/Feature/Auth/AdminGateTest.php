<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['auth:sanctum', 'admin'])
            ->get('/__test/admin-only', fn () => response()->json(['ok' => true]));
    }

    public function test_gate_allows_admin_user(): void
    {
        $admin = User::factory()->create(['use_is_admin' => true]);

        $this->assertTrue(Gate::forUser($admin)->allows('admin'));
    }

    public function test_gate_denies_non_admin_user(): void
    {
        $user = User::factory()->create(['use_is_admin' => false]);

        $this->assertFalse(Gate::forUser($user)->allows('admin'));
    }

    public function test_admin_middleware_allows_admin_user(): void
    {
        $admin = User::factory()->create(['use_is_admin' => true]);
        Sanctum::actingAs($admin);

        $this->getJson('/__test/admin-only')->assertOk();
    }

    public function test_admin_middleware_denies_non_admin_user(): void
    {
        $user = User::factory()->create(['use_is_admin' => false]);
        Sanctum::actingAs($user);

        $this->getJson('/__test/admin-only')->assertForbidden();
    }

    public function test_admin_middleware_denies_guest(): void
    {
        $this->getJson('/__test/admin-only')->assertUnauthorized();
    }
}
