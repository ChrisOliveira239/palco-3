<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtistProfileRequest;
use App\Http\Requests\UpdateArtistProfileRequest;
use App\Models\ArtistProfile;

class ArtistProfileController extends Controller
{
    public function index()
    {
        return response()->json([
            'artist_profiles' => ArtistProfile::where('art_active', true)->paginate(),
        ]);
    }

    public function show(ArtistProfile $artistProfile)
    {
        abort_if(! $artistProfile->art_active, 404);

        return response()->json(['artist_profile' => $artistProfile]);
    }

    public function store(StoreArtistProfileRequest $request)
    {
        $artistProfile = ArtistProfile::create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
        ]);

        return response()->json(['artist_profile' => $artistProfile], 201);
    }

    public function update(UpdateArtistProfileRequest $request, ArtistProfile $artistProfile)
    {
        $this->authorize('update', $artistProfile);

        $artistProfile->update($request->validated());

        return response()->json(['artist_profile' => $artistProfile]);
    }

    public function destroy(ArtistProfile $artistProfile)
    {
        $this->authorize('delete', $artistProfile);

        $artistProfile->update(['art_active' => false]);

        return response()->json(['message' => 'Perfil de artista desativado.']);
    }
}
