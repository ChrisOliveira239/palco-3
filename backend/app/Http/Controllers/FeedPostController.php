<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedPostRequest;
use App\Http\Requests\UpdateFeedPostRequest;
use App\Models\ArtistProfile;
use App\Models\FeedPost;
use App\Models\Group;
use Illuminate\Database\Eloquent\Model;

class FeedPostController extends Controller
{
    public function indexForArtistProfile(ArtistProfile $artistProfile)
    {
        return $this->index($artistProfile);
    }

    public function indexForGroup(Group $group)
    {
        return $this->index($group);
    }

    public function storeForArtistProfile(StoreFeedPostRequest $request, ArtistProfile $artistProfile)
    {
        return $this->store($request, $artistProfile);
    }

    public function storeForGroup(StoreFeedPostRequest $request, Group $group)
    {
        return $this->store($request, $group);
    }

    public function updateForArtistProfile(UpdateFeedPostRequest $request, ArtistProfile $artistProfile, FeedPost $feedPost)
    {
        return $this->update($request, $artistProfile, $feedPost);
    }

    public function updateForGroup(UpdateFeedPostRequest $request, Group $group, FeedPost $feedPost)
    {
        return $this->update($request, $group, $feedPost);
    }

    public function destroyForArtistProfile(ArtistProfile $artistProfile, FeedPost $feedPost)
    {
        return $this->destroy($artistProfile, $feedPost);
    }

    public function destroyForGroup(Group $group, FeedPost $feedPost)
    {
        return $this->destroy($group, $feedPost);
    }

    private function index(Model $alvo)
    {
        return response()->json([
            'feed_posts' => $alvo->feedPosts()->where('fee_active', true)->latest()->get(),
        ]);
    }

    private function store(StoreFeedPostRequest $request, Model $alvo)
    {
        $this->authorize('manageFeedPosts', $alvo);

        $feedPost = $alvo->feedPosts()->create([...$request->validated(), 'fee_active' => true]);

        return response()->json(['feed_post' => $feedPost], 201);
    }

    private function update(UpdateFeedPostRequest $request, Model $alvo, FeedPost $feedPost)
    {
        $this->authorize('manageFeedPosts', $alvo);
        abort_unless($feedPost->autor_type === get_class($alvo) && $feedPost->autor_id === $alvo->id, 404);

        $feedPost->update($request->validated());

        return response()->json(['feed_post' => $feedPost]);
    }

    private function destroy(Model $alvo, FeedPost $feedPost)
    {
        $this->authorize('manageFeedPosts', $alvo);
        abort_unless($feedPost->autor_type === get_class($alvo) && $feedPost->autor_id === $alvo->id, 404);

        $feedPost->update(['fee_active' => false]);

        return response()->json(['message' => 'Post desativado.']);
    }
}
