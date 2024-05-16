<?php

namespace App\Policies;

use App\Todo;
use App\User;

class PermissionTodo
{
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Todo $todo): bool
    {
        return $user->id == $todo->creator_id || $todo->contacts()->where('user_contact_id', $user->id)->exists() || $todo->parent->contacts()->where('user_contact_id', $user->id)->exists() || $todo->parent->creator_id == $user->id;
    }
}
