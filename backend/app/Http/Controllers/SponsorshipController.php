<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSponsorshipRequest;
use App\Http\Requests\UpdateSponsorshipRequest;
use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use App\Models\Sponsorship;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SponsorshipController extends Controller
{
    public function indexForEvent(Event $event)
    {
        return $this->alvoSponsorships($event, 'ACEITO');
    }

    public function indexForArtistProfile(ArtistProfile $artistProfile)
    {
        return $this->alvoSponsorships($artistProfile, 'ACEITO');
    }

    public function indexForGroup(Group $group)
    {
        return $this->alvoSponsorships($group, 'ACEITO');
    }

    public function pendingForEvent(Event $event)
    {
        $this->authorize('manageSponsorships', $event);

        return $this->alvoSponsorships($event, 'PROPOSTO');
    }

    public function pendingForArtistProfile(ArtistProfile $artistProfile)
    {
        $this->authorize('manageSponsorships', $artistProfile);

        return $this->alvoSponsorships($artistProfile, 'PROPOSTO');
    }

    public function pendingForGroup(Group $group)
    {
        $this->authorize('manageSponsorships', $group);

        return $this->alvoSponsorships($group, 'PROPOSTO');
    }

    public function storeForEvent(StoreSponsorshipRequest $request, Event $event)
    {
        return $this->store($request, $event);
    }

    public function storeForArtistProfile(StoreSponsorshipRequest $request, ArtistProfile $artistProfile)
    {
        return $this->store($request, $artistProfile);
    }

    public function storeForGroup(StoreSponsorshipRequest $request, Group $group)
    {
        return $this->store($request, $group);
    }

    public function index(Request $request)
    {
        return response()->json([
            'sponsorships' => $request->user()->sponsorships()->with('alvo')->get(),
        ]);
    }

    public function update(UpdateSponsorshipRequest $request, Sponsorship $sponsorship)
    {
        $this->authorize('manageSponsorships', $sponsorship->alvo);
        abort_unless($sponsorship->spo_active, 404);
        abort_unless($sponsorship->spo_status === 'PROPOSTO', 422, 'Essa proposta já foi respondida.');

        $sponsorship->update($request->validated());

        return response()->json(['sponsorship' => $sponsorship]);
    }

    public function destroy(Request $request, Sponsorship $sponsorship)
    {
        abort_unless($sponsorship->sponsor_id === $request->user()->id, 403);
        abort_unless($sponsorship->spo_status === 'PROPOSTO', 422, 'Só é possível cancelar propostas ainda não respondidas.');

        $sponsorship->update(['spo_active' => false]);

        return response()->json(['message' => 'Proposta de patrocínio cancelada.']);
    }

    private function alvoSponsorships(Model $alvo, string $status)
    {
        return response()->json([
            'sponsorships' => $alvo->sponsorships()->where('spo_status', $status)->where('spo_active', true)->get(),
        ]);
    }

    private function store(StoreSponsorshipRequest $request, Model $alvo)
    {
        $sponsorship = $alvo->sponsorships()->create([
            ...$request->validated(),
            'sponsor_type' => User::class,
            'sponsor_id' => $request->user()->id,
            'spo_status' => 'PROPOSTO',
            'spo_active' => true,
        ]);

        return response()->json(['sponsorship' => $sponsorship], 201);
    }
}
