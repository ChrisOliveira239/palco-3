<?php

namespace Tests\Feature\Models;

use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_opportunity_and_user(): void
    {
        $application = OpportunityApplication::factory()->create();

        $this->assertInstanceOf(Opportunity::class, $application->opportunity);
        $this->assertInstanceOf(User::class, $application->user);
    }

    public function test_opa_status_is_string(): void
    {
        $application = OpportunityApplication::factory()->create();

        $this->assertSame('PENDENTE', $application->opa_status);
    }

    public function test_aceita_state(): void
    {
        $application = OpportunityApplication::factory()->aceita()->create();

        $this->assertSame('ACEITO', $application->opa_status);
    }

    public function test_recusada_state(): void
    {
        $application = OpportunityApplication::factory()->recusada()->create();

        $this->assertSame('RECUSADO', $application->opa_status);
    }

    public function test_inverse_relation_on_user(): void
    {
        $user = User::factory()->create();
        $application = OpportunityApplication::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->opportunityApplications->contains($application));
    }
}
