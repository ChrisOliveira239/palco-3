<?php

namespace Tests\Feature\Profiles;

use App\Models\ArtistProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArtistProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_active_artist_profiles(): void
    {
        $active = ArtistProfile::factory()->create();
        ArtistProfile::factory()->create(['art_active' => false]);

        $response = $this->getJson('/api/artist-profiles')->assertOk();

        $ids = collect($response->json('artist_profiles.data'))->pluck('id');

        $this->assertTrue($ids->contains($active->id));
        $this->assertCount(1, $ids);
    }

    public function test_guest_can_view_active_artist_profile(): void
    {
        $artistProfile = ArtistProfile::factory()->create();

        $this->getJson("/api/artist-profiles/{$artistProfile->id}")
            ->assertOk()
            ->assertJsonPath('artist_profile.id', $artistProfile->id);
    }

    public function test_inactive_artist_profile_returns_404(): void
    {
        $artistProfile = ArtistProfile::factory()->create(['art_active' => false]);

        $this->getJson("/api/artist-profiles/{$artistProfile->id}")->assertNotFound();
    }

    public function test_user_without_profile_can_create_one(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/artist-profiles', [
            'art_nome_artistico' => 'Artista Teste',
        ]);

        $response->assertCreated()->assertJsonPath('artist_profile.user_id', $user->id);

        $this->assertDatabaseHas('artist_profiles', [
            'user_id' => $user->id,
            'art_nome_artistico' => 'Artista Teste',
        ]);
    }

    public function test_user_with_profile_cannot_create_another(): void
    {
        $user = User::factory()->has(ArtistProfile::factory())->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/artist-profiles', [
            'art_nome_artistico' => 'Segundo Perfil',
        ])->assertForbidden();
    }

    public function test_store_ignores_art_verificado(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/artist-profiles', [
            'art_nome_artistico' => 'Artista Teste',
            'art_verificado' => true,
        ])->assertCreated();

        $this->assertDatabaseHas('artist_profiles', [
            'user_id' => $user->id,
            'art_verificado' => false,
        ]);
    }

    public function test_owner_can_update_own_profile(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->patchJson("/api/artist-profiles/{$artistProfile->id}", [
            'art_nome_artistico' => 'Nome Atualizado',
        ])->assertOk()->assertJsonPath('artist_profile.art_nome_artistico', 'Nome Atualizado');
    }

    public function test_non_owner_cannot_update_profile(): void
    {
        $artistProfile = ArtistProfile::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/artist-profiles/{$artistProfile->id}", [
            'art_nome_artistico' => 'Tentativa Alheia',
        ])->assertForbidden();
    }

    public function test_owner_can_deactivate_own_profile(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/artist-profiles/{$artistProfile->id}")->assertOk();

        $this->assertDatabaseHas('artist_profiles', [
            'id' => $artistProfile->id,
            'art_active' => false,
        ]);
    }

    public function test_non_owner_cannot_deactivate_profile(): void
    {
        $artistProfile = ArtistProfile::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/artist-profiles/{$artistProfile->id}")->assertForbidden();
    }

    public function test_guest_cannot_create_update_or_delete_profile(): void
    {
        $artistProfile = ArtistProfile::factory()->create();

        $this->postJson('/api/artist-profiles', ['art_nome_artistico' => 'X'])->assertUnauthorized();
        $this->patchJson("/api/artist-profiles/{$artistProfile->id}", ['art_nome_artistico' => 'X'])->assertUnauthorized();
        $this->deleteJson("/api/artist-profiles/{$artistProfile->id}")->assertUnauthorized();
    }
}
