<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Models\ArtistProfile;
use App\Models\Group;
use App\Models\Opportunity;

class OpportunityController extends Controller
{
    public function index()
    {
        return response()->json([
            'opportunities' => Opportunity::where('opp_status', 'ABERTA')->where('opp_active', true)->paginate(),
        ]);
    }

    public function show(Opportunity $opportunity)
    {
        abort_unless($opportunity->opp_status === 'ABERTA' && $opportunity->opp_active, 404);

        return response()->json(['opportunity' => $opportunity]);
    }

    public function store(StoreOpportunityRequest $request)
    {
        $criadorClass = [
            'artist_profile' => ArtistProfile::class,
            'group' => Group::class,
        ][$request->validated('criador_type')];

        $opportunity = Opportunity::create([
            ...$request->safe()->except('criador_type'),
            'criador_type' => $criadorClass,
            'opp_status' => 'ABERTA',
            'opp_active' => true,
        ]);

        return response()->json(['opportunity' => $opportunity], 201);
    }

    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity)
    {
        $this->authorize('update', $opportunity);

        $opportunity->update($request->validated());

        return response()->json(['opportunity' => $opportunity]);
    }

    public function destroy(Opportunity $opportunity)
    {
        $this->authorize('delete', $opportunity);

        $opportunity->update(['opp_active' => false]);

        return response()->json(['message' => 'Oportunidade desativada.']);
    }
}
