<?php

namespace Tests\Feature\Models;

use App\Models\ArtistProfile;
use App\Models\FeedPost;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_autor_resolves_artist_profile(): void
    {
        $feedPost = FeedPost::factory()->create();

        $this->assertInstanceOf(ArtistProfile::class, $feedPost->autor);
    }

    public function test_autor_resolves_group(): void
    {
        $feedPost = FeedPost::factory()->porGroup()->create();

        $this->assertInstanceOf(Group::class, $feedPost->autor);
    }

    public function test_fee_tipo_is_string_and_fee_active_casts_to_boolean(): void
    {
        $feedPost = FeedPost::factory()->create([
            'fee_tipo' => 'FOTO',
            'fee_active' => 1,
        ]);

        $this->assertSame('FOTO', $feedPost->fee_tipo);
        $this->assertTrue($feedPost->fee_active);
    }

    public function test_inverse_relations_on_artist_profile_and_group(): void
    {
        $artistProfile = ArtistProfile::factory()->create();
        $group = Group::factory()->create();

        $artistPost = FeedPost::factory()->create([
            'autor_type' => ArtistProfile::class,
            'autor_id' => $artistProfile->id,
        ]);
        $groupPost = FeedPost::factory()->create([
            'autor_type' => Group::class,
            'autor_id' => $group->id,
        ]);

        $this->assertTrue($artistProfile->feedPosts->contains($artistPost));
        $this->assertTrue($group->feedPosts->contains($groupPost));
    }
}
