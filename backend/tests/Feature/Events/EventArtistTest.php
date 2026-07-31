<?php

namespace Tests\Feature\Events;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventArtistTest extends TestCase
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

    protected function invitedArtist(): array
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();

        return [$user, $artistProfile];
    }

    public function test_guest_lists_only_accepted_artists(): void
    {
        $event = Event::factory()->create();
        $accepted = ArtistProfile::factory()->create();
        $pending = ArtistProfile::factory()->create();
        $event->artists()->attach($accepted->id, ['eva_status' => 'ACEITO']);
        $event->artists()->attach($pending->id, ['eva_status' => 'PENDENTE']);

        $response = $this->getJson("/api/events/{$event->id}/artists")->assertOk();

        $ids = collect($response->json('artists'))->pluck('id');

        $this->assertEquals([$accepted->id], $ids->all());
    }

    public function test_organizer_can_invite_artist_as_pendente(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        [, $artistProfile] = $this->invitedArtist();
        Sanctum::actingAs($organizerUser);

        $this->postJson("/api/events/{$event->id}/artists", [
            'artist_profile_id' => $artistProfile->id,
        ])->assertCreated();

        $this->assertDatabaseHas('event_artist', [
            'event_id' => $event->id,
            'artist_profile_id' => $artistProfile->id,
            'eva_status' => 'PENDENTE',
        ]);
    }

    public function test_invite_auto_accepts_artist_member_of_organizers_group(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        $ownedGroup = Group::factory()->create(['user_id' => $organizerUser->id]);
        $memberUser = User::factory()->create();
        $ownedGroup->members()->attach($memberUser->id, ['grm_papel' => 'MEMBRO']);
        $memberArtistProfile = ArtistProfile::factory()->for($memberUser)->create();
        Sanctum::actingAs($organizerUser);

        $this->postJson("/api/events/{$event->id}/artists", [
            'artist_profile_id' => $memberArtistProfile->id,
        ])->assertCreated();

        $this->assertDatabaseHas('event_artist', [
            'event_id' => $event->id,
            'artist_profile_id' => $memberArtistProfile->id,
            'eva_status' => 'ACEITO',
        ]);
    }

    public function test_duplicate_invite_fails_validation(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        [, $artistProfile] = $this->invitedArtist();
        $event->artists()->attach($artistProfile->id, ['eva_status' => 'PENDENTE']);
        Sanctum::actingAs($organizerUser);

        $this->postJson("/api/events/{$event->id}/artists", [
            'artist_profile_id' => $artistProfile->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('artist_profile_id');
    }

    public function test_non_organizer_cannot_invite(): void
    {
        $event = Event::factory()->create();
        [, $artistProfile] = $this->invitedArtist();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/artists", [
            'artist_profile_id' => $artistProfile->id,
        ])->assertForbidden();
    }

    public function test_guest_cannot_invite(): void
    {
        $event = Event::factory()->create();
        [, $artistProfile] = $this->invitedArtist();

        $this->postJson("/api/events/{$event->id}/artists", [
            'artist_profile_id' => $artistProfile->id,
        ])->assertUnauthorized();
    }

    public function test_invited_artist_can_accept_own_invite(): void
    {
        [, $event] = $this->organizerEvent();
        [$artistUser, $artistProfile] = $this->invitedArtist();
        $event->artists()->attach($artistProfile->id, ['eva_status' => 'PENDENTE']);
        Sanctum::actingAs($artistUser);

        $this->patchJson("/api/events/{$event->id}/artists/{$artistProfile->id}", [
            'eva_status' => 'ACEITO',
        ])->assertOk();

        $this->assertDatabaseHas('event_artist', [
            'event_id' => $event->id,
            'artist_profile_id' => $artistProfile->id,
            'eva_status' => 'ACEITO',
        ]);
    }

    public function test_invited_artist_can_decline_own_invite(): void
    {
        [, $event] = $this->organizerEvent();
        [$artistUser, $artistProfile] = $this->invitedArtist();
        $event->artists()->attach($artistProfile->id, ['eva_status' => 'PENDENTE']);
        Sanctum::actingAs($artistUser);

        $this->patchJson("/api/events/{$event->id}/artists/{$artistProfile->id}", [
            'eva_status' => 'RECUSADO',
        ])->assertOk();

        $this->assertDatabaseHas('event_artist', [
            'event_id' => $event->id,
            'artist_profile_id' => $artistProfile->id,
            'eva_status' => 'RECUSADO',
        ]);
    }

    public function test_other_user_cannot_accept_or_decline_others_invite(): void
    {
        [, $event] = $this->organizerEvent();
        [, $artistProfile] = $this->invitedArtist();
        $event->artists()->attach($artistProfile->id, ['eva_status' => 'PENDENTE']);
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/events/{$event->id}/artists/{$artistProfile->id}", [
            'eva_status' => 'ACEITO',
        ])->assertForbidden();
    }

    public function test_organizer_can_remove_participant(): void
    {
        [$organizerUser, $event] = $this->organizerEvent();
        [, $artistProfile] = $this->invitedArtist();
        $event->artists()->attach($artistProfile->id, ['eva_status' => 'ACEITO']);
        Sanctum::actingAs($organizerUser);

        $this->deleteJson("/api/events/{$event->id}/artists/{$artistProfile->id}")->assertOk();

        $this->assertDatabaseMissing('event_artist', [
            'event_id' => $event->id,
            'artist_profile_id' => $artistProfile->id,
        ]);
    }

    public function test_participant_can_remove_self(): void
    {
        [, $event] = $this->organizerEvent();
        [$artistUser, $artistProfile] = $this->invitedArtist();
        $event->artists()->attach($artistProfile->id, ['eva_status' => 'ACEITO']);
        Sanctum::actingAs($artistUser);

        $this->deleteJson("/api/events/{$event->id}/artists/{$artistProfile->id}")->assertOk();

        $this->assertDatabaseMissing('event_artist', [
            'event_id' => $event->id,
            'artist_profile_id' => $artistProfile->id,
        ]);
    }

    public function test_third_party_cannot_remove_participant(): void
    {
        [, $event] = $this->organizerEvent();
        [, $artistProfile] = $this->invitedArtist();
        $event->artists()->attach($artistProfile->id, ['eva_status' => 'ACEITO']);
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/events/{$event->id}/artists/{$artistProfile->id}")->assertForbidden();
    }

    public function test_artist_from_another_event_returns_404(): void
    {
        [, $event] = $this->organizerEvent();
        $otherEvent = Event::factory()->create();
        [$artistUser, $artistProfile] = $this->invitedArtist();
        $otherEvent->artists()->attach($artistProfile->id, ['eva_status' => 'PENDENTE']);
        Sanctum::actingAs($artistUser);

        $this->patchJson("/api/events/{$event->id}/artists/{$artistProfile->id}", [
            'eva_status' => 'ACEITO',
        ])->assertNotFound();
    }
}
