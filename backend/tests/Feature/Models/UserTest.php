<?php

namespace Tests\Feature\Models;

use App\Models\ArtistProfile;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_one_artist_profile(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->artistProfile->is($artistProfile));
    }

    public function test_owned_groups(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->ownedGroups->contains($group));
    }

    public function test_groups_membership(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();

        $group->members()->attach($user->id, ['grm_papel' => 'MEMBRO']);

        $this->assertTrue($user->groups->contains($group));
    }

    public function test_use_tipo_conta_is_string(): void
    {
        $user = User::factory()->create(['use_tipo_conta' => 'EMPRESA']);

        $this->assertSame('EMPRESA', $user->use_tipo_conta);
    }
}
