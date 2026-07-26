<?php

namespace Tests\Feature\Models;

use App\Models\ArtistProfile;
use App\Models\Group;
use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_criador_resolves_artist_profile(): void
    {
        $opportunity = Opportunity::factory()->create();

        $this->assertInstanceOf(ArtistProfile::class, $opportunity->criador);
    }

    public function test_criador_resolves_group(): void
    {
        $opportunity = Opportunity::factory()->porGroup()->create();

        $this->assertInstanceOf(Group::class, $opportunity->criador);
    }

    public function test_belongs_to_skill(): void
    {
        $opportunity = Opportunity::factory()->create();

        $this->assertInstanceOf(Skill::class, $opportunity->skill);
    }

    public function test_opp_status_is_string_and_opp_active_casts_to_boolean(): void
    {
        $opportunity = Opportunity::factory()->create([
            'opp_status' => 'FECHADA',
            'opp_active' => 1,
        ]);

        $this->assertSame('FECHADA', $opportunity->opp_status);
        $this->assertTrue($opportunity->opp_active);
    }

    public function test_has_many_applications(): void
    {
        $opportunity = Opportunity::factory()->create();
        $application = OpportunityApplication::factory()->create([
            'opportunity_id' => $opportunity->id,
        ]);

        $this->assertTrue($opportunity->applications->contains($application));
    }

    public function test_inverse_relations_on_artist_profile_group_and_skill(): void
    {
        $artistProfile = ArtistProfile::factory()->create();
        $group = Group::factory()->create();
        $skill = Skill::factory()->create();

        $artistOpportunity = Opportunity::factory()->create([
            'criador_type' => ArtistProfile::class,
            'criador_id' => $artistProfile->id,
            'skill_id' => $skill->id,
        ]);
        $groupOpportunity = Opportunity::factory()->create([
            'criador_type' => Group::class,
            'criador_id' => $group->id,
            'skill_id' => $skill->id,
        ]);

        $this->assertTrue($artistProfile->opportunities->contains($artistOpportunity));
        $this->assertTrue($group->opportunities->contains($groupOpportunity));
        $this->assertTrue($skill->opportunities->contains($artistOpportunity));
        $this->assertTrue($skill->opportunities->contains($groupOpportunity));
    }
}
