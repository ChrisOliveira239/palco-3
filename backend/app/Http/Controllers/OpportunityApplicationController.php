<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOpportunityApplicationRequest;
use App\Http\Requests\UpdateOpportunityApplicationRequest;
use App\Models\Opportunity;
use App\Models\OpportunityApplication;
use Illuminate\Http\Request;

class OpportunityApplicationController extends Controller
{
    public function index(Opportunity $opportunity)
    {
        $this->authorize('manageApplications', $opportunity);

        return response()->json([
            'applications' => $opportunity->applications()->with('user')->get(),
        ]);
    }

    public function store(StoreOpportunityApplicationRequest $request, Opportunity $opportunity)
    {
        abort_unless($opportunity->opp_active, 404);
        abort_unless($opportunity->opp_status === 'ABERTA', 422, 'Oportunidade não está aberta.');
        abort_if($opportunity->applications()->where('user_id', $request->user()->id)->exists(), 422, 'Você já se candidatou a essa oportunidade.');

        $application = $opportunity->applications()->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'opa_status' => 'PENDENTE',
            'opa_active' => true,
        ]);

        return response()->json(['application' => $application], 201);
    }

    public function update(UpdateOpportunityApplicationRequest $request, Opportunity $opportunity, OpportunityApplication $application)
    {
        $this->authorize('manageApplications', $opportunity);
        abort_unless($application->opportunity_id === $opportunity->id, 404);
        abort_unless($application->opa_active, 404);
        abort_unless($application->opa_status === 'PENDENTE', 422, 'Essa candidatura já foi respondida.');

        $application->update($request->validated());

        return response()->json(['application' => $application]);
    }

    public function destroy(Request $request, Opportunity $opportunity, OpportunityApplication $application)
    {
        abort_unless($application->opportunity_id === $opportunity->id, 404);
        abort_unless($application->user_id === $request->user()->id, 403);
        abort_unless($application->opa_status === 'PENDENTE', 422, 'Só é possível cancelar candidaturas ainda não respondidas.');

        $application->update(['opa_active' => false]);

        return response()->json(['message' => 'Candidatura cancelada.']);
    }

    public function mine(Request $request)
    {
        return response()->json([
            'applications' => $request->user()->opportunityApplications()->with('opportunity')->get(),
        ]);
    }
}
