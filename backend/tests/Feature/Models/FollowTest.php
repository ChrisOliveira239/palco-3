<?php

namespace Tests\Feature\Models;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resolves_user(): void
    {
        $follow = Follow::factory()->create();

        $this->assertInstanceOf(User::class, $follow->user);
    }

    public function test_seguivel_resolves_artist_profile(): void
    {
        $follow = Follow::factory()->create();

        $this->assertInstanceOf(ArtistProfile::class, $follow->seguivel);
    }

    public function test_seguivel_resolves_group(): void
    {
        $follow = Follow::factory()->porGroup()->create();

        $this->assertInstanceOf(Group::class, $follow->seguivel);
    }

    public function test_seguivel_resolves_event(): void
    {
        $follow = Follow::factory()->porEvent()->create();

        $this->assertInstanceOf(Event::class, $follow->seguivel);
    }

    public function test_inverse_relations(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->create();
        $group = Group::factory()->create();
        $event = Event::factory()->create();

        $followArtist = Follow::factory()->create([
            'user_id' => $user->id,
            'seguivel_type' => ArtistProfile::class,
            'seguivel_id' => $artistProfile->id,
        ]);
        $followGroup = Follow::factory()->create([
            'user_id' => $user->id,
            'seguivel_type' => Group::class,
            'seguivel_id' => $group->id,
        ]);
        $followEvent = Follow::factory()->create([
            'user_id' => $user->id,
            'seguivel_type' => Event::class,
            'seguivel_id' => $event->id,
        ]);

        $this->assertTrue($user->follows->contains($followArtist));
        $this->assertTrue($user->follows->contains($followGroup));
        $this->assertTrue($user->follows->contains($followEvent));
        $this->assertTrue($artistProfile->followers->contains($followArtist));
        $this->assertTrue($group->followers->contains($followGroup));
        $this->assertTrue($event->followers->contains($followEvent));
    }
}
