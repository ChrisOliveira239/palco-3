<?php

namespace Tests\Feature\Models;

use App\Models\ArtistProfile;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_persisted_skill(): void
    {
        $skill = Skill::factory()->create(['ski_nome' => 'Guitarrista']);

        $this->assertDatabaseHas('skills', [
            'id' => $skill->id,
            'ski_nome' => 'Guitarrista',
            'ski_active' => true,
        ]);
    }

    public function test_belongs_to_many_artist_profiles(): void
    {
        $skill = Skill::factory()->create();
        $artistProfile = ArtistProfile::factory()->create();

        $skill->artistProfiles()->attach($artistProfile);

        $this->assertTrue($skill->artistProfiles->contains($artistProfile));
        $this->assertTrue($artistProfile->skills->contains($skill));
    }
}
