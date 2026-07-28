<?php

namespace Tests\Feature\Events;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function organizerEvent(): array
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
        ]);

        return [$user, $event];
    }

    public function test_guest_lists_active_sessions_of_event(): void
    {
        $event = Event::factory()->create();
        $active = EventSession::factory()->for($event)->create();
        EventSession::factory()->for($event)->create(['evs_active' => false]);

        $response = $this->getJson("/api/events/{$event->id}/sessions")->assertOk();

        $ids = collect($response->json('sessions'))->pluck('id');

        $this->assertTrue($ids->contains($active->id));
        $this->assertCount(1, $ids);
    }

    public function test_organizer_can_create_session_with_venue(): void
    {
        [$user, $event] = $this->organizerEvent();
        $venue = Venue::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/events/{$event->id}/sessions", [
            'venue_id' => $venue->id,
            'evs_data_inicio' => now()->addDays(10)->toDateTimeString(),
        ]);

        $response->assertCreated()->assertJsonPath('session.venue_id', $venue->id);
    }

    public function test_organizer_can_create_session_with_endereco_livre(): void
    {
        [$user, $event] = $this->organizerEvent();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/events/{$event->id}/sessions", [
            'evs_endereco' => 'Praça Central, 100',
            'evs_cidade' => 'Recife',
            'evs_estado' => 'PE',
            'evs_data_inicio' => now()->addDays(10)->toDateTimeString(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('session.venue_id', null)
            ->assertJsonPath('session.evs_cidade', 'Recife');
    }

    public function test_session_without_venue_or_endereco_livre_fails(): void
    {
        [$user, $event] = $this->organizerEvent();
        Sanctum::actingAs($user);

        $this->postJson("/api/events/{$event->id}/sessions", [
            'evs_data_inicio' => now()->addDays(10)->toDateTimeString(),
        ])->assertUnprocessable()->assertJsonValidationErrors('evs_endereco');
    }

    public function test_non_organizer_cannot_create_session(): void
    {
        $event = Event::factory()->create();
        $venue = Venue::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/sessions", [
            'venue_id' => $venue->id,
            'evs_data_inicio' => now()->addDays(10)->toDateTimeString(),
        ])->assertForbidden();
    }

    public function test_guest_cannot_create_session(): void
    {
        $event = Event::factory()->create();
        $venue = Venue::factory()->create();

        $this->postJson("/api/events/{$event->id}/sessions", [
            'venue_id' => $venue->id,
            'evs_data_inicio' => now()->addDays(10)->toDateTimeString(),
        ])->assertUnauthorized();
    }

    public function test_evs_data_fim_before_inicio_fails(): void
    {
        [$user, $event] = $this->organizerEvent();
        $venue = Venue::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson("/api/events/{$event->id}/sessions", [
            'venue_id' => $venue->id,
            'evs_data_inicio' => now()->addDays(10)->toDateTimeString(),
            'evs_data_fim' => now()->addDays(9)->toDateTimeString(),
        ])->assertUnprocessable()->assertJsonValidationErrors('evs_data_fim');
    }

    public function test_nonexistent_venue_id_fails(): void
    {
        [$user, $event] = $this->organizerEvent();
        Sanctum::actingAs($user);

        $this->postJson("/api/events/{$event->id}/sessions", [
            'venue_id' => 99999,
            'evs_data_inicio' => now()->addDays(10)->toDateTimeString(),
        ])->assertUnprocessable()->assertJsonValidationErrors('venue_id');
    }

    public function test_organizer_can_update_own_session(): void
    {
        [$user, $event] = $this->organizerEvent();
        $session = EventSession::factory()->for($event)->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/events/{$event->id}/sessions/{$session->id}", [
            'evs_local_nome' => 'Novo Nome do Local',
        ])->assertOk()->assertJsonPath('session.evs_local_nome', 'Novo Nome do Local');
    }

    public function test_non_organizer_cannot_update_session(): void
    {
        $event = Event::factory()->create();
        $session = EventSession::factory()->for($event)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/events/{$event->id}/sessions/{$session->id}", [
            'evs_local_nome' => 'Tentativa',
        ])->assertForbidden();
    }

    public function test_organizer_can_deactivate_own_session(): void
    {
        [$user, $event] = $this->organizerEvent();
        $session = EventSession::factory()->for($event)->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/events/{$event->id}/sessions/{$session->id}")->assertOk();

        $this->assertDatabaseHas('event_sessions', ['id' => $session->id, 'evs_active' => false]);
    }

    public function test_non_organizer_cannot_deactivate_session(): void
    {
        $event = Event::factory()->create();
        $session = EventSession::factory()->for($event)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/events/{$event->id}/sessions/{$session->id}")->assertForbidden();
    }

    public function test_session_from_another_event_returns_404(): void
    {
        [$user, $event] = $this->organizerEvent();
        $otherEvent = Event::factory()->create();
        $session = EventSession::factory()->for($otherEvent)->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/events/{$event->id}/sessions/{$session->id}", [
            'evs_local_nome' => 'X',
        ])->assertNotFound();
    }
}
