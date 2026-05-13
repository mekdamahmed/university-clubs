<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // Leader can manage tasks of their club
    public function manage(User $user, Task $task) {
        return $user->id === $task->club->leader_id || $user->is_admin;
    }

    // Member can only view or mark their own task as completed
    public function complete(User $user, Task $task) {
        return $user->id === $task->assigned_to;
    }
}