<?php

namespace App\Policies;

use App\Models\ArtistProfile;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;

class EventPolicy
{
    public function update(User $user, Event $event): bool
    {
        return $this->isOrganizer($user, $event);
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->isOrganizer($user, $event);
    }

    public function manageSessions(User $user, Event $event): bool
    {
        return $this->isOrganizer($user, $event);
    }

    public function manageMedia(User $user, Event $event): bool
    {
        return $this->isOrganizer($user, $event);
    }

    protected function isOrganizer(User $user, Event $event): bool
    {
        if ($event->organizador_type === ArtistProfile::class) {
            return $user->artistProfile?->id === $event->organizador_id;
        }

        if ($event->organizador_type === Group::class) {
            return $user->isGroupOwner($event->organizador) || $user->isGroupAdmin($event->organizador);
        }

        return false;
    }
}
