<?php

namespace Tests\Feature\Models;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user(): void
    {
        $notification = Notification::factory()->create();

        $this->assertInstanceOf(User::class, $notification->user);
    }

    public function test_casts_not_conteudo_to_array_and_flags_to_boolean(): void
    {
        $notification = Notification::factory()->create([
            'not_conteudo' => ['mensagem' => 'Olá'],
            'not_lida' => 1,
            'not_active' => 1,
        ]);

        $this->assertSame(['mensagem' => 'Olá'], $notification->not_conteudo);
        $this->assertTrue($notification->not_lida);
        $this->assertTrue($notification->not_active);
    }

    public function test_inverse_relation_on_user(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->notifications->contains($notification));
    }
}
