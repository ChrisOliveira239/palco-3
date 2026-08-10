<?php

namespace Tests\Feature\Opportunities;

use App\Models\ArtistProfile;
use App\Models\Group;
use App\Models\Opportunity;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OpportunityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{0: string, 1: User}>
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

    public function test_guest_lists_only_open_and_active_opportunities(): void
    {
        Opportunity::factory()->create(['opp_status' => 'ABERTA', 'opp_active' => true]);
        Opportunity::factory()->create(['opp_status' => 'FECHADA', 'opp_active' => true]);
        Opportunity::factory()->create(['opp_status' => 'ABERTA', 'opp_active' => false]);

        $response = $this->getJson('/api/opportunities')->assertOk();

        $this->assertCount(1, $response->json('opportunities.data'));
    }

    public function test_guest_sees_open_opportunity_detail(): void
    {
        $opportunity = Opportunity::factory()->create(['opp_status' => 'ABERTA', 'opp_active' => true]);

        $this->getJson("/api/opportunities/{$opportunity->id}")
            ->assertOk()
            ->assertJsonPath('opportunity.id', $opportunity->id);
    }

    public function test_guest_cannot_see_closed_or_inactive_opportunity(): void
    {
        $closed = Opportunity::factory()->create(['opp_status' => 'FECHADA', 'opp_active' => true]);
        $inactive = Opportunity::factory()->create(['opp_status' => 'ABERTA', 'opp_active' => false]);

        $this->getJson("/api/opportunities/{$closed->id}")->assertNotFound();
        $this->getJson("/api/opportunities/{$inactive->id}")->assertNotFound();
    }

    public function test_owner_creates_opportunity(): void
    {
        foreach ($this->criadorScenarios() as [$tipo, $criador, $owner]) {
            Sanctum::actingAs($owner);

            $this->postJson('/api/opportunities', [
                'opp_titulo' => 'Procura-se fotógrafo',
                'opp_descricao' => 'Precisamos de um fotógrafo pro evento.',
                'criador_type' => $tipo,
                'criador_id' => $criador->id,
            ])->assertCreated()
                ->assertJsonPath('opportunity.opp_status', 'ABERTA')
                ->assertJsonPath('opportunity.opp_active', true);
        }
    }

    public function test_non_owner_cannot_create_opportunity(): void
    {
        foreach ($this->criadorScenarios() as [$tipo, $criador]) {
            Sanctum::actingAs(User::factory()->create());

            $this->postJson('/api/opportunities', [
                'opp_titulo' => 'Procura-se fotógrafo',
                'opp_descricao' => 'Precisamos de um fotógrafo pro evento.',
                'criador_type' => $tipo,
                'criador_id' => $criador->id,
            ])->assertForbidden();
        }
    }

    public function test_guest_cannot_create_opportunity(): void
    {
        [$tipo, $criador] = $this->criadorScenarios()[0];

        $this->postJson('/api/opportunities', [
            'opp_titulo' => 'Procura-se fotógrafo',
            'opp_descricao' => 'Precisamos de um fotógrafo pro evento.',
            'criador_type' => $tipo,
            'criador_id' => $criador->id,
        ])->assertUnauthorized();
    }

    public function test_nonexistent_skill_fails_validation(): void
    {
        [$tipo, $criador, $owner] = $this->criadorScenarios()[0];
        Sanctum::actingAs($owner);

        $this->postJson('/api/opportunities', [
            'opp_titulo' => 'Procura-se fotógrafo',
            'opp_descricao' => 'Precisamos de um fotógrafo pro evento.',
            'criador_type' => $tipo,
            'criador_id' => $criador->id,
            'skill_id' => 999999,
        ])->assertUnprocessable()->assertJsonValidationErrors('skill_id');
    }

    public function test_owner_updates_opportunity(): void
    {
        [, $artistProfile, $owner] = $this->criadorScenarios()[0];
        $opportunity = Opportunity::factory()->create([
            'criador_type' => ArtistProfile::class,
            'criador_id' => $artistProfile->id,
        ]);
        $skill = Skill::factory()->create();
        Sanctum::actingAs($owner);

        $this->patchJson("/api/opportunities/{$opportunity->id}", [
            'opp_titulo' => 'Novo título',
            'skill_id' => $skill->id,
            'opp_cidade' => 'Curitiba',
        ])->assertOk()
            ->assertJsonPath('opportunity.opp_titulo', 'Novo título')
            ->assertJsonPath('opportunity.skill_id', $skill->id)
            ->assertJsonPath('opportunity.opp_cidade', 'Curitiba');
    }

    public function test_owner_closes_opportunity_via_normal_update(): void
    {
        [, $artistProfile, $owner] = $this->criadorScenarios()[0];
        $opportunity = Opportunity::factory()->create([
            'criador_type' => ArtistProfile::class,
            'criador_id' => $artistProfile->id,
        ]);
        Sanctum::actingAs($owner);

        $this->patchJson("/api/opportunities/{$opportunity->id}", ['opp_status' => 'FECHADA'])
            ->assertOk()
            ->assertJsonPath('opportunity.opp_status', 'FECHADA');
    }

    public function test_non_owner_cannot_update_opportunity(): void
    {
        [, $artistProfile] = $this->criadorScenarios()[0];
        $opportunity = Opportunity::factory()->create([
            'criador_type' => ArtistProfile::class,
            'criador_id' => $artistProfile->id,
        ]);
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/opportunities/{$opportunity->id}", ['opp_titulo' => 'Hack'])
            ->assertForbidden();
    }

    public function test_owner_deactivates_opportunity(): void
    {
        [, $artistProfile, $owner] = $this->criadorScenarios()[0];
        $opportunity = Opportunity::factory()->create([
            'criador_type' => ArtistProfile::class,
            'criador_id' => $artistProfile->id,
        ]);
        Sanctum::actingAs($owner);

        $this->deleteJson("/api/opportunities/{$opportunity->id}")->assertOk();

        $this->assertDatabaseHas('opportunities', ['id' => $opportunity->id, 'opp_active' => false]);
    }

    public function test_non_owner_cannot_deactivate_opportunity(): void
    {
        [, $artistProfile] = $this->criadorScenarios()[0];
        $opportunity = Opportunity::factory()->create([
            'criador_type' => ArtistProfile::class,
            'criador_id' => $artistProfile->id,
        ]);
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/opportunities/{$opportunity->id}")->assertForbidden();
    }
}
