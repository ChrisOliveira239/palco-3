<?php

namespace Tests\Feature\Events;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventGroupTest extends TestCase
{
    use RefreshDatabase;

    protected function organizerEvent(): array
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
        ]);

        return [$user, $event];
    }

    public function test_guest_lists_only_accepted_groups(): void
    {
        $event = Event::factory()->create();
        $accepted = Group::factory()->create();
        $pending = Group::factory()->create();
        $event->groups()->attach($accepted->id, ['evg_status' => 'ACEITO']);
        $event->groups()->attach($pending->id, ['evg_status' => 'PENDENTE']);

        $response = $this->getJson("/api/events/{$event->id}/groups")->assertOk();

        $ids = collect($response->json('groups'))->pluck('id');

        $this->assertEquals([$accepted->id], $ids->all());
    }

    public function test_organizer_can_invite_group_as_pendente(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        $group = Group::factory()->create();
        Sanctum::actingAs($organizerUser);

        $this->postJson("/api/events/{$event->id}/groups", [
            'group_id' => $group->id,
        ])->assertCreated();

        $this->assertDatabaseHas('event_group', [
            'event_id' => $event->id,
            'group_id' => $group->id,
            'evg_status' => 'PENDENTE',
        ]);
    }

    public function test_invite_auto_accepts_organizers_own_group(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        $ownedGroup = Group::factory()->create(['user_id' => $organizerUser->id]);
        Sanctum::actingAs($organizerUser);

        $this->postJson("/api/events/{$event->id}/groups", [
            'group_id' => $ownedGroup->id,
        ])->assertCreated();

        $this->assertDatabaseHas('event_group', [
            'event_id' => $event->id,
            'group_id' => $ownedGroup->id,
            'evg_status' => 'ACEITO',
        ]);
    }

    public function test_invite_auto_accepts_group_where_organizer_is_admin(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        $adminGroup = Group::factory()->create();
        $adminGroup->members()->attach($organizerUser->id, ['grm_papel' => 'ADMIN']);
        Sanctum::actingAs($organizerUser);

        $this->postJson("/api/events/{$event->id}/groups", [
            'group_id' => $adminGroup->id,
        ])->assertCreated();

        $this->assertDatabaseHas('event_group', [
            'event_id' => $event->id,
            'group_id' => $adminGroup->id,
            'evg_status' => 'ACEITO',
        ]);
    }

    public function test_duplicate_invite_fails_validation(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        $group = Group::factory()->create();
        $event->groups()->attach($group->id, ['evg_status' => 'PENDENTE']);
        Sanctum::actingAs($organizerUser);

        $this->postJson("/api/events/{$event->id}/groups", [
            'group_id' => $group->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('group_id');
    }

    public function test_non_organizer_cannot_invite(): void
    {
        $event = Event::factory()->create();
        $group = Group::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/groups", [
            'group_id' => $group->id,
        ])->assertForbidden();
    }

    public function test_guest_cannot_invite(): void
    {
        $event = Event::factory()->create();
        $group = Group::factory()->create();

        $this->postJson("/api/events/{$event->id}/groups", [
            'group_id' => $group->id,
        ])->assertUnauthorized();
    }

    public function test_group_owner_can_accept_own_invite(): void
    {
        [, $event] = $this->organizerEvent();
        $ownerUser = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $ownerUser->id]);
        $event->groups()->attach($group->id, ['evg_status' => 'PENDENTE']);
        Sanctum::actingAs($ownerUser);

        $this->patchJson("/api/events/{$event->id}/groups/{$group->id}", [
            'evg_status' => 'ACEITO',
        ])->assertOk();

        $this->assertDatabaseHas('event_group', [
            'event_id' => $event->id,
            'group_id' => $group->id,
            'evg_status' => 'ACEITO',
        ]);
    }

    public function test_group_admin_can_decline_invite(): void
    {
        [, $event] = $this->organizerEvent();
        $adminUser = User::factory()->create();
        $group = Group::factory()->create();
        $group->members()->attach($adminUser->id, ['grm_papel' => 'ADMIN']);
        $event->groups()->attach($group->id, ['evg_status' => 'PENDENTE']);
        Sanctum::actingAs($adminUser);

        $this->patchJson("/api/events/{$event->id}/groups/{$group->id}", [
            'evg_status' => 'RECUSADO',
        ])->assertOk();

        $this->assertDatabaseHas('event_group', [
            'event_id' => $event->id,
            'group_id' => $group->id,
            'evg_status' => 'RECUSADO',
        ]);
    }

    public function test_other_user_cannot_accept_or_decline_others_invite(): void
    {
        [, $event] = $this->organizerEvent();
        $group = Group::factory()->create();
        $event->groups()->attach($group->id, ['evg_status' => 'PENDENTE']);
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/events/{$event->id}/groups/{$group->id}", [
            'evg_status' => 'ACEITO',
        ])->assertForbidden();
    }

    public function test_organizer_can_remove_participant(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        $group = Group::factory()->create();
        $event->groups()->attach($group->id, ['evg_status' => 'ACEITO']);
        Sanctum::actingAs($organizerUser);

        $this->deleteJson("/api/events/{$event->id}/groups/{$group->id}")->assertOk();

        $this->assertDatabaseMissing('event_group', [
            'event_id' => $event->id,
            'group_id' => $group->id,
        ]);
    }

    public function test_group_owner_can_remove_self(): void
    {
        [, $event] = $this->organizerEvent();
        $ownerUser = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $ownerUser->id]);
        $event->groups()->attach($group->id, ['evg_status' => 'ACEITO']);
        Sanctum::actingAs($ownerUser);

        $this->deleteJson("/api/events/{$event->id}/groups/{$group->id}")->assertOk();

        $this->assertDatabaseMissing('event_group', [
            'event_id' => $event->id,
            'group_id' => $group->id,
        ]);
    }

    public function test_third_party_cannot_remove_participant(): void
    {
        [, $event] = $this->organizerEvent();
        $group = Group::factory()->create();
        $event->groups()->attach($group->id, ['evg_status' => 'ACEITO']);
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/events/{$event->id}/groups/{$group->id}")->assertForbidden();
    }

    public function test_group_from_another_event_returns_404(): void
    {
        [, $event] = $this->organizerEvent();
        $otherEvent = Event::factory()->create();
        $ownerUser = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $ownerUser->id]);
        $otherEvent->groups()->attach($group->id, ['evg_status' => 'PENDENTE']);
        Sanctum::actingAs($ownerUser);

        $this->patchJson("/api/events/{$event->id}/groups/{$group->id}", [
            'evg_status' => 'ACEITO',
        ])->assertNotFound();
    }
}
