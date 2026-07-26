<?php

namespace Tests\Feature\Models;

use App\Models\ArtistProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtistProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user_and_user_has_one_artist_profile(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($artistProfile->user->is($user));
        $this->assertTrue($user->artistProfile->is($artistProfile));
    }

    public function test_verificado_state(): void
    {
        $artistProfile = ArtistProfile::factory()->verificado()->create();

        $this->assertTrue($artistProfile->art_verificado);
    }
}
