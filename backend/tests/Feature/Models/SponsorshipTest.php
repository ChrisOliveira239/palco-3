<?php

namespace Tests\Feature\Models;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use App\Models\Sponsorship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SponsorshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_sponsor_resolves_user(): void
    {
        $sponsorship = Sponsorship::factory()->create();

        $this->assertInstanceOf(User::class, $sponsorship->sponsor);
    }

    public function test_alvo_resolves_event(): void
    {
        $sponsorship = Sponsorship::factory()->create();

        $this->assertInstanceOf(Event::class, $sponsorship->alvo);
    }

    public function test_alvo_resolves_artist_profile(): void
    {
        $sponsorship = Sponsorship::factory()->porArtistProfile()->create();

        $this->assertInstanceOf(ArtistProfile::class, $sponsorship->alvo);
    }

    public function test_alvo_resolves_group(): void
    {
        $sponsorship = Sponsorship::factory()->porGroup()->create();

        $this->assertInstanceOf(Group::class, $sponsorship->alvo);
    }

    public function test_spo_tipo_apoio_and_spo_status_are_strings(): void
    {
        $sponsorship = Sponsorship::factory()->create([
            'spo_tipo_apoio' => 'EQUIPAMENTO',
        ]);

        $this->assertSame('EQUIPAMENTO', $sponsorship->spo_tipo_apoio);
        $this->assertSame('PROPOSTO', $sponsorship->spo_status);
    }

    public function test_dinheiro_state_fills_valor(): void
    {
        $sponsorship = Sponsorship::factory()->dinheiro()->create();

        $this->assertSame('DINHEIRO', $sponsorship->spo_tipo_apoio);
        $this->assertNotNull($sponsorship->spo_valor);
        $this->assertNull($sponsorship->spo_descricao);
    }

    public function test_inverse_relations_on_user_and_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $sponsorship = Sponsorship::factory()->create([
            'sponsor_id' => $user->id,
            'alvo_id' => $event->id,
        ]);

        $this->assertTrue($user->sponsorships->contains($sponsorship));
        $this->assertTrue($event->sponsorships->contains($sponsorship));
    }
}
