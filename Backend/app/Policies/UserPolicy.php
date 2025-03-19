<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the authenticated user can delete the given user.
     *
     * @param  \App\Models\User  $authUser  The currently authenticated user.
     * @param  \App\Models\User  $user      The user being deleted.
     * @return bool
     */
    public function delete(User $authUser, User $user)
    {
        // Allow deletion only if the authenticated user is deleting their own account.
        return $authUser->id === $user->id;
    }
}
