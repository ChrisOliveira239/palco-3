<?php

namespace Tests\Feature\Models;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_favorites_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $user->favoriteEvents()->attach($event);

        $this->assertTrue($user->favoriteEvents->contains($event));
    }

    public function test_event_favorited_by_resolves_user(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $user->favoriteEvents()->attach($event);

        $this->assertTrue($event->favoritedBy->contains($user));
    }
}
