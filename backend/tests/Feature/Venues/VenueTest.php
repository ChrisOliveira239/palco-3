<?php

namespace Tests\Feature\Venues;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_active_venues(): void
    {
        $active = Venue::factory()->create();
        Venue::factory()->create(['ven_active' => false]);

        $response = $this->getJson('/api/venues')->assertOk();

        $ids = collect($response->json('venues'))->pluck('id');

        $this->assertTrue($ids->contains($active->id));
        $this->assertCount(1, $ids);
    }

    public function test_admin_can_create_venue(): void
    {
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $response = $this->postJson('/api/venues', [
            'ven_nome' => 'Teatro Municipal',
            'ven_endereco' => 'Praça Ramos de Azevedo, s/n',
            'ven_cidade' => 'São Paulo',
            'ven_estado' => 'SP',
            'ven_latitude' => -23.5431,
            'ven_longitude' => -46.6379,
        ]);

        $response->assertCreated()->assertJsonPath('venue.ven_nome', 'Teatro Municipal');

        $this->assertDatabaseHas('venues', ['ven_nome' => 'Teatro Municipal']);
    }

    public function test_non_admin_cannot_create_venue(): void
    {
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => false]));

        $this->postJson('/api/venues', [
            'ven_nome' => 'Teatro',
            'ven_endereco' => 'Rua X',
            'ven_cidade' => 'Cidade',
            'ven_estado' => 'SP',
        ])->assertForbidden();
    }

    public function test_guest_cannot_create_venue(): void
    {
        $this->postJson('/api/venues', [
            'ven_nome' => 'Teatro',
            'ven_endereco' => 'Rua X',
            'ven_cidade' => 'Cidade',
            'ven_estado' => 'SP',
        ])->assertUnauthorized();
    }

    public function test_admin_can_update_venue(): void
    {
        $venue = Venue::factory()->create(['ven_nome' => 'Antigo']);
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $this->patchJson("/api/venues/{$venue->id}", ['ven_nome' => 'Novo Nome'])
            ->assertOk()
            ->assertJsonPath('venue.ven_nome', 'Novo Nome');
    }

    public function test_admin_can_deactivate_venue(): void
    {
        $venue = Venue::factory()->create();
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $this->deleteJson("/api/venues/{$venue->id}")->assertOk();

        $this->assertDatabaseHas('venues', ['id' => $venue->id, 'ven_active' => false]);
    }

    public function test_latitude_out_of_range_fails_validation(): void
    {
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $this->postJson('/api/venues', [
            'ven_nome' => 'Teatro',
            'ven_endereco' => 'Rua X',
            'ven_cidade' => 'Cidade',
            'ven_estado' => 'SP',
            'ven_latitude' => 200,
            'ven_longitude' => -46.6379,
        ])->assertUnprocessable()->assertJsonValidationErrors('ven_latitude');
    }
}
