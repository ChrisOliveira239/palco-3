<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::where('eve_status', 'PUBLICADO')
            ->where('eve_active', true)
            ->with(['category', 'sessions' => function ($query) {
                $query->where('evs_active', true)->orderBy('evs_data_inicio')->with('venue');
            }]);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('cidade')) {
            $cidade = $request->cidade;
            $query->whereHas('sessions', function ($query) use ($cidade) {
                $query->where('evs_active', true)
                    ->where(function ($query) use ($cidade) {
                        $query->where('evs_cidade', 'like', "%{$cidade}%")
                            ->orWhereHas('venue', fn ($query) => $query->where('ven_cidade', 'like', "%{$cidade}%"));
                    });
            });
        }

        return response()->json(['events' => $query->paginate()]);
    }

    public function show(Event $event)
    {
        abort_unless($event->eve_status === 'PUBLICADO' && $event->eve_active, 404);

        $event->load([
            'category',
            'sessions' => function ($query) {
                $query->where('evs_active', true)->orderBy('evs_data_inicio')->with([
                    'venue',
                    'ticketTypes' => fn ($query) => $query->where('tit_active', true),
                ]);
            },
            'media' => fn ($query) => $query->where('evm_active', true)->orderBy('evm_ordem'),
            'artists' => fn ($query) => $query->wherePivot('eva_status', 'ACEITO'),
            'groups' => fn ($query) => $query->wherePivot('evg_status', 'ACEITO'),
        ]);

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
