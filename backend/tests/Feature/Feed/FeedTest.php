<?php

namespace Tests\Feature\Feed;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\FeedPost;
use App\Models\Follow;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_posts_only_from_followed_artist_profile(): void
    {
        $followed = ArtistProfile::factory()->create();
        $notFollowed = ArtistProfile::factory()->create();
        $followedPost = FeedPost::factory()->create(['autor_type' => ArtistProfile::class, 'autor_id' => $followed->id]);
        FeedPost::factory()->create(['autor_type' => ArtistProfile::class, 'autor_id' => $notFollowed->id]);

        $user = User::factory()->create();
        Follow::factory()->create(['user_id' => $user->id, 'seguivel_type' => ArtistProfile::class, 'seguivel_id' => $followed->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/feed')->assertOk();

        $ids = collect($response->json('feed.data'))->pluck('id');

        $this->assertEquals([$followedPost->id], $ids->all());
    }

    public function test_user_sees_posts_from_followed_group(): void
    {
        $group = Group::factory()->create();
        $post = FeedPost::factory()->create(['autor_type' => Group::class, 'autor_id' => $group->id]);

        $user = User::factory()->create();
        Follow::factory()->create(['user_id' => $user->id, 'seguivel_type' => Group::class, 'seguivel_id' => $group->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/feed')->assertOk();

        $ids = collect($response->json('feed.data'))->pluck('id');

        $this->assertEquals([$post->id], $ids->all());
    }

    public function test_inactive_post_from_followed_author_does_not_appear(): void
    {
        $followed = ArtistProfile::factory()->create();
        FeedPost::factory()->create(['autor_type' => ArtistProfile::class, 'autor_id' => $followed->id, 'fee_active' => false]);

        $user = User::factory()->create();
        Follow::factory()->create(['user_id' => $user->id, 'seguivel_type' => ArtistProfile::class, 'seguivel_id' => $followed->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/feed')->assertOk();

        $this->assertCount(0, $response->json('feed.data'));
    }

    public function test_user_with_no_follows_gets_empty_feed(): void
    {
        FeedPost::factory()->count(3)->create();
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson('/api/feed')->assertOk();

        $this->assertCount(0, $response->json('feed.data'));
    }

    public function test_guest_cannot_access_feed(): void
    {
        $this->getJson('/api/feed')->assertUnauthorized();
    }

    public function test_following_an_event_does_not_bring_any_post(): void
    {
        $owner = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($owner)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
        ]);
        FeedPost::factory()->create(['autor_type' => ArtistProfile::class, 'autor_id' => $artistProfile->id]);

        $user = User::factory()->create();
        Follow::factory()->create(['user_id' => $user->id, 'seguivel_type' => Event::class, 'seguivel_id' => $event->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/feed')->assertOk();

        $this->assertCount(0, $response->json('feed.data'));
    }
}
