<?php

namespace Tests\Feature\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_pending_event(): void
    {
        $event = Event::factory()->create(['eve_status' => 'PENDENTE']);
        $admin = User::factory()->create(['use_is_admin' => true]);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/events/{$event->id}/approve")
            ->assertOk()
            ->assertJsonPath('event.eve_status', 'PUBLICADO');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'eve_status' => 'PUBLICADO',
            'aprovado_por_id' => $admin->id,
        ]);
        $this->assertNotNull($event->fresh()->eve_aprovado_em);
    }

    public function test_admin_can_reject_pending_event(): void
    {
        $event = Event::factory()->create(['eve_status' => 'PENDENTE']);
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $this->patchJson("/api/events/{$event->id}/reject")
            ->assertOk()
            ->assertJsonPath('event.eve_status', 'REJEITADO');

        $this->assertDatabaseHas('events', ['id' => $event->id, 'eve_status' => 'REJEITADO']);
    }

    public function test_non_admin_cannot_approve_or_reject_event(): void
    {
        $event = Event::factory()->create(['eve_status' => 'PENDENTE']);
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => false]));

        $this->patchJson("/api/events/{$event->id}/approve")->assertForbidden();
        $this->patchJson("/api/events/{$event->id}/reject")->assertForbidden();
    }

    public function test_guest_cannot_approve_or_reject_event(): void
    {
        $event = Event::factory()->create(['eve_status' => 'PENDENTE']);

        $this->patchJson("/api/events/{$event->id}/approve")->assertUnauthorized();
        $this->patchJson("/api/events/{$event->id}/reject")->assertUnauthorized();
    }

    public function test_approving_non_pending_event_fails(): void
    {
        $event = Event::factory()->create(['eve_status' => 'PUBLICADO']);
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $this->patchJson("/api/events/{$event->id}/approve")->assertStatus(409);
    }

    public function test_rejecting_non_pending_event_fails(): void
    {
        $event = Event::factory()->create(['eve_status' => 'RASCUNHO']);
        Sanctum::actingAs(User::factory()->create(['use_is_admin' => true]));

        $this->patchJson("/api/events/{$event->id}/reject")->assertStatus(409);
    }
}
