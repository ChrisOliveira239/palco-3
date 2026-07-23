<?php

namespace Tests\Feature\Models;

use App\Models\Event;
use App\Models\EventReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_event_and_user(): void
    {
        $review = EventReview::factory()->create();

        $this->assertInstanceOf(Event::class, $review->event);
        $this->assertInstanceOf(User::class, $review->user);
    }

    public function test_casts_evr_nota_to_integer(): void
    {
        $review = EventReview::factory()->create(['evr_nota' => '4']);

        $this->assertSame(4, $review->evr_nota);
    }

    public function test_inverse_relations_on_event_and_user(): void
    {
        $event = Event::factory()->create();
        $user = User::factory()->create();
        $review = EventReview::factory()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue($event->reviews->contains($review));
        $this->assertTrue($user->eventReviews->contains($review));
    }
}
