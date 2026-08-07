<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketPurchaseService
{
    public function purchase(TicketType $ticketType, User $user): Ticket
    {
        return DB::transaction(function () use ($ticketType, $user) {
            $locked = TicketType::lockForUpdate()->findOrFail($ticketType->id);

            abort_unless($locked->tit_active, 404);
            abort_if($locked->tit_venda_inicio && now()->lt($locked->tit_venda_inicio), 422, 'Vendas ainda não começaram.');
            abort_if($locked->tit_venda_fim && now()->gt($locked->tit_venda_fim), 422, 'Vendas encerradas.');
            abort_if($locked->tit_quantidade_vendida >= $locked->tit_quantidade_total, 422, 'Ingressos esgotados.');

            $locked->increment('tit_quantidade_vendida');

            return $locked->tickets()->create([
                'user_id' => $user->id,
                'tic_codigo_qr' => (string) Str::uuid(),
                'tic_status' => 'VALIDO',
            ]);
        });
    }
}
