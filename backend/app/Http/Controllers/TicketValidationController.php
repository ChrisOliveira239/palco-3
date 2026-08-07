<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateTicketRequest;
use App\Models\Event;
use App\Services\TicketValidationService;

class TicketValidationController extends Controller
{
    public function __construct(private TicketValidationService $validationService)
    {
    }

    public function store(ValidateTicketRequest $request, Event $event)
    {
        $this->authorize('validateTickets', $event);

        $ticket = $this->validationService->validate($event, $request->validated('tic_codigo_qr'));

        return response()->json(['ticket' => $ticket]);
    }
}
