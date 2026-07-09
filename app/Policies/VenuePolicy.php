<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venue;

class VenuePolicy
{
    /**
     * The owner or an administrator may manage a facility.
     */
    public function update(User $user, Venue $venue): bool
    {
        return $user->id === $venue->user_id || $user->isAdmin();
    }

    public function delete(User $user, Venue $venue): bool
    {
        return $this->update($user, $venue);
    }
}
