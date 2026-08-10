<?php

namespace Tests\Feature\Favorites;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    protected function publishedEvent(): Event
    {
        $owner = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($owner)->create();

        return Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
            'eve_status' => 'PUBLICADO',
            'eve_active' => true,
        ]);
    }

    public function test_authenticated_user_favorites_published_event(): void
    {
        $event = $this->publishedEvent();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/favorite")->assertCreated();
    }

    public function test_guest_cannot_favorite(): void
    {
        $event = $this->publishedEvent();

        $this->postJson("/api/events/{$event->id}/favorite")->assertUnauthorized();
    }

    public function test_favoriting_same_event_twice_fails(): void
    {
        $event = $this->publishedEvent();
        $user = User::factory()->create();
        $user->favoriteEvents()->attach($event->id);
        Sanctum::actingAs($user);

        $this->postJson("/api/events/{$event->id}/favorite")->assertStatus(422);
    }

    public function test_cannot_favorite_pending_event(): void
    {
        $event = $this->publishedEvent();
        $event->update(['eve_status' => 'PENDENTE']);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/favorite")->assertNotFound();
    }

    public function test_cannot_favorite_inactive_event(): void
    {
        $event = $this->publishedEvent();
        $event->update(['eve_active' => false]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/favorite")->assertNotFound();
    }

    public function test_organizer_can_favorite_own_event(): void
    {
        $owner = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($owner)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
            'eve_status' => 'PUBLICADO',
            'eve_active' => true,
        ]);
        Sanctum::actingAs($owner);

        $this->postJson("/api/events/{$event->id}/favorite")->assertCreated();
    }

    public function test_user_unfavorites_event(): void
    {
        $event = $this->publishedEvent();
        $user = User::factory()->create();
        $user->favoriteEvents()->attach($event->id);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/events/{$event->id}/favorite")->assertOk();

        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'event_id' => $event->id]);
    }

    public function test_unfavoriting_event_not_favorited_returns_404(): void
    {
        $event = $this->publishedEvent();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/events/{$event->id}/favorite")->assertNotFound();
    }

    public function test_user_lists_only_own_favorites(): void
    {
        $event = $this->publishedEvent();
        $user = User::factory()->create();
        $user->favoriteEvents()->attach($event->id);
        User::factory()->create()->favoriteEvents()->attach($this->publishedEvent()->id);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/favorites')->assertOk();

        $ids = collect($response->json('favorites'))->pluck('id');

        $this->assertEquals([$event->id], $ids->all());
    }
}
