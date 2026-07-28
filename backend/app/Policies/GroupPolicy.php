<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function update(User $user, Group $group): bool
    {
        return $user->isGroupOwner($group) || $user->isGroupAdmin($group);
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->isGroupOwner($group);
    }

    public function manageMembers(User $user, Group $group): bool
    {
        return $user->isGroupOwner($group) || $user->isGroupAdmin($group);
    }
}