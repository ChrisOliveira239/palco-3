<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;

class EventController extends Controller
{
    public function index()
    {
        return response()->json([
            'events' => Event::where('eve_status', 'PUBLICADO')->where('eve_active', true)->paginate(),
        ]);
    }

    public function show(Event $event)
    {
        abort_unless($event->eve_status === 'PUBLICADO' && $event->eve_active, 404);

        return response()->json(['event' => $event]);
    }

    public function store(StoreEventRequest $request)
    {
        $organizadorClass = [
            'artist_profile' => ArtistProfile::class,
            'group' => Group::class,
        ][$request->validated('organizador_type')];

        $event = Event::create([
            ...$request->safe()->except('organizador_type'),
            'organizador_type' => $organizadorClass,
            'eve_status' => 'PENDENTE',
        ]);

        return response()->json(['event' => $event], 201);
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update($request->validated());

        if ($event->eve_status === 'REJEITADO') {
            $event->update(['eve_status' => 'PENDENTE']);
        }

        return response()->json(['event' => $event]);
    }

    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->update(['eve_active' => false]);

        return response()->json(['message' => 'Evento desativado.']);
    }

    public function approve(Event $event)
    {
        abort_unless($event->eve_status === 'PENDENTE', 409, 'Evento não está pendente de aprovação.');

        $event->update([
            'eve_status' => 'PUBLICADO',
            'aprovado_por_id' => request()->user()->id,
            'eve_aprovado_em' => now(),
        ]);

        return response()->json(['event' => $event]);
    }

    public function reject(Event $event)
    {
        abort_unless($event->eve_status === 'PENDENTE', 409, 'Evento não está pendente de aprovação.');

        $event->update(['eve_status' => 'REJEITADO']);

        return response()->json(['event' => $event]);
    }
}
