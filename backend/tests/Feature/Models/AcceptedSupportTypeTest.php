<?php

namespace Tests\Feature\Models;

use App\Models\AcceptedSupportType;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptedSupportTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_alvo_resolves_event(): void
    {
        $accepted = AcceptedSupportType::factory()->create();

        $this->assertInstanceOf(Event::class, $accepted->alvo);
    }

    public function test_ast_tipo_apoio_is_string(): void
    {
        $accepted = AcceptedSupportType::factory()->create([
            'ast_tipo_apoio' => 'OUTRO',
        ]);

        $this->assertSame('OUTRO', $accepted->ast_tipo_apoio);
    }

    public function test_inverse_relation_on_event(): void
    {
        $event = Event::factory()->create();
        $accepted = AcceptedSupportType::factory()->create(['alvo_id' => $event->id]);

        $this->assertTrue($event->acceptedSupportTypes->contains($accepted));
    }
}
