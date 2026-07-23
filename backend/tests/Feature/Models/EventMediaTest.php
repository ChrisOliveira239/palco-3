<?php

namespace Tests\Feature\Models;

use App\Enums\EventMediaType;
use App\Models\Event;
use App\Models\EventMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_event(): void
    {
        $event = Event::factory()->create();
        $media = EventMedia::factory()->create(['event_id' => $event->id]);

        $this->assertTrue($media->event->is($event));
    }

    public function test_casts_evm_tipo_to_enum(): void
    {
        $media = EventMedia::factory()->create();

        $this->assertSame(EventMediaType::FOTO, $media->evm_tipo);
    }

    public function test_video_state(): void
    {
        $media = EventMedia::factory()->video()->create();

        $this->assertSame(EventMediaType::VIDEO, $media->evm_tipo);
    }
}
