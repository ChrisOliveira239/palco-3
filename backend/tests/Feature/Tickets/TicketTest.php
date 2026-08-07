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

class TicketTest extends TestCase
{
    use RefreshDatabase;

    protected function ticketTypeInSession(array $attributes = []): array
    {
        $organizerUser = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($organizerUser)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
        ]);
        $session = EventSession::factory()->for($event)->create();
        $ticketType = TicketType::factory()->for($session)->create($attributes);

        return [$event, $session, $ticketType];
    }

    protected function purchaseUrl(Event $event, EventSession $session, TicketType $ticketType): string
    {
        return "/api/events/{$event->id}/sessions/{$session->id}/ticket-types/{$ticketType->id}/tickets";
    }

    public function test_authenticated_user_can_purchase_ticket(): void
    {
        [$event, $session, $ticketType] = $this->ticketTypeInSession();
        $buyer = User::factory()->create();
        Sanctum::actingAs($buyer);

        $response = $this->postJson($this->purchaseUrl($event, $session, $ticketType));

        $response->assertCreated()
            ->assertJsonPath('ticket.tic_status', 'VALIDO')
            ->assertJsonPath('ticket.user_id', $buyer->id);
        $this->assertNotEmpty($response->json('ticket.tic_codigo_qr'));
    }

    public function test_purchase_increments_quantidade_vendida(): void
    {
        [$event, $session, $ticketType] = $this->ticketTypeInSession(['tit_quantidade_vendida' => 5]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->purchaseUrl($event, $session, $ticketType))->assertCreated();

        $this->assertDatabaseHas('ticket_types', ['id' => $ticketType->id, 'tit_quantidade_vendida' => 6]);
    }

    public function test_guest_cannot_purchase(): void
    {
        [$event, $session, $ticketType] = $this->ticketTypeInSession();

        $this->postJson($this->purchaseUrl($event, $session, $ticketType))->assertUnauthorized();
    }

    public function test_inactive_ticket_type_returns_404(): void
    {
        [$event, $session, $ticketType] = $this->ticketTypeInSession(['tit_active' => false]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->purchaseUrl($event, $session, $ticketType))->assertNotFound();
    }

    public function test_purchase_before_venda_inicio_fails(): void
    {
        [$event, $session, $ticketType] = $this->ticketTypeInSession([
            'tit_venda_inicio' => now()->addDays(5),
        ]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->purchaseUrl($event, $session, $ticketType))->assertStatus(422);
    }

    public function test_purchase_after_venda_fim_fails(): void
    {
        [$event, $session, $ticketType] = $this->ticketTypeInSession([
            'tit_venda_inicio' => now()->subDays(10),
            'tit_venda_fim' => now()->subDays(1),
        ]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->purchaseUrl($event, $session, $ticketType))->assertStatus(422);
    }

    public function test_sold_out_ticket_type_fails_without_creating_ticket(): void
    {
        [$event, $session, $ticketType] = $this->ticketTypeInSession([
            'tit_quantidade_total' => 10,
            'tit_quantidade_vendida' => 10,
        ]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->purchaseUrl($event, $session, $ticketType))->assertStatus(422);

        $this->assertDatabaseHas('ticket_types', ['id' => $ticketType->id, 'tit_quantidade_vendida' => 10]);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_ticket_type_from_wrong_session_returns_404(): void
    {
        [$event, $session, $ticketType] = $this->ticketTypeInSession();
        $otherSession = EventSession::factory()->for($event)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/sessions/{$otherSession->id}/ticket-types/{$ticketType->id}/tickets")
            ->assertNotFound();
    }

    public function test_user_lists_only_own_tickets(): void
    {
        [, , $ticketType] = $this->ticketTypeInSession();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownTicket = Ticket::factory()->for($ticketType)->for($user)->create();
        Ticket::factory()->for($ticketType)->for($otherUser)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tickets')->assertOk();

        $ids = collect($response->json('tickets'))->pluck('id');

        $this->assertEquals([$ownTicket->id], $ids->all());
    }

    public function test_owner_can_view_own_ticket(): void
    {
        [, , $ticketType] = $this->ticketTypeInSession();
        $user = User::factory()->create();
        $ticket = Ticket::factory()->for($ticketType)->for($user)->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/tickets/{$ticket->id}")->assertOk()->assertJsonPath('ticket.id', $ticket->id);
    }

    public function test_other_user_cannot_view_ticket(): void
    {
        [, , $ticketType] = $this->ticketTypeInSession();
        $owner = User::factory()->create();
        $ticket = Ticket::factory()->for($ticketType)->for($owner)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/tickets/{$ticket->id}")->assertForbidden();
    }
}
