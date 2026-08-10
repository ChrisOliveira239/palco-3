<?php

namespace Tests\Feature\Opportunities;

use App\Models\ArtistProfile;
use App\Models\Group;
use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OpportunityApplicationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{0: string, 1: \Illuminate\Database\Eloquent\Model, 2: User}>
     */
    protected function criadorScenarios(): array
    {
        $artistProfileOwner = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($artistProfileOwner)->create();

        $groupOwner = User::factory()->create();
        $group = Group::factory()->for($groupOwner, 'owner')->create();

        return [
            ['artist_profile', $artistProfile, $artistProfileOwner],
            ['group', $group, $groupOwner],
        ];
    }

    protected function opportunityFor(string $tipo, $criador): Opportunity
    {
        return Opportunity::factory()->create([
            'criador_type' => $tipo === 'artist_profile' ? ArtistProfile::class : Group::class,
            'criador_id' => $criador->id,
        ]);
    }

    public function test_authenticated_user_applies_to_open_opportunity(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/opportunities/{$opportunity->id}/applications", ['opa_mensagem' => 'Tenho interesse.'])
            ->assertCreated()
            ->assertJsonPath('application.opa_status', 'PENDENTE')
            ->assertJsonPath('application.opa_active', true);
    }

    public function test_owner_cannot_apply_to_own_opportunity(): void
    {
        foreach ($this->criadorScenarios() as [$tipo, $criador, $owner]) {
            $opportunity = $this->opportunityFor($tipo, $criador);
            Sanctum::actingAs($owner);

            $this->postJson("/api/opportunities/{$opportunity->id}/applications", [])->assertForbidden();
        }
    }

    public function test_guest_cannot_apply(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);

        $this->postJson("/api/opportunities/{$opportunity->id}/applications", [])->assertUnauthorized();
    }

    public function test_duplicate_application_fails(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $user = User::factory()->create();
        OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id, 'user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->postJson("/api/opportunities/{$opportunity->id}/applications", [])->assertStatus(422);
    }

    public function test_cannot_apply_to_closed_opportunity(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $opportunity->update(['opp_status' => 'FECHADA']);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/opportunities/{$opportunity->id}/applications", [])->assertStatus(422);
    }

    public function test_cannot_apply_to_inactive_opportunity(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $opportunity->update(['opp_active' => false]);
        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/opportunities/{$opportunity->id}/applications", [])->assertNotFound();
    }

    public function test_owner_lists_applications(): void
    {
        [$tipo, $criador, $owner] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        OpportunityApplication::factory()->count(2)->create(['opportunity_id' => $opportunity->id]);
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/opportunities/{$opportunity->id}/applications")->assertOk();

        $this->assertCount(2, $response->json('applications'));
    }

    public function test_non_owner_cannot_list_applications(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/opportunities/{$opportunity->id}/applications")->assertForbidden();
    }

    public function test_owner_accepts_and_rejects_application(): void
    {
        [$tipo, $criador, $owner] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $application = OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id]);
        $otherApplication = OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id]);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/opportunities/{$opportunity->id}/applications/{$application->id}", ['opa_status' => 'ACEITO'])
            ->assertOk()->assertJsonPath('application.opa_status', 'ACEITO');

        $this->patchJson("/api/opportunities/{$opportunity->id}/applications/{$otherApplication->id}", ['opa_status' => 'RECUSADO'])
            ->assertOk()->assertJsonPath('application.opa_status', 'RECUSADO');
    }

    public function test_non_owner_cannot_accept_or_reject_application(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $application = OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id]);
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/opportunities/{$opportunity->id}/applications/{$application->id}", ['opa_status' => 'ACEITO'])
            ->assertForbidden();
    }

    public function test_accepting_already_answered_application_fails(): void
    {
        [$tipo, $criador, $owner] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $application = OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id, 'opa_status' => 'ACEITO']);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/opportunities/{$opportunity->id}/applications/{$application->id}", ['opa_status' => 'RECUSADO'])
            ->assertStatus(422);
    }

    public function test_accepting_cancelled_application_returns_404(): void
    {
        [$tipo, $criador, $owner] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $application = OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id, 'opa_active' => false]);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/opportunities/{$opportunity->id}/applications/{$application->id}", ['opa_status' => 'ACEITO'])
            ->assertNotFound();
    }

    public function test_applicant_cancels_own_pending_application(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $applicant = User::factory()->create();
        $application = OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id, 'user_id' => $applicant->id]);
        Sanctum::actingAs($applicant);

        $this->deleteJson("/api/opportunities/{$opportunity->id}/applications/{$application->id}")->assertOk();

        $this->assertDatabaseHas('opportunity_applications', ['id' => $application->id, 'opa_active' => false]);
    }

    public function test_other_user_cannot_cancel_application(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $application = OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id]);
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/opportunities/{$opportunity->id}/applications/{$application->id}")->assertForbidden();
    }

    public function test_cancelling_already_accepted_application_fails(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $applicant = User::factory()->create();
        $application = OpportunityApplication::factory()->create([
            'opportunity_id' => $opportunity->id,
            'user_id' => $applicant->id,
            'opa_status' => 'ACEITO',
        ]);
        Sanctum::actingAs($applicant);

        $this->deleteJson("/api/opportunities/{$opportunity->id}/applications/{$application->id}")->assertStatus(422);
    }

    public function test_application_from_another_opportunity_returns_404(): void
    {
        [$tipo, $criador, $owner] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $otherOpportunity = $this->opportunityFor($tipo, $criador);
        $application = OpportunityApplication::factory()->create(['opportunity_id' => $otherOpportunity->id]);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/opportunities/{$opportunity->id}/applications/{$application->id}", ['opa_status' => 'ACEITO'])
            ->assertNotFound();
    }

    public function test_user_lists_only_own_applications(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];
        $opportunity = $this->opportunityFor($tipo, $criador);
        $applicant = User::factory()->create();
        $ownApplication = OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id, 'user_id' => $applicant->id]);
        OpportunityApplication::factory()->create(['opportunity_id' => $opportunity->id]);
        Sanctum::actingAs($applicant);

        $response = $this->getJson('/api/opportunity-applications')->assertOk();

        $ids = collect($response->json('applications'))->pluck('id');

        $this->assertEquals([$ownApplication->id], $ids->all());
    }
}
