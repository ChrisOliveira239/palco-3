<?php

namespace Tests\Feature\Events;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventMediaTest extends TestCase
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

    public function test_guest_lists_active_media_ordered(): void
    {
        $event = Event::factory()->create();
        $second = EventMedia::factory()->for($event)->create(['evm_ordem' => 2]);
        $first = EventMedia::factory()->for($event)->create(['evm_ordem' => 1]);
        EventMedia::factory()->for($event)->create(['evm_active' => false]);

        $response = $this->getJson("/api/events/{$event->id}/media")->assertOk();

        $ids = collect($response->json('media'))->pluck('id');

        $this->assertCount(2, $ids);
        $this->assertEquals([$first->id, $second->id], $ids->all());
    }

    public function test_organizer_can_create_photo_media(): void
    {
        [$user, $event] = $this->organizerEvent();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/events/{$event->id}/media", [
            'evm_tipo' => 'FOTO',
            'evm_url' => 'https://example.com/foto.jpg',
        ]);

        $response->assertCreated()->assertJsonPath('media.evm_tipo', 'FOTO');
    }

    public function test_organizer_can_create_video_media(): void
    {
        [$user, $event] = $this->organizerEvent();
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/events/{$event->id}/media", [
            'evm_tipo' => 'VIDEO',
            'evm_url' => 'https://example.com/video.mp4',
        ]);

        $response->assertCreated()->assertJsonPath('media.evm_tipo', 'VIDEO');
    }

    public function test_invalid_evm_tipo_fails_validation(): void
    {
        [$user, $event] = $this->organizerEvent();
        Sanctum::actingAs($user);

        $this->postJson("/api/events/{$event->id}/media", [
            'evm_tipo' => 'AUDIO',
            'evm_url' => 'https://example.com/foto.jpg',
        ])->assertUnprocessable()->assertJsonValidationErrors('evm_tipo');
    }

    public function test_missing_evm_url_fails_validation(): void
    {
        [$user, $event] = $this->organizerEvent();
        Sanctum::actingAs($user);

        $this->postJson("/api/events/{$event->id}/media", [
            'evm_tipo' => 'FOTO',
        ])->assertUnprocessable()->assertJsonValidationErrors('evm_url');
    }

    public function test_non_organizer_cannot_create_media(): void
    {
        $event = Event::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/media", [
            'evm_tipo' => 'FOTO',
            'evm_url' => 'https://example.com/foto.jpg',
        ])->assertForbidden();
    }

    public function test_guest_cannot_create_media(): void
    {
        $event = Event::factory()->create();

        $this->postJson("/api/events/{$event->id}/media", [
            'evm_tipo' => 'FOTO',
            'evm_url' => 'https://example.com/foto.jpg',
        ])->assertUnauthorized();
    }

    public function test_organizer_can_update_own_media(): void
    {
        [$user, $event] = $this->organizerEvent();
        $media = EventMedia::factory()->for($event)->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/events/{$event->id}/media/{$media->id}", [
            'evm_ordem' => 5,
        ])->assertOk()->assertJsonPath('media.evm_ordem', 5);
    }

    public function test_organizer_can_deactivate_own_media(): void
    {
        [$user, $event] = $this->organizerEvent();
        $media = EventMedia::factory()->for($event)->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/events/{$event->id}/media/{$media->id}")->assertOk();

        $this->assertDatabaseHas('event_media', ['id' => $media->id, 'evm_active' => false]);
    }

    public function test_non_organizer_cannot_update_or_deactivate_media(): void
    {
        $event = Event::factory()->create();
        $media = EventMedia::factory()->for($event)->create();
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/events/{$event->id}/media/{$media->id}", [
            'evm_ordem' => 9,
        ])->assertForbidden();

        $this->deleteJson("/api/events/{$event->id}/media/{$media->id}")->assertForbidden();
    }

    public function test_media_from_another_event_returns_404(): void
    {
        [$user, $event] = $this->organizerEvent();
        $otherEvent = Event::factory()->create();
        $media = EventMedia::factory()->for($otherEvent)->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/events/{$event->id}/media/{$media->id}", [
            'evm_ordem' => 1,
        ])->assertNotFound();
    }
}
