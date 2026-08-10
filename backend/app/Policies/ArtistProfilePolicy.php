<?php

namespace App\Policies;

use App\Models\ArtistProfile;
use App\Models\User;

class ArtistProfilePolicy
{
    public function update(User $user, ArtistProfile $artistProfile): bool
    {
        return $user->id === $artistProfile->user_id;
    }

    public function delete(User $user, ArtistProfile $artistProfile): bool
    {
        return $user->id === $artistProfile->user_id;
    }

    public function manageSponsorships(User $user, ArtistProfile $artistProfile): bool
    {
        return $user->id === $artistProfile->user_id;
    }

    public function manageFeedPosts(User $user, ArtistProfile $artistProfile): bool
    {
        return $user->id === $artistProfile->user_id;
    }
}
