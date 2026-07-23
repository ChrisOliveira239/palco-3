<?php

namespace Tests\Feature\Models;

use App\Models\Event;
use App\Models\EventSession;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_event_and_venue(): void
    {
        $event = Event::factory()->create();
        $venue = Venue::factory()->create();
        $session = EventSession::factory()->create([
            'event_id' => $event->id,
            'venue_id' => $venue->id,
        ]);

        $this->assertTrue($session->event->is($event));
        $this->assertTrue($session->venue->is($venue));
    }

    public function test_factory_creates_persisted_session(): void
    {
        $session = EventSession::factory()->create();

        $this->assertDatabaseHas('event_sessions', [
            'id' => $session->id,
            'evs_active' => true,
        ]);
    }
}
