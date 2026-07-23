<?php

namespace Tests\Feature\Models;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\FeedPost;
use App\Models\Group;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_denunciante_resolves_user(): void
    {
        $report = Report::factory()->create();

        $this->assertInstanceOf(User::class, $report->denunciante);
    }

    public function test_alvo_resolves_event(): void
    {
        $report = Report::factory()->create();

        $this->assertInstanceOf(Event::class, $report->alvo);
    }

    public function test_alvo_resolves_artist_profile(): void
    {
        $report = Report::factory()->porArtistProfile()->create();

        $this->assertInstanceOf(ArtistProfile::class, $report->alvo);
    }

    public function test_alvo_resolves_group(): void
    {
        $report = Report::factory()->porGroup()->create();

        $this->assertInstanceOf(Group::class, $report->alvo);
    }

    public function test_alvo_resolves_feed_post(): void
    {
        $report = Report::factory()->porFeedPost()->create();

        $this->assertInstanceOf(FeedPost::class, $report->alvo);
    }

    public function test_rep_status_is_string(): void
    {
        $report = Report::factory()->create(['rep_status' => 'ANALISADO']);

        $this->assertSame('ANALISADO', $report->rep_status);
    }

    public function test_inverse_relations_on_user_and_event(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        $report = Report::factory()->create([
            'denunciante_id' => $user->id,
            'alvo_id' => $event->id,
        ]);

        $this->assertTrue($user->reports->contains($report));
        $this->assertTrue($event->reports->contains($report));
    }
}
