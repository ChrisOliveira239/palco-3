<?php

namespace Tests\Feature\Follows;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{0: \Illuminate\Database\Eloquent\Model, 1: User, 2: string}>
     */
    protected function alvoScenarios(): array
    {
        $artistProfileOwner = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($artistProfileOwner)->create();

        $groupOwner = User::factory()->create();
        $group = Group::factory()->for($groupOwner, 'owner')->create();

        $eventOrganizerOwner = User::factory()->create();
        $eventArtistProfile = ArtistProfile::factory()->for($eventOrganizerOwner)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $eventArtistProfile->id,
        ]);

        return [
            [$artistProfile, $artistProfileOwner, 'artist-profiles'],
            [$group, $groupOwner, 'groups'],
            [$event, $eventOrganizerOwner, 'events'],
        ];
    }

    public function test_authenticated_user_follows_each_alvo_type(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, , $prefix]) {
            Sanctum::actingAs(User::factory()->create());

            $this->postJson("/api/{$prefix}/{$alvo->id}/follow")->assertCreated();
        }
    }

    public function test_owner_cannot_follow_own_alvo(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, $owner, $prefix]) {
            Sanctum::actingAs($owner);

            $this->postJson("/api/{$prefix}/{$alvo->id}/follow")->assertStatus(422);
        }
    }

    public function test_guest_cannot_follow(): void
    {
        [$alvo, , $prefix] = $this->alvoScenarios()[0];

        $this->postJson("/api/{$prefix}/{$alvo->id}/follow")->assertUnauthorized();
    }

    public function test_following_same_alvo_twice_fails(): void
    {
        [$alvo, , $prefix] = $this->alvoScenarios()[0];
        $user = User::factory()->create();
        Follow::factory()->create(['user_id' => $user->id, 'seguivel_type' => get_class($alvo), 'seguivel_id' => $alvo->id]);
        Sanctum::actingAs($user);

        $this->postJson("/api/{$prefix}/{$alvo->id}/follow")->assertStatus(422);
    }

    public function test_user_unfollows_alvo(): void
    {
        [$alvo, , $prefix] = $this->alvoScenarios()[0];
        $user = User::factory()->create();
        $follow = Follow::factory()->create(['user_id' => $user->id, 'seguivel_type' => get_class($alvo), 'seguivel_id' => $alvo->id]);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/{$prefix}/{$alvo->id}/follow")->assertOk();

        $this->assertDatabaseMissing('follows', ['id' => $follow->id]);
    }

    public function test_unfollowing_alvo_not_followed_returns_404(): void
    {
        [$alvo, , $prefix] = $this->alvoScenarios()[0];
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/{$prefix}/{$alvo->id}/follow")->assertNotFound();
    }

    public function test_guest_lists_followers_of_alvo(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, , $prefix]) {
            Follow::factory()->count(2)->create(['seguivel_type' => get_class($alvo), 'seguivel_id' => $alvo->id]);

            $response = $this->getJson("/api/{$prefix}/{$alvo->id}/followers")->assertOk();

            $this->assertCount(2, $response->json('followers'));
        }
    }

    public function test_user_lists_who_they_follow(): void
    {
        [$alvo] = $this->alvoScenarios()[0];
        $user = User::factory()->create();
        $follow = Follow::factory()->create(['user_id' => $user->id, 'seguivel_type' => get_class($alvo), 'seguivel_id' => $alvo->id]);
        Follow::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/following')->assertOk();

        $ids = collect($response->json('follows'))->pluck('id');

        $this->assertEquals([$follow->id], $ids->all());
    }
}
