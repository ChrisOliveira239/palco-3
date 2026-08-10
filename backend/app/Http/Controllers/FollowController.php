<?php

namespace App\Http\Controllers;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function followArtistProfile(Request $request, ArtistProfile $artistProfile)
    {
        return $this->follow($request, $artistProfile);
    }

    public function followGroup(Request $request, Group $group)
    {
        return $this->follow($request, $group);
    }

    public function followEvent(Request $request, Event $event)
    {
        return $this->follow($request, $event);
    }

    public function unfollowArtistProfile(Request $request, ArtistProfile $artistProfile)
    {
        return $this->unfollow($request, $artistProfile);
    }

    public function unfollowGroup(Request $request, Group $group)
    {
        return $this->unfollow($request, $group);
    }

    public function unfollowEvent(Request $request, Event $event)
    {
        return $this->unfollow($request, $event);
    }

    public function followersForArtistProfile(ArtistProfile $artistProfile)
    {
        return $this->followers($artistProfile);
    }

    public function followersForGroup(Group $group)
    {
        return $this->followers($group);
    }

    public function followersForEvent(Event $event)
    {
        return $this->followers($event);
    }

    public function following(Request $request)
    {
        return response()->json([
            'follows' => $request->user()->follows()->with('seguivel')->get(),
        ]);
    }

    private function follow(Request $request, Model $alvo)
    {
        $user = $request->user();
        abort_if($this->isSelf($user, $alvo), 422, 'Você não pode seguir a si mesmo.');
        abort_if(
            Follow::where('user_id', $user->id)->where('seguivel_type', get_class($alvo))->where('seguivel_id', $alvo->id)->exists(),
            422,
            'Você já segue esse perfil.'
        );

        $follow = Follow::create([
            'user_id' => $user->id,
            'seguivel_type' => get_class($alvo),
            'seguivel_id' => $alvo->id,
        ]);

        return response()->json(['follow' => $follow], 201);
    }

    private function unfollow(Request $request, Model $alvo)
    {
        $follow = Follow::where('user_id', $request->user()->id)
            ->where('seguivel_type', get_class($alvo))
            ->where('seguivel_id', $alvo->id)
            ->first();

        abort_unless($follow, 404);

        $follow->delete();

        return response()->json(['message' => 'Deixou de seguir.']);
    }

    private function followers(Model $alvo)
    {
        return response()->json(['followers' => $alvo->followers()->get()]);
    }

    private function isSelf(User $user, Model $alvo): bool
    {
        if ($alvo instanceof ArtistProfile) {
            return $user->artistProfile?->id === $alvo->id;
        }

        if ($alvo instanceof Group) {
            return $user->isGroupOwner($alvo) || $user->isGroupAdmin($alvo);
        }

        if ($alvo instanceof Event) {
            if ($alvo->organizador_type === ArtistProfile::class) {
                return $user->artistProfile?->id === $alvo->organizador_id;
            }

            if ($alvo->organizador_type === Group::class) {
                return $user->isGroupOwner($alvo->organizador) || $user->isGroupAdmin($alvo->organizador);
            }
        }

        return false;
    }
}
