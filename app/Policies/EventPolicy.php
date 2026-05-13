<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    // Only club leader can add/edit events for their club
    public function manage(User $user, Event $event) {
        return $user->id === $event->club->leader_id || $user->is_admin;
    }
}