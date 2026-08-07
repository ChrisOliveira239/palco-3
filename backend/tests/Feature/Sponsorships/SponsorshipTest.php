<?php

namespace Tests\Feature\Sponsorships;

use App\Models\AcceptedSupportType;
use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use App\Models\Sponsorship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SponsorshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{0: \Illuminate\Database\Eloquent\Model, 1: User, 2: string}>
     */
    protected function alvoScenarios(): array
    {
        $eventOwner = User::factory()->create();
        $eventArtistProfile = ArtistProfile::factory()->for($eventOwner)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $eventArtistProfile->id,
        ]);

        $artistProfileOwner = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($artistProfileOwner)->create();

        $groupOwner = User::factory()->create();
        $group = Group::factory()->for($groupOwner, 'owner')->create();

        return [
            [$event, $eventOwner, 'events'],
            [$artistProfile, $artistProfileOwner, 'artist-profiles'],
            [$group, $groupOwner, 'groups'],
        ];
    }

    public function test_guest_lists_only_accepted_sponsorships(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, , $prefix]) {
            Sponsorship::factory()->create(['alvo_type' => get_class($alvo), 'alvo_id' => $alvo->id, 'spo_status' => 'ACEITO']);
            Sponsorship::factory()->create(['alvo_type' => get_class($alvo), 'alvo_id' => $alvo->id, 'spo_status' => 'PROPOSTO']);
            Sponsorship::factory()->create(['alvo_type' => get_class($alvo), 'alvo_id' => $alvo->id, 'spo_status' => 'RECUSADO']);

            $response = $this->getJson("/api/{$prefix}/{$alvo->id}/sponsorships")->assertOk();

            $this->assertCount(1, $response->json('sponsorships'));
        }
    }

    public function test_authenticated_user_creates_sponsorship_proposal(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, , $prefix]) {
            Sanctum::actingAs(User::factory()->create());

            $this->postJson("/api/{$prefix}/{$alvo->id}/sponsorships", [
                'spo_tipo_apoio' => 'FOTOGRAFIA',
            ])->assertCreated()
                ->assertJsonPath('sponsorship.spo_status', 'PROPOSTO')
                ->assertJsonPath('sponsorship.spo_active', true);
        }
    }

    public function test_tipo_apoio_not_accepted_by_alvo_fails_validation(): void
    {
        [$event] = $this->alvoScenarios()[0];
        AcceptedSupportType::factory()->create(['alvo_type' => Event::class, 'alvo_id' => $event->id, 'ast_tipo_apoio' => 'DINHEIRO']);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/sponsorships", ['spo_tipo_apoio' => 'SOM'])
            ->assertUnprocessable()->assertJsonValidationErrors('spo_tipo_apoio');
    }

    public function test_tipo_apoio_allowed_when_alvo_has_no_accepted_types_configured(): void
    {
        [$event] = $this->alvoScenarios()[0];
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/sponsorships", ['spo_tipo_apoio' => 'SOM'])->assertCreated();
    }

    public function test_dinheiro_without_valor_fails_validation(): void
    {
        [$event] = $this->alvoScenarios()[0];
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/events/{$event->id}/sponsorships", ['spo_tipo_apoio' => 'DINHEIRO'])
            ->assertUnprocessable()->assertJsonValidationErrors('spo_valor');
    }

    public function test_guest_cannot_create_sponsorship(): void
    {
        [$event] = $this->alvoScenarios()[0];

        $this->postJson("/api/events/{$event->id}/sponsorships", ['spo_tipo_apoio' => 'SOM'])->assertUnauthorized();
    }

    public function test_owner_lists_pending_sponsorships(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, $owner, $prefix]) {
            Sponsorship::factory()->create(['alvo_type' => get_class($alvo), 'alvo_id' => $alvo->id, 'spo_status' => 'PROPOSTO']);
            Sponsorship::factory()->create(['alvo_type' => get_class($alvo), 'alvo_id' => $alvo->id, 'spo_status' => 'ACEITO']);
            Sanctum::actingAs($owner);

            $response = $this->getJson("/api/{$prefix}/{$alvo->id}/sponsorships/pending")->assertOk();

            $this->assertCount(1, $response->json('sponsorships'));
        }
    }

    public function test_non_owner_cannot_list_pending_sponsorships(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, , $prefix]) {
            Sanctum::actingAs(User::factory()->create());

            $this->getJson("/api/{$prefix}/{$alvo->id}/sponsorships/pending")->assertForbidden();
        }
    }

    public function test_owner_accepts_and_rejects_sponsorship(): void
    {
        [$event, $owner] = $this->alvoScenarios()[0];
        $proposal = Sponsorship::factory()->create(['alvo_type' => Event::class, 'alvo_id' => $event->id]);
        $otherProposal = Sponsorship::factory()->create(['alvo_type' => Event::class, 'alvo_id' => $event->id]);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/sponsorships/{$proposal->id}", ['spo_status' => 'ACEITO'])
            ->assertOk()->assertJsonPath('sponsorship.spo_status', 'ACEITO');

        $this->patchJson("/api/sponsorships/{$otherProposal->id}", ['spo_status' => 'RECUSADO'])
            ->assertOk()->assertJsonPath('sponsorship.spo_status', 'RECUSADO');
    }

    public function test_non_owner_cannot_accept_or_reject_sponsorship(): void
    {
        [$event] = $this->alvoScenarios()[0];
        $proposal = Sponsorship::factory()->create(['alvo_type' => Event::class, 'alvo_id' => $event->id]);
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/sponsorships/{$proposal->id}", ['spo_status' => 'ACEITO'])->assertForbidden();
    }

    public function test_accepting_already_answered_sponsorship_fails(): void
    {
        [$event, $owner] = $this->alvoScenarios()[0];
        $proposal = Sponsorship::factory()->create(['alvo_type' => Event::class, 'alvo_id' => $event->id, 'spo_status' => 'ACEITO']);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/sponsorships/{$proposal->id}", ['spo_status' => 'RECUSADO'])->assertStatus(422);
    }

    public function test_accepting_cancelled_sponsorship_returns_404(): void
    {
        [$event, $owner] = $this->alvoScenarios()[0];
        $proposal = Sponsorship::factory()->create(['alvo_type' => Event::class, 'alvo_id' => $event->id, 'spo_active' => false]);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/sponsorships/{$proposal->id}", ['spo_status' => 'ACEITO'])->assertNotFound();
    }

    public function test_sponsor_cancels_own_pending_proposal(): void
    {
        [$event] = $this->alvoScenarios()[0];
        $sponsor = User::factory()->create();
        $proposal = Sponsorship::factory()->create([
            'alvo_type' => Event::class,
            'alvo_id' => $event->id,
            'sponsor_type' => User::class,
            'sponsor_id' => $sponsor->id,
        ]);
        Sanctum::actingAs($sponsor);

        $this->deleteJson("/api/sponsorships/{$proposal->id}")->assertOk();

        $this->assertDatabaseHas('sponsorships', ['id' => $proposal->id, 'spo_active' => false]);
    }

    public function test_other_user_cannot_cancel_sponsorship(): void
    {
        [$event] = $this->alvoScenarios()[0];
        $proposal = Sponsorship::factory()->create(['alvo_type' => Event::class, 'alvo_id' => $event->id]);
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/sponsorships/{$proposal->id}")->assertForbidden();
    }

    public function test_cancelling_already_accepted_sponsorship_fails(): void
    {
        [$event] = $this->alvoScenarios()[0];
        $sponsor = User::factory()->create();
        $proposal = Sponsorship::factory()->create([
            'alvo_type' => Event::class,
            'alvo_id' => $event->id,
            'sponsor_type' => User::class,
            'sponsor_id' => $sponsor->id,
            'spo_status' => 'ACEITO',
        ]);
        Sanctum::actingAs($sponsor);

        $this->deleteJson("/api/sponsorships/{$proposal->id}")->assertStatus(422);
    }

    public function test_user_lists_only_own_sponsorships(): void
    {
        [$event] = $this->alvoScenarios()[0];
        $sponsor = User::factory()->create();
        $ownProposal = Sponsorship::factory()->create([
            'alvo_type' => Event::class,
            'alvo_id' => $event->id,
            'sponsor_type' => User::class,
            'sponsor_id' => $sponsor->id,
        ]);
        Sponsorship::factory()->create(['alvo_type' => Event::class, 'alvo_id' => $event->id]);
        Sanctum::actingAs($sponsor);

        $response = $this->getJson('/api/sponsorships')->assertOk();

        $ids = collect($response->json('sponsorships'))->pluck('id');

        $this->assertEquals([$ownProposal->id], $ids->all());
    }
}
