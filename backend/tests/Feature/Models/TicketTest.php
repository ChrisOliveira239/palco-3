<?php

namespace Tests\Feature\Models;

use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_ticket_type_and_user(): void
    {
        $ticketType = TicketType::factory()->create();
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'ticket_type_id' => $ticketType->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue($ticket->ticketType->is($ticketType));
        $this->assertTrue($ticket->user->is($user));
    }

    public function test_tic_status_is_string(): void
    {
        $ticket = Ticket::factory()->create();

        $this->assertSame('VALIDO', $ticket->tic_status);
    }

    public function test_usado_state(): void
    {
        $ticket = Ticket::factory()->usado()->create();

        $this->assertSame('USADO', $ticket->tic_status);
        $this->assertNotNull($ticket->tic_usado_em);
    }

    public function test_cancelado_state(): void
    {
        $ticket = Ticket::factory()->cancelado()->create();

        $this->assertSame('CANCELADO', $ticket->tic_status);
    }
}
