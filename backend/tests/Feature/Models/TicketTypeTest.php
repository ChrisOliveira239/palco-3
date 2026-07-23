<?php

namespace Tests\Feature\Models;

use App\Models\EventSession;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TicketTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_event_session(): void
    {
        $session = EventSession::factory()->create();
        $ticketType = TicketType::factory()->create(['event_session_id' => $session->id]);

        $this->assertTrue($ticketType->eventSession->is($session));
    }

    public function test_has_many_tickets(): void
    {
        $ticketType = TicketType::factory()->create();
        $ticket = $ticketType->tickets()->create([
            'user_id' => User::factory()->create()->id,
            'tic_codigo_qr' => Str::uuid(),
        ]);

        $this->assertTrue($ticketType->tickets->contains($ticket));
    }

    public function test_casts_tit_preco_to_decimal(): void
    {
        $ticketType = TicketType::factory()->create(['tit_preco' => 99.9]);

        $this->assertSame('99.90', $ticketType->tit_preco);
    }
}
