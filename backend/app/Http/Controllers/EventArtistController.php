<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventArtistRequest;
use App\Http\Requests\UpdateEventArtistRequest;
use App\Models\ArtistProfile;
use App\Models\Event;
use Illuminate\Http\Request;

class EventArtistController extends Controller
{
    public function index(Event $event)
    {
        return response()->json([
            'artists' => $event->artists()->wherePivot('eva_status', 'ACEITO')->get(),
        ]);
    }

    public function store(StoreEventArtistRequest $request, Event $event)
    {
        $this->authorize('manageParticipants', $event);

        $artistProfile = ArtistProfile::findOrFail($request->validated('artist_profile_id'));
        $status = $request->user()->organizerVouchesFor($artistProfile) ? 'ACEITO' : 'PENDENTE';

        $event->artists()->attach($artistProfile->id, ['eva_status' => $status]);

        return response()->json(['message' => 'Artista convidado para o evento.'], 201);
    }

    public function update(UpdateEventArtistRequest $request, Event $event, ArtistProfile $artistProfile)
    {
        abort_unless($request->user()->id === $artistProfile->user_id, 403);
        abort_unless($event->artists()->wherePivot('artist_profile_id', $artistProfile->id)->exists(), 404);

        $event->artists()->updateExistingPivot($artistProfile->id, $request->validated());

        return response()->json(['message' => 'Convite atualizado.']);
    }

    public function destroy(Request $request, Event $event, ArtistProfile $artistProfile)
    {
        $isSelf = $request->user()->id === $artistProfile->user_id;

        if (! $isSelf) {
            $this->authorize('manageParticipants', $event);
        }

        abort_unless($event->artists()->wherePivot('artist_profile_id', $artistProfile->id)->exists(), 404);

        $event->artists()->detach($artistProfile->id);

        return response()->json(['message' => 'Artista removido do evento.']);
    }
}
