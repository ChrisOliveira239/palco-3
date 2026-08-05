<?php

namespace Tests\Feature\Tickets;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function organizerSession(): array
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
        ]);
        $session = EventSession::factory()->for($event)->create();

        return [$user, $event, $session];
    }

    public function test_guest_lists_only_active_ticket_types(): void
    {
        [, $event, $session] = $this->organizerSession();
        $active = TicketType::factory()->for($session)->create();
        TicketType::factory()->for($session)->create(['tit_active' => false]);

        $response = $this->getJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types")->assertOk();

        $ids = collect($response->json('ticket_types'))->pluck('id');

        $this->assertEquals([$active->id], $ids->all());
    }

    public function test_organizer_can_create_ticket_type(): void
    {
        [$user, $event, $session] = $this->organizerSession();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types", [
            'tit_nome' => 'Inteira',
            'tit_preco' => 100,
            'tit_quantidade_total' => 50,
        ]);

        $response->assertCreated()->assertJsonPath('ticket_type.tit_nome', 'Inteira');
    }

    public function test_negative_preco_fails_validation(): void
    {
        [$user, $event, $session] = $this->organizerSession();
        Sanctum::actingAs($user);

        $this->postJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types", [
            'tit_nome' => 'Inteira',
            'tit_preco' => -10,
            'tit_quantidade_total' => 50,
        ])->assertUnprocessable()->assertJsonValidationErrors('tit_preco');
    }

    public function test_zero_quantidade_total_fails_validation(): void
    {
        [$user, $event, $session] = $this->organizerSession();
        Sanctum::actingAs($user);

        $this->postJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types", [
            'tit_nome' => 'Inteira',
            'tit_preco' => 100,
            'tit_quantidade_total' => 0,
        ])->assertUnprocessable()->assertJsonValidationErrors('tit_quantidade_total');
    }

    public function test_venda_fim_before_inicio_fails_validation(): void
    {
        [$user, $event, $session] = $this->organizerSession();
        Sanctum::actingAs($user);

        $this->postJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types", [
            'tit_nome' => 'Inteira',
            'tit_preco' => 100,
            'tit_quantidade_total' => 50,
            'tit_venda_inicio' => now()->addDays(10)->toDateTimeString(),
            'tit_venda_fim' => now()->addDays(9)->toDateTimeString(),
        ])->assertUnprocessable()->assertJsonValidationErrors('tit_venda_fim');
    }

    public function test_non_organizer_cannot_create_ticket_type(): void
    {
        [, $event, $session] = $this->organizerSession();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types", [
            'tit_nome' => 'Inteira',
            'tit_preco' => 100,
            'tit_quantidade_total' => 50,
        ])->assertForbidden();
    }

    public function test_guest_cannot_create_ticket_type(): void
    {
        [, $event, $session] = $this->organizerSession();

        $this->postJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types", [
            'tit_nome' => 'Inteira',
            'tit_preco' => 100,
            'tit_quantidade_total' => 50,
        ])->assertUnauthorized();
    }

    public function test_organizer_can_update_own_ticket_type(): void
    {
        [$user, $event, $session] = $this->organizerSession();
        $ticketType = TicketType::factory()->for($session)->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types/{$ticketType->id}", [
            'tit_preco' => 150,
        ])->assertOk()->assertJsonPath('ticket_type.tit_preco', '150.00');
    }

    public function test_reducing_quantidade_total_below_vendida_fails(): void
    {
        [$user, $event, $session] = $this->organizerSession();
        $ticketType = TicketType::factory()->for($session)->create([
            'tit_quantidade_total' => 50,
            'tit_quantidade_vendida' => 30,
        ]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types/{$ticketType->id}", [
            'tit_quantidade_total' => 20,
        ])->assertUnprocessable()->assertJsonValidationErrors('tit_quantidade_total');
    }

    public function test_organizer_can_deactivate_own_ticket_type(): void
    {
        [$user, $event, $session] = $this->organizerSession();
        $ticketType = TicketType::factory()->for($session)->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types/{$ticketType->id}")->assertOk();

        $this->assertDatabaseHas('ticket_types', ['id' => $ticketType->id, 'tit_active' => false]);
    }

    public function test_non_organizer_cannot_update_or_deactivate_ticket_type(): void
    {
        [, $event, $session] = $this->organizerSession();
        $ticketType = TicketType::factory()->for($session)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types/{$ticketType->id}", [
            'tit_preco' => 200,
        ])->assertForbidden();

        $this->deleteJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types/{$ticketType->id}")->assertForbidden();
    }

    public function test_ticket_type_from_another_session_returns_404(): void
    {
        [$user, $event, $session] = $this->organizerSession();
        $otherSession = EventSession::factory()->for($event)->create();
        $ticketType = TicketType::factory()->for($otherSession)->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/events/{$event->id}/sessions/{$session->id}/ticket-types/{$ticketType->id}", [
            'tit_preco' => 200,
        ])->assertNotFound();
    }

    public function test_session_from_another_event_returns_404(): void
    {
        [, $event] = $this->organizerSession();
        $otherEvent = Event::factory()->create();
        $otherSession = EventSession::factory()->for($otherEvent)->create();

        $this->getJson("/api/events/{$event->id}/sessions/{$otherSession->id}/ticket-types")
            ->assertNotFound();
    }
}
