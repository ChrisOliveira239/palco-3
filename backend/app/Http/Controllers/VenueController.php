<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVenueRequest;
use App\Http\Requests\UpdateVenueRequest;
use App\Models\Venue;

class VenueController extends Controller
{
    public function index()
    {
        return response()->json(['venues' => Venue::where('ven_active', true)->get()]);
    }

    public function show(Venue $venue)
    {
        abort_unless($venue->ven_active, 404);

        return response()->json(['venue' => $venue]);
    }

    public function store(StoreVenueRequest $request)
    {
        return response()->json(['venue' => Venue::create($request->validated())], 201);
    }

    public function update(UpdateVenueRequest $request, Venue $venue)
    {
        $venue->update($request->validated());

        return response()->json(['venue' => $venue]);
    }

    public function destroy(Venue $venue)
    {
        $venue->update(['ven_active' => false]);

        return response()->json(['message' => 'Local desativado.']);
    }
}
