<?php

namespace Tests\Feature\Tickets;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function ticketFor(Event $organizerEvent, array $ticketAttributes = []): Ticket
    {
        $session = EventSession::factory()->for($organizerEvent)->create();
        $ticketType = TicketType::factory()->for($session)->create();

        return Ticket::factory()->for($ticketType)->create($ticketAttributes);
    }

    protected function organizerEvent(): array
    {
        $organizerUser = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($organizerUser)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
        ]);

        return [$organizerUser, $event];
    }

    protected function validateUrl(Event $event): string
    {
        return "/api/events/{$event->id}/tickets/validate";
    }

    public function test_organizer_can_validate_valid_ticket(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        $ticket = $this->ticketFor($event);
        Sanctum::actingAs($organizerUser);

        $response = $this->postJson($this->validateUrl($event), [
            'tic_codigo_qr' => $ticket->tic_codigo_qr,
        ]);

        $response->assertOk()->assertJsonPath('ticket.tic_status', 'USADO');
        $this->assertNotNull($response->json('ticket.tic_usado_em'));
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'tic_status' => 'USADO']);
    }

    public function test_validating_same_ticket_twice_fails(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        $ticket = $this->ticketFor($event);
        Sanctum::actingAs($organizerUser);

        $this->postJson($this->validateUrl($event), ['tic_codigo_qr' => $ticket->tic_codigo_qr])->assertOk();

        $this->postJson($this->validateUrl($event), ['tic_codigo_qr' => $ticket->tic_codigo_qr])
            ->assertStatus(422);
    }

    public function test_cancelled_ticket_cannot_be_validated(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        $ticket = $this->ticketFor($event, ['tic_status' => 'CANCELADO']);
        Sanctum::actingAs($organizerUser);

        $this->postJson($this->validateUrl($event), ['tic_codigo_qr' => $ticket->tic_codigo_qr])
            ->assertStatus(422);

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'tic_status' => 'CANCELADO']);
    }

    public function test_unknown_qr_code_returns_404(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        Sanctum::actingAs($organizerUser);

        $this->postJson($this->validateUrl($event), ['tic_codigo_qr' => 'codigo-inexistente'])
            ->assertNotFound();
    }

    public function test_ticket_from_another_event_returns_404(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        [, $otherEvent] = $this->organizerEvent();
        $ticket = $this->ticketFor($otherEvent);
        Sanctum::actingAs($organizerUser);

        $this->postJson($this->validateUrl($event), ['tic_codigo_qr' => $ticket->tic_codigo_qr])
            ->assertNotFound();
    }

    public function test_non_organizer_cannot_validate_ticket(): void
    {
        [, $event] = $this->organizerEvent();
        $ticket = $this->ticketFor($event);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->validateUrl($event), ['tic_codigo_qr' => $ticket->tic_codigo_qr])
            ->assertForbidden();
    }

    public function test_guest_cannot_validate_ticket(): void
    {
        [, $event] = $this->organizerEvent();
        $ticket = $this->ticketFor($event);

        $this->postJson($this->validateUrl($event), ['tic_codigo_qr' => $ticket->tic_codigo_qr])
            ->assertUnauthorized();
    }
}
