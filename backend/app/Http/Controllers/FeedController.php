<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use App\Models\FeedPost;
use App\Models\Group;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $artistProfileIds = $user->follows()->where('seguivel_type', ArtistProfile::class)->pluck('seguivel_id');
        $groupIds = $user->follows()->where('seguivel_type', Group::class)->pluck('seguivel_id');

        $posts = FeedPost::where('fee_active', true)
            ->where(function ($query) use ($artistProfileIds, $groupIds) {
                $query->where(function ($q) use ($artistProfileIds) {
                    $q->where('autor_type', ArtistProfile::class)->whereIn('autor_id', $artistProfileIds);
                })->orWhere(function ($q) use ($groupIds) {
                    $q->where('autor_type', Group::class)->whereIn('autor_id', $groupIds);
                });
            })
            ->with('autor')
            ->latest()
            ->paginate();

        return response()->json(['feed' => $posts]);
    }
}
