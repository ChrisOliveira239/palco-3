<?php

namespace Tests\Feature\Models;

use App\Enums\GroupMemberRole;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_owner(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($group->owner->is($owner));
    }

    public function test_members_belongs_to_many_with_role_pivot(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();

        $group->members()->attach($member->id, ['grm_papel' => GroupMemberRole::ADMIN]);

        $fetched = $group->members()->first();

        $this->assertTrue($fetched->is($member));
        $this->assertSame(GroupMemberRole::ADMIN, $fetched->pivot->grm_papel);
        $this->assertTrue($member->groups->contains($group));

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $member->id,
            'grm_papel' => 'admin',
        ]);
    }
}
