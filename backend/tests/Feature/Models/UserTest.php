<?php

namespace Tests\Feature\Models;

use App\Enums\GroupMemberRole;
use App\Enums\TipoConta;
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

        $group->members()->attach($user->id, ['grm_papel' => GroupMemberRole::MEMBRO]);

        $this->assertTrue($user->groups->contains($group));
    }

    public function test_casts_use_tipo_conta_to_enum(): void
    {
        $user = User::factory()->create(['use_tipo_conta' => 'empresa']);

        $this->assertSame(TipoConta::EMPRESA, $user->use_tipo_conta);
    }
}
