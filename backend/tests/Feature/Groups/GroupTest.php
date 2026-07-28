<?php

namespace Tests\Feature\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_active_groups(): void
    {
        $active = Group::factory()->create();
        Group::factory()->create(['gro_active' => false]);

        $response = $this->getJson('/api/groups')->assertOk();

        $ids = collect($response->json('groups.data'))->pluck('id');

        $this->assertTrue($ids->contains($active->id));
        $this->assertCount(1, $ids);
    }

    public function test_guest_can_view_active_group(): void
    {
        $group = Group::factory()->create();

        $this->getJson("/api/groups/{$group->id}")
            ->assertOk()
            ->assertJsonPath('group.id', $group->id);
    }

    public function test_inactive_group_returns_404(): void
    {
        $group = Group::factory()->create(['gro_active' => false]);

        $this->getJson("/api/groups/{$group->id}")->assertNotFound();
    }

    public function test_authenticated_user_can_create_group(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/groups', ['gro_nome' => 'Grupo Teste']);

        $response->assertCreated()->assertJsonPath('group.user_id', $user->id);

        $this->assertDatabaseHas('groups', [
            'user_id' => $user->id,
            'gro_nome' => 'Grupo Teste',
        ]);
    }

    public function test_owner_can_update_group(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/groups/{$group->id}", ['gro_nome' => 'Nome Atualizado'])
            ->assertOk()
            ->assertJsonPath('group.gro_nome', 'Nome Atualizado');
    }

    public function test_admin_member_can_update_group(): void
    {
        $group = Group::factory()->create();
        $admin = User::factory()->create();
        $group->members()->attach($admin->id, ['grm_papel' => 'ADMIN']);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/groups/{$group->id}", ['gro_nome' => 'Nome Atualizado'])
            ->assertOk();
    }

    public function test_regular_member_cannot_update_group(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);
        Sanctum::actingAs($member);

        $this->patchJson("/api/groups/{$group->id}", ['gro_nome' => 'Tentativa'])
            ->assertForbidden();
    }

    public function test_non_member_cannot_update_group(): void
    {
        $group = Group::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/groups/{$group->id}", ['gro_nome' => 'Tentativa'])
            ->assertForbidden();
    }

    public function test_owner_can_deactivate_group(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($owner);

        $this->deleteJson("/api/groups/{$group->id}")->assertOk();

        $this->assertDatabaseHas('groups', ['id' => $group->id, 'gro_active' => false]);
    }

    public function test_admin_member_cannot_deactivate_group(): void
    {
        $group = Group::factory()->create();
        $admin = User::factory()->create();
        $group->members()->attach($admin->id, ['grm_papel' => 'ADMIN']);
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/groups/{$group->id}")->assertForbidden();
    }

    public function test_guest_cannot_create_update_or_delete_group(): void
    {
        $group = Group::factory()->create();

        $this->postJson('/api/groups', ['gro_nome' => 'X'])->assertUnauthorized();
        $this->patchJson("/api/groups/{$group->id}", ['gro_nome' => 'X'])->assertUnauthorized();
        $this->deleteJson("/api/groups/{$group->id}")->assertUnauthorized();
    }
}
