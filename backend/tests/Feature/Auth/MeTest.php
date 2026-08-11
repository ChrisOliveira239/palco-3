<?php

namespace Tests\Feature\Auth;

use App\Models\ArtistProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_own_profile(): void
    {
        $user = User::factory()->create(['email' => 'eu@example.com']);
        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'eu@example.com');
    }

    public function test_guest_cannot_access_me(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_me_returns_null_artist_profile_when_user_has_none(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.artist_profile', null);
    }

    public function test_me_returns_artist_profile_when_user_has_one(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.artist_profile.id', $artistProfile->id);
    }
}
