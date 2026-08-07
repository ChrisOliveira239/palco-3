<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketValidationService
{
    public function validate(Event $event, string $codigoQr): Ticket
    {
        return DB::transaction(function () use ($event, $codigoQr) {
            $ticket = Ticket::where('tic_codigo_qr', $codigoQr)->lockForUpdate()->first();

            abort_unless($ticket, 404, 'Ingresso não encontrado.');
            abort_unless($ticket->ticketType->eventSession->event_id === $event->id, 404, 'Ingresso não pertence a esse evento.');
            abort_if($ticket->tic_status === 'CANCELADO', 422, 'Ingresso cancelado.');
            abort_if($ticket->tic_status === 'USADO', 422, 'Ingresso já foi validado.');

            $ticket->update([
                'tic_status' => 'USADO',
                'tic_usado_em' => now(),
            ]);

            return $ticket->load(['user', 'ticketType']);
        });
    }
}
