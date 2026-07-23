<?php

namespace Tests\Feature\Models;

use App\Enums\EventMediaType;
use App\Enums\EventStatus;
use App\Models\ArtistProfile;
use App\Models\Category;
use App\Models\Event;
use App\Models\Group;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $event = Event::factory()->create(['category_id' => $category->id]);

        $this->assertTrue($event->category->is($category));
    }

    public function test_organizador_resolves_artist_profile(): void
    {
        $event = Event::factory()->create();

        $this->assertInstanceOf(ArtistProfile::class, $event->organizador);
    }

    public function test_organizador_resolves_group(): void
    {
        $event = Event::factory()->porGrupo()->create();

        $this->assertInstanceOf(Group::class, $event->organizador);
    }

    public function test_has_many_sessions_and_media(): void
    {
        $event = Event::factory()->create();
        $session = $event->sessions()->create([
            'venue_id' => Venue::factory()->create()->id,
            'evs_data_inicio' => now(),
            'evs_data_fim' => now()->addHours(3),
        ]);
        $media = $event->media()->create([
            'evm_tipo' => EventMediaType::FOTO,
            'evm_url' => 'https://example.com/foto.jpg',
        ]);

        $this->assertTrue($event->sessions->contains($session));
        $this->assertTrue($event->media->contains($media));
    }

    public function test_belongs_to_many_artists_and_groups(): void
    {
        $event = Event::factory()->create();
        $artistProfile = ArtistProfile::factory()->create();
        $group = Group::factory()->create();

        $event->artists()->attach($artistProfile);
        $event->groups()->attach($group);

        $this->assertTrue($event->artists->contains($artistProfile));
        $this->assertTrue($event->groups->contains($group));
        $this->assertTrue($artistProfile->events->contains($event));
        $this->assertTrue($group->events->contains($event));
    }

    public function test_casts_eve_status_to_enum(): void
    {
        $event = Event::factory()->publicado()->create();

        $this->assertSame(EventStatus::PUBLICADO, $event->eve_status);
    }
}
