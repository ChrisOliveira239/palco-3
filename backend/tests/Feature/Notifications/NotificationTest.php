<?php

namespace Tests\Feature\Notifications;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_lists_only_own_active_notifications(): void
    {
        $user = User::factory()->create();
        $own = Notification::factory()->create(['user_id' => $user->id]);
        Notification::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/notifications')->assertOk();

        $ids = collect($response->json('notifications.data'))->pluck('id');

        $this->assertEquals([$own->id], $ids->all());
    }

    public function test_inactive_notification_does_not_appear_in_index(): void
    {
        $user = User::factory()->create();
        Notification::factory()->create(['user_id' => $user->id, 'not_active' => false]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/notifications')->assertOk();

        $this->assertCount(0, $response->json('notifications.data'));
    }

    public function test_guest_cannot_list_notifications(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
    }

    public function test_user_marks_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id, 'not_lida' => false]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/notifications/{$notification->id}/read")->assertOk();

        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'not_lida' => true]);
    }

    public function test_user_cannot_mark_others_notification_as_read(): void
    {
        $notification = Notification::factory()->create(['not_lida' => false]);
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/notifications/{$notification->id}/read")->assertForbidden();
    }

    public function test_mark_all_as_read_only_affects_own_unread_notifications(): void
    {
        $user = User::factory()->create();
        $ownUnread = Notification::factory()->create(['user_id' => $user->id, 'not_lida' => false]);
        $otherUnread = Notification::factory()->create(['not_lida' => false]);
        Sanctum::actingAs($user);

        $this->patchJson('/api/notifications/read-all')->assertOk();

        $this->assertDatabaseHas('notifications', ['id' => $ownUnread->id, 'not_lida' => true]);
        $this->assertDatabaseHas('notifications', ['id' => $otherUnread->id, 'not_lida' => false]);
    }

    public function test_user_deactivates_own_notification(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/notifications/{$notification->id}")->assertOk();

        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'not_active' => false]);
    }

    public function test_user_cannot_deactivate_others_notification(): void
    {
        $notification = Notification::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/notifications/{$notification->id}")->assertForbidden();
    }
}
