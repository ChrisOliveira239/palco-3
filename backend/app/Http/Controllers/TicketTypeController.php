<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketTypeRequest;
use App\Http\Requests\UpdateTicketTypeRequest;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\TicketType;

class TicketTypeController extends Controller
{
    public function index(Event $event, EventSession $session)
    {
        abort_unless($session->event_id === $event->id, 404);

        return response()->json([
            'ticket_types' => $session->ticketTypes()->where('tit_active', true)->get(),
        ]);
    }

    public function store(StoreTicketTypeRequest $request, Event $event, EventSession $session)
    {
        $this->authorize('manageTicketTypes', $event);
        abort_unless($session->event_id === $event->id, 404);

        $ticketType = $session->ticketTypes()->create($request->validated());

        return response()->json(['ticket_type' => $ticketType], 201);
    }

    public function update(UpdateTicketTypeRequest $request, Event $event, EventSession $session, TicketType $ticketType)
    {
        $this->authorize('manageTicketTypes', $event);
        abort_unless($session->event_id === $event->id, 404);
        abort_unless($ticketType->event_session_id === $session->id, 404);

        $ticketType->update($request->validated());

        return response()->json(['ticket_type' => $ticketType]);
    }

    public function destroy(Event $event, EventSession $session, TicketType $ticketType)
    {
        $this->authorize('manageTicketTypes', $event);
        abort_unless($session->event_id === $event->id, 404);
        abort_unless($ticketType->event_session_id === $session->id, 404);

        $ticketType->update(['tit_active' => false]);

        return response()->json(['message' => 'Tipo de ingresso desativado.']);
    }
}
