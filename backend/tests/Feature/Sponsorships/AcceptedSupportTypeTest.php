<?php

namespace Tests\Feature\Sponsorships;

use App\Models\AcceptedSupportType;
use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AcceptedSupportTypeTest extends TestCase
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

    public function test_guest_lists_accepted_support_types_for_any_alvo(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, , $prefix]) {
            AcceptedSupportType::factory()->create(['alvo_type' => get_class($alvo), 'alvo_id' => $alvo->id, 'ast_tipo_apoio' => 'DINHEIRO']);

            $response = $this->getJson("/api/{$prefix}/{$alvo->id}/accepted-support-types")->assertOk();

            $this->assertCount(1, $response->json('accepted_support_types'));
        }
    }

    public function test_owner_can_add_accepted_support_type(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, $owner, $prefix]) {
            Sanctum::actingAs($owner);

            $this->postJson("/api/{$prefix}/{$alvo->id}/accepted-support-types", ['ast_tipo_apoio' => 'EQUIPAMENTO'])
                ->assertCreated()
                ->assertJsonPath('accepted_support_type.ast_tipo_apoio', 'EQUIPAMENTO');
        }
    }

    public function test_non_owner_cannot_add_accepted_support_type(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, , $prefix]) {
            Sanctum::actingAs(User::factory()->create());

            $this->postJson("/api/{$prefix}/{$alvo->id}/accepted-support-types", ['ast_tipo_apoio' => 'EQUIPAMENTO'])
                ->assertForbidden();
        }
    }

    public function test_guest_cannot_add_accepted_support_type(): void
    {
        [$event] = $this->alvoScenarios()[0];

        $this->postJson("/api/events/{$event->id}/accepted-support-types", ['ast_tipo_apoio' => 'EQUIPAMENTO'])
            ->assertUnauthorized();
    }

    public function test_invalid_tipo_apoio_fails_validation(): void
    {
        [$event, $owner] = $this->alvoScenarios()[0];
        Sanctum::actingAs($owner);

        $this->postJson("/api/events/{$event->id}/accepted-support-types", ['ast_tipo_apoio' => 'BITCOIN'])
            ->assertUnprocessable()->assertJsonValidationErrors('ast_tipo_apoio');
    }

    public function test_duplicate_tipo_apoio_for_same_alvo_fails_validation(): void
    {
        [$event, $owner] = $this->alvoScenarios()[0];
        Sanctum::actingAs($owner);

        $this->postJson("/api/events/{$event->id}/accepted-support-types", ['ast_tipo_apoio' => 'DINHEIRO'])->assertCreated();

        $this->postJson("/api/events/{$event->id}/accepted-support-types", ['ast_tipo_apoio' => 'DINHEIRO'])
            ->assertUnprocessable()->assertJsonValidationErrors('ast_tipo_apoio');
    }

    public function test_owner_can_remove_accepted_support_type(): void
    {
        foreach ($this->alvoScenarios() as [$alvo, $owner, $prefix]) {
            $acceptedSupportType = AcceptedSupportType::factory()->create([
                'alvo_type' => get_class($alvo),
                'alvo_id' => $alvo->id,
            ]);
            Sanctum::actingAs($owner);

            $this->deleteJson("/api/{$prefix}/{$alvo->id}/accepted-support-types/{$acceptedSupportType->id}")->assertOk();

            $this->assertDatabaseMissing('accepted_support_types', ['id' => $acceptedSupportType->id]);
        }
    }

    public function test_accepted_support_type_from_another_alvo_returns_404(): void
    {
        [$event, $owner] = $this->alvoScenarios()[0];
        $otherEvent = Event::factory()->create();
        $acceptedSupportType = AcceptedSupportType::factory()->create([
            'alvo_type' => Event::class,
            'alvo_id' => $otherEvent->id,
        ]);
        Sanctum::actingAs($owner);

        $this->deleteJson("/api/events/{$event->id}/accepted-support-types/{$acceptedSupportType->id}")
            ->assertNotFound();
    }
}
