<?php

namespace App\Services;

use App\Models\User;

class ProfileService
{
    /**
     * Get the authenticated user's profile.
     */
    public function show(User $user): User
    {
        return $user->load('profile');
    }

    /**
     * Update authenticated user's profile.
     */
    public function update(
        User $user,
        array $data
    ): User {

        $profile = $user->profile;

        $profile->update($data);

        return $user->fresh()->load('profile');
    }
}
