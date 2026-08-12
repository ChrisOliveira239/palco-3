<?php

namespace Tests\Feature\Events;

use App\Models\ArtistProfile;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\Group;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_lists_only_published_active_events(): void
    {
        $published = Event::factory()->create(['eve_status' => 'PUBLICADO', 'eve_active' => true]);
        Event::factory()->create(['eve_status' => 'PENDENTE']);
        Event::factory()->create(['eve_status' => 'PUBLICADO', 'eve_active' => false]);

        $response = $this->getJson('/api/events')->assertOk();

        $ids = collect($response->json('events.data'))->pluck('id');

        $this->assertTrue($ids->contains($published->id));
        $this->assertCount(1, $ids);
    }

    public function test_guest_filters_events_by_category(): void
    {
        $rock = Category::factory()->create();
        $jazz = Category::factory()->create();
        $eventoRock = Event::factory()->create(['eve_status' => 'PUBLICADO', 'category_id' => $rock->id]);
        Event::factory()->create(['eve_status' => 'PUBLICADO', 'category_id' => $jazz->id]);

        $response = $this->getJson("/api/events?category_id={$rock->id}")->assertOk();
        $ids = collect($response->json('events.data'))->pluck('id');

        $this->assertEquals([$eventoRock->id], $ids->all());
    }

    public function test_guest_filters_events_by_cidade_via_evento_session(): void
    {
        $eventoRecife = Event::factory()->create(['eve_status' => 'PUBLICADO']);
        EventSession::factory()->enderecoLivre()->create(['event_id' => $eventoRecife->id, 'evs_cidade' => 'Recife']);

        $eventoOlinda = Event::factory()->create(['eve_status' => 'PUBLICADO']);
        EventSession::factory()->enderecoLivre()->create(['event_id' => $eventoOlinda->id, 'evs_cidade' => 'Olinda']);

        $response = $this->getJson('/api/events?cidade=Recife')->assertOk();
        $ids = collect($response->json('events.data'))->pluck('id');

        $this->assertEquals([$eventoRecife->id], $ids->all());
    }

    public function test_guest_filters_events_by_cidade_via_venue(): void
    {
        $venue = Venue::factory()->create(['ven_cidade' => 'Salvador']);
        $eventoSalvador = Event::factory()->create(['eve_status' => 'PUBLICADO']);
        EventSession::factory()->create(['event_id' => $eventoSalvador->id, 'venue_id' => $venue->id]);

        Event::factory()->create(['eve_status' => 'PUBLICADO']);

        $response = $this->getJson('/api/events?cidade=Salvador')->assertOk();
        $ids = collect($response->json('events.data'))->pluck('id');

        $this->assertEquals([$eventoSalvador->id], $ids->all());
    }

    public function test_guest_filters_events_by_raio_de_distancia(): void
    {
        $venuePerto = Venue::factory()->create(['ven_latitude' => -8.0631, 'ven_longitude' => -34.8711]);
        $eventoPerto = Event::factory()->create(['eve_status' => 'PUBLICADO']);
        EventSession::factory()->create(['event_id' => $eventoPerto->id, 'venue_id' => $venuePerto->id]);

        $venueLonge = Venue::factory()->create(['ven_latitude' => -23.5505199, 'ven_longitude' => -46.6333094]);
        $eventoLonge = Event::factory()->create(['eve_status' => 'PUBLICADO']);
        EventSession::factory()->create(['event_id' => $eventoLonge->id, 'venue_id' => $venueLonge->id]);

        $response = $this->getJson('/api/events?lat=-8.0578381&lng=-34.8828158&raio_km=10')->assertOk();
        $ids = collect($response->json('events.data'))->pluck('id');

        $this->assertEquals([$eventoPerto->id], $ids->all());
    }

    public function test_raio_de_distancia_ignora_sessao_com_endereco_livre(): void
    {
        $eventoEnderecoLivre = Event::factory()->create(['eve_status' => 'PUBLICADO']);
        EventSession::factory()->enderecoLivre()->create([
            'event_id' => $eventoEnderecoLivre->id,
            'evs_cidade' => 'Recife',
        ]);

        $response = $this->getJson('/api/events?lat=-8.0578381&lng=-34.8828158&raio_km=10')->assertOk();

        $this->assertCount(0, $response->json('events.data'));
    }

    public function test_guest_can_view_published_event(): void
    {
        $event = Event::factory()->create(['eve_status' => 'PUBLICADO', 'eve_active' => true]);

        $this->getJson("/api/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('event.id', $event->id);
    }

    public function test_show_returns_event_with_related_data_eager_loaded(): void
    {
        $event = Event::factory()->create(['eve_status' => 'PUBLICADO', 'eve_active' => true]);

        $session = EventSession::factory()->create(['event_id' => $event->id]);
        \App\Models\TicketType::factory()->create(['event_session_id' => $session->id, 'tit_active' => true]);
        \App\Models\TicketType::factory()->create(['event_session_id' => $session->id, 'tit_active' => false]);

        \App\Models\EventMedia::factory()->create(['event_id' => $event->id, 'evm_active' => true]);
        \App\Models\EventMedia::factory()->create(['event_id' => $event->id, 'evm_active' => false]);

        $artistaAceito = ArtistProfile::factory()->create();
        $artistaPendente = ArtistProfile::factory()->create();
        $event->artists()->attach($artistaAceito->id, ['eva_status' => 'ACEITO']);
        $event->artists()->attach($artistaPendente->id, ['eva_status' => 'PENDENTE']);

        $response = $this->getJson("/api/events/{$event->id}")->assertOk();

        $this->assertEquals($event->category_id, $response->json('event.category.id'));
        $this->assertCount(1, $response->json('event.sessions.0.ticket_types'));
        $this->assertCount(1, $response->json('event.media'));
        $artistIds = collect($response->json('event.artists'))->pluck('id');
        $this->assertEquals([$artistaAceito->id], $artistIds->all());
    }

    public function test_non_published_event_returns_404_even_for_organizer(): void
    {
        $artistProfile = ArtistProfile::factory()->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
            'eve_status' => 'PENDENTE',
        ]);
        Sanctum::actingAs($artistProfile->user);

        $this->getJson("/api/events/{$event->id}")->assertNotFound();
    }

    public function test_artist_can_create_event(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();
        $category = Category::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/events', [
            'eve_titulo' => 'Show de Rock',
            'eve_descricao' => 'Uma noite de rock',
            'category_id' => $category->id,
            'organizador_type' => 'artist_profile',
            'organizador_id' => $artistProfile->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('event.eve_status', 'PENDENTE')
            ->assertJsonPath('event.organizador_type', ArtistProfile::class);

        $this->assertDatabaseHas('events', [
            'eve_titulo' => 'Show de Rock',
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
            'eve_status' => 'PENDENTE',
        ]);
    }

    public function test_group_owner_can_create_event_for_group(): void
    {
        $owner = User::factory()->create();
        $group = Group::factory()->create(['user_id' => $owner->id]);
        $category = Category::factory()->create();
        Sanctum::actingAs($owner);

        $this->postJson('/api/events', [
            'eve_titulo' => 'Festival',
            'eve_descricao' => 'Festival de música',
            'category_id' => $category->id,
            'organizador_type' => 'group',
            'organizador_id' => $group->id,
        ])->assertCreated();
    }

    public function test_group_admin_member_can_create_event_for_group(): void
    {
        $group = Group::factory()->create();
        $admin = User::factory()->create();
        $group->members()->attach($admin->id, ['grm_papel' => 'ADMIN']);
        $category = Category::factory()->create();
        Sanctum::actingAs($admin);

        $this->postJson('/api/events', [
            'eve_titulo' => 'Festival',
            'eve_descricao' => 'Festival de música',
            'category_id' => $category->id,
            'organizador_type' => 'group',
            'organizador_id' => $group->id,
        ])->assertCreated();
    }

    public function test_group_regular_member_cannot_create_event_for_group(): void
    {
        $group = Group::factory()->create();
        $member = User::factory()->create();
        $group->members()->attach($member->id, ['grm_papel' => 'MEMBRO']);
        $category = Category::factory()->create();
        Sanctum::actingAs($member);

        $this->postJson('/api/events', [
            'eve_titulo' => 'Festival',
            'eve_descricao' => 'Festival de música',
            'category_id' => $category->id,
            'organizador_type' => 'group',
            'organizador_id' => $group->id,
        ])->assertForbidden();
    }

    public function test_user_cannot_create_event_using_someone_elses_artist_profile(): void
    {
        $otherProfile = ArtistProfile::factory()->create();
        $category = Category::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/events', [
            'eve_titulo' => 'Show',
            'eve_descricao' => 'Descrição',
            'category_id' => $category->id,
            'organizador_type' => 'artist_profile',
            'organizador_id' => $otherProfile->id,
        ])->assertForbidden();
    }

    public function test_organizer_can_update_own_event(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
        ]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/events/{$event->id}", ['eve_titulo' => 'Título Atualizado'])
            ->assertOk()
            ->assertJsonPath('event.eve_titulo', 'Título Atualizado');
    }

    public function test_non_organizer_cannot_update_event(): void
    {
        $event = Event::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/events/{$event->id}", ['eve_titulo' => 'Tentativa'])
            ->assertForbidden();
    }

    public function test_editing_rejected_event_reverts_to_pendente(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
            'eve_status' => 'REJEITADO',
        ]);
        Sanctum::actingAs($user);

        $this->patchJson("/api/events/{$event->id}", ['eve_titulo' => 'Corrigido'])
            ->assertOk()
            ->assertJsonPath('event.eve_status', 'PENDENTE');
    }

    public function test_organizer_can_deactivate_own_event(): void
    {
        $user = User::factory()->create();
        $artistProfile = ArtistProfile::factory()->for($user)->create();
        $event = Event::factory()->create([
            'organizador_type' => ArtistProfile::class,
            'organizador_id' => $artistProfile->id,
        ]);
        Sanctum::actingAs($user);

        $this->deleteJson("/api/events/{$event->id}")->assertOk();

        $this->assertDatabaseHas('events', ['id' => $event->id, 'eve_active' => false]);
    }

    public function test_non_organizer_cannot_deactivate_event(): void
    {
        $event = Event::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/events/{$event->id}")->assertForbidden();
    }

    public function test_guest_cannot_create_update_or_delete_event(): void
    {
        $event = Event::factory()->create();
        $category = Category::factory()->create();

        $this->postJson('/api/events', [
            'eve_titulo' => 'X',
            'eve_descricao' => 'X',
            'category_id' => $category->id,
            'organizador_type' => 'artist_profile',
            'organizador_id' => 1,
        ])->assertUnauthorized();
        $this->patchJson("/api/events/{$event->id}", ['eve_titulo' => 'X'])->assertUnauthorized();
        $this->deleteJson("/api/events/{$event->id}")->assertUnauthorized();
    }
}
