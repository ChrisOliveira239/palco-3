<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcceptedSupportTypeRequest;
use App\Models\AcceptedSupportType;
use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Database\Eloquent\Model;

class AcceptedSupportTypeController extends Controller
{
    public function indexForEvent(Event $event)
    {
        return $this->index($event);
    }

    public function indexForArtistProfile(ArtistProfile $artistProfile)
    {
        return $this->index($artistProfile);
    }

    public function indexForGroup(Group $group)
    {
        return $this->index($group);
    }

    public function storeForEvent(StoreAcceptedSupportTypeRequest $request, Event $event)
    {
        return $this->store($request, $event);
    }

    public function storeForArtistProfile(StoreAcceptedSupportTypeRequest $request, ArtistProfile $artistProfile)
    {
        return $this->store($request, $artistProfile);
    }

    public function storeForGroup(StoreAcceptedSupportTypeRequest $request, Group $group)
    {
        return $this->store($request, $group);
    }

    public function destroyForEvent(Event $event, AcceptedSupportType $acceptedSupportType)
    {
        return $this->destroy($event, $acceptedSupportType);
    }

    public function destroyForArtistProfile(ArtistProfile $artistProfile, AcceptedSupportType $acceptedSupportType)
    {
        return $this->destroy($artistProfile, $acceptedSupportType);
    }

    public function destroyForGroup(Group $group, AcceptedSupportType $acceptedSupportType)
    {
        return $this->destroy($group, $acceptedSupportType);
    }

    private function index(Model $alvo)
    {
        return response()->json(['accepted_support_types' => $alvo->acceptedSupportTypes]);
    }

    private function store(StoreAcceptedSupportTypeRequest $request, Model $alvo)
    {
        $this->authorize('manageSponsorships', $alvo);

        $acceptedSupportType = $alvo->acceptedSupportTypes()->create($request->validated());

        return response()->json(['accepted_support_type' => $acceptedSupportType], 201);
    }

    private function destroy(Model $alvo, AcceptedSupportType $acceptedSupportType)
    {
        $this->authorize('manageSponsorships', $alvo);
        abort_unless(
            $acceptedSupportType->alvo_type === get_class($alvo) && $acceptedSupportType->alvo_id === $alvo->id,
            404
        );

        $acceptedSupportType->delete();

        return response()->json(['message' => 'Tipo de apoio removido.']);
    }
}
