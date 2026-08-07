<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventSession;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Services\TicketPurchaseService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private TicketPurchaseService $purchaseService)
    {
    }

    public function store(Request $request, Event $event, EventSession $session, TicketType $ticketType)
    {
        abort_unless($session->event_id === $event->id, 404);
        abort_unless($ticketType->event_session_id === $session->id, 404);

        $ticket = $this->purchaseService->purchase($ticketType, $request->user());

        return response()->json(['ticket' => $ticket], 201);
    }

    public function index(Request $request)
    {
        return response()->json([
            'tickets' => $request->user()->tickets()->with('ticketType')->get(),
        ]);
    }

    public function show(Request $request, Ticket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);

        return response()->json(['ticket' => $ticket->load('ticketType')]);
    }
}
