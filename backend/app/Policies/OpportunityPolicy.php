<?php

namespace App\Policies;

use App\Models\ArtistProfile;
use App\Models\Group;
use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    public function update(User $user, Opportunity $opportunity): bool
    {
        return $this->isCriador($user, $opportunity);
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        return $this->isCriador($user, $opportunity);
    }

    public function manageApplications(User $user, Opportunity $opportunity): bool
    {
        return $this->isCriador($user, $opportunity);
    }

    protected function isCriador(User $user, Opportunity $opportunity): bool
    {
        if ($opportunity->criador_type === ArtistProfile::class) {
            return $user->artistProfile?->id === $opportunity->criador_id;
        }

        if ($opportunity->criador_type === Group::class) {
            return $user->isGroupOwner($opportunity->criador) || $user->isGroupAdmin($opportunity->criador);
        }

        return false;
    }
}
