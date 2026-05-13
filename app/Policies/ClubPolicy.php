<?php

namespace App\Policies;

use App\Models\Club;
use App\Models\User;

class ClubPolicy
{
    // Only admin can create or delete clubs
    public function manage(User $user) {
        return $user->is_admin;
    }

    // Only the leader of THIS club can manage its settings or members
    public function update(User $user, Club $club) {
        return $user->id === $club->leader_id || $user->is_admin;
    }
}