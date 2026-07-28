<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventSessionRequest;
use App\Http\Requests\UpdateEventSessionRequest;
use App\Models\Event;
use App\Models\EventSession;

class EventSessionController extends Controller
{
    public function index(Event $event)
    {
        return response()->json(['sessions' => $event->sessions()->where('evs_active', true)->get()]);
    }

    public function store(StoreEventSessionRequest $request, Event $event)
    {
        $this->authorize('manageSessions', $event);

        $session = $event->sessions()->create($request->validated());

        return response()->json(['session' => $session], 201);
    }

    public function update(UpdateEventSessionRequest $request, Event $event, EventSession $session)
    {
        $this->authorize('manageSessions', $event);
        abort_unless($session->event_id === $event->id, 404);

        $session->update($request->validated());

        return response()->json(['session' => $session]);
    }

    public function destroy(Event $event, EventSession $session)
    {
        $this->authorize('manageSessions', $event);
        abort_unless($session->event_id === $event->id, 404);

        $session->update(['evs_active' => false]);

        return response()->json(['message' => 'Sessão desativada.']);
    }
}
