<?php

namespace Tests\Feature\Feed;

use App\Models\ArtistProfile;
use App\Models\FeedPost;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FeedPostTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{0: string, 1: \Illuminate\Database\Eloquent\Model, 2: User}>
     */
    protected function criadorScenarios(): array
    {
        $artistProfileOwner = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($artistProfileOwner)->create();

        $groupOwner = User::factory()->create();
        $group = Group::factory()->for($groupOwner, 'owner')->create();

        return [
            ['artist-profiles', $artistProfile, $artistProfileOwner],
            ['groups', $group, $groupOwner],
        ];
    }

    public function test_guest_lists_only_active_posts_of_autor(): void
    {
        foreach ($this->criadorScenarios() as [$prefix, $autor]) {
            FeedPost::factory()->count(2)->create(['autor_type' => get_class($autor), 'autor_id' => $autor->id]);
            FeedPost::factory()->create(['autor_type' => get_class($autor), 'autor_id' => $autor->id, 'fee_active' => false]);

            $response = $this->getJson("/api/{$prefix}/{$autor->id}/feed-posts")->assertOk();

            $this->assertCount(2, $response->json('feed_posts'));
        }
    }

    public function test_owner_publishes_atualizacao_without_midia(): void
    {
        foreach ($this->criadorScenarios() as [$prefix, $autor, $owner]) {
            Sanctum::actingAs($owner);

            $this->postJson("/api/{$prefix}/{$autor->id}/feed-posts", [
                'fee_tipo' => 'ATUALIZACAO',
                'fee_conteudo' => 'Novidade por aí.',
            ])->assertCreated();
        }
    }

    public function test_publishing_foto_without_midia_url_fails_validation(): void
    {
        [$prefix, $autor, $owner] = $this->criadorScenarios()[0];
        Sanctum::actingAs($owner);

        $this->postJson("/api/{$prefix}/{$autor->id}/feed-posts", ['fee_tipo' => 'FOTO'])
            ->assertStatus(422);
    }

    public function test_publishing_foto_with_midia_url_succeeds(): void
    {
        [$prefix, $autor, $owner] = $this->criadorScenarios()[0];
        Sanctum::actingAs($owner);

        $this->postJson("/api/{$prefix}/{$autor->id}/feed-posts", [
            'fee_tipo' => 'FOTO',
            'fee_midia_url' => 'https://example.com/foto.jpg',
        ])->assertCreated();
    }

    public function test_non_owner_cannot_publish(): void
    {
        foreach ($this->criadorScenarios() as [$prefix, $autor]) {
            Sanctum::actingAs(User::factory()->create());

            $this->postJson("/api/{$prefix}/{$autor->id}/feed-posts", ['fee_tipo' => 'ATUALIZACAO'])
                ->assertForbidden();
        }
    }

    public function test_guest_cannot_publish(): void
    {
        [$prefix, $autor] = $this->criadorScenarios()[0];

        $this->postJson("/api/{$prefix}/{$autor->id}/feed-posts", ['fee_tipo' => 'ATUALIZACAO'])
            ->assertUnauthorized();
    }

    public function test_owner_updates_own_post_content(): void
    {
        foreach ($this->criadorScenarios() as [$prefix, $autor, $owner]) {
            $post = FeedPost::factory()->create(['autor_type' => get_class($autor), 'autor_id' => $autor->id]);
            Sanctum::actingAs($owner);

            $this->patchJson("/api/{$prefix}/{$autor->id}/feed-posts/{$post->id}", ['fee_conteudo' => 'Editado.'])
                ->assertOk()
                ->assertJsonPath('feed_post.fee_conteudo', 'Editado.');
        }
    }

    public function test_non_owner_cannot_update_post(): void
    {
        [$prefix, $autor] = $this->criadorScenarios()[0];
        $post = FeedPost::factory()->create(['autor_type' => get_class($autor), 'autor_id' => $autor->id]);
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/{$prefix}/{$autor->id}/feed-posts/{$post->id}", ['fee_conteudo' => 'Editado.'])
            ->assertForbidden();
    }

    public function test_updating_post_from_another_autor_returns_404(): void
    {
        [$prefix, $autor, $owner] = $this->criadorScenarios()[0];
        $otherAutor = $prefix === 'artist-profiles'
            ? ArtistProfile::factory()->create()
            : Group::factory()->create();
        $post = FeedPost::factory()->create(['autor_type' => get_class($otherAutor), 'autor_id' => $otherAutor->id]);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/{$prefix}/{$autor->id}/feed-posts/{$post->id}", ['fee_conteudo' => 'Editado.'])
            ->assertNotFound();
    }

    public function test_owner_deactivates_post(): void
    {
        foreach ($this->criadorScenarios() as [$prefix, $autor, $owner]) {
            $post = FeedPost::factory()->create(['autor_type' => get_class($autor), 'autor_id' => $autor->id]);
            Sanctum::actingAs($owner);

            $this->deleteJson("/api/{$prefix}/{$autor->id}/feed-posts/{$post->id}")->assertOk();

            $this->assertDatabaseHas('feed_posts', ['id' => $post->id, 'fee_active' => false]);
        }
    }

    public function test_non_owner_cannot_deactivate_post(): void
    {
        [$prefix, $autor] = $this->criadorScenarios()[0];
        $post = FeedPost::factory()->create(['autor_type' => get_class($autor), 'autor_id' => $autor->id]);
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/{$prefix}/{$autor->id}/feed-posts/{$post->id}")->assertForbidden();
    }
}
