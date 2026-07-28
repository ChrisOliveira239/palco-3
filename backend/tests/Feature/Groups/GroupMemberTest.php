<?php

namespace Tests\Feature\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GroupMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_members(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);

        $this->getJson("/api/groups/{$group->id}/members")->assertOk();
    }

    public function test_owner_can_add_member_with_default_role(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $owner->id]);
        $newMember = User::factory()->create();
        Sanctum::actingAs($owner);

        $this->postJson("/api/groups/{$group->id}/members", ['user_id' => $newMember->id])
            ->assertCreated();

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $newMember->id,
            'grm_papel' => 'MEMBRO',
        ]);
    }

    public function test_owner_can_add_member_with_explicit_admin_role(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $owner->id]);
        $newMember = User::factory()->create();
        Sanctum::actingAs($owner);

        $this->postJson("/api/groups/{$group->id}/members", [
            'user_id' => $newMember->id,
            'grm_papel' => 'ADMIN',
        ])->assertCreated();

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $newMember->id,
            'grm_papel' => 'ADMIN',
        ]);
    }

    public function test_admin_member_can_add_member(): void
    {
        $group = Group::factory()->create();
        $admin = User::factory()->create();
        $group->members()->attach($admin->id, ['grm_papel' => 'ADMIN']);
        $newMember = User::factory()->create();
        Sanctum::actingAs($admin);

        $this->postJson("/api/groups/{$group->id}/members", ['user_id' => $newMember->id])
            ->assertCreated();
    }

    public function test_regular_member_cannot_add_member(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);
        $newMember = User::factory()->create();
        Sanctum::actingAs($member);

        $this->postJson("/api/groups/{$group->id}/members", ['user_id' => $newMember->id])
            ->assertForbidden();
    }

    public function test_adding_existing_member_fails_validation(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $owner->id]);
        $existingMember = User::factory()->create();
        $group->members()->attach($existingMember->id, ['grm_papel' => 'MEMBRO']);
        Sanctum::actingAs($owner);

        $this->postJson("/api/groups/{$group->id}/members", ['user_id' => $existingMember->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('user_id');
    }

    public function test_owner_can_update_member_role(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $owner->id]);
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/groups/{$group->id}/members/{$member->id}", ['grm_papel' => 'ADMIN'])
            ->assertOk();

        $this->assertDatabaseHas('group_members', [
            'group_id' => $group->id,
            'user_id' => $member->id,
            'grm_papel' => 'ADMIN',
        ]);
    }

    public function test_regular_member_cannot_update_member_role(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);
        $otherMember = User::factory()->create();
        $group->members()->attach($otherMember->id, ['grm_papel' => 'MEMBRO']);
        Sanctum::actingAs($member);

        $this->patchJson("/api/groups/{$group->id}/members/{$otherMember->id}", ['grm_papel' => 'ADMIN'])
            ->assertForbidden();
    }

    public function test_member_can_remove_self(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);
        Sanctum::actingAs($member);

        $this->deleteJson("/api/groups/{$group->id}/members/{$member->id}")->assertOk();

        $this->assertDatabaseMissing('group_members', [
            'group_id' => $group->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_owner_can_remove_another_member(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $owner->id]);
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);
        Sanctum::actingAs($owner);

        $this->deleteJson("/api/groups/{$group->id}/members/{$member->id}")->assertOk();

        $this->assertDatabaseMissing('group_members', [
            'group_id' => $group->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_regular_member_cannot_remove_another_member(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);
        $otherMember = User::factory()->create();
        $group->members()->attach($otherMember->id, ['grm_papel' => 'MEMBRO']);
        Sanctum::actingAs($member);

        $this->deleteJson("/api/groups/{$group->id}/members/{$otherMember->id}")
            ->assertForbidden();
    }

    public function test_guest_cannot_add_update_or_remove_member(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);

        $this->postJson("/api/groups/{$group->id}/members", ['user_id' => $member->id])
            ->assertUnauthorized();
        $this->patchJson("/api/groups/{$group->id}/members/{$member->id}", ['grm_papel' => 'ADMIN'])
            ->assertUnauthorized();
        $this->deleteJson("/api/groups/{$group->id}/members/{$member->id}")->assertUnauthorized();
    }
}
