<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\InvalidCurrentPasswordException;

class ProfileService
{
    public function __construct(
        private readonly MediaService $mediaService
    ) {}
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

    /**
     * Update authenticated user's avatar.
     */
    public function updateAvatar(
        User $user,
        UploadedFile $avatar
    ): User {

        $profile = $user->profile;

        /*
        |--------------------------------------------------------------------------
        | Delete Existing Avatar
        |--------------------------------------------------------------------------
        */

        if ($profile->avatar_public_id) {

            $this->mediaService->deleteImage(
                $profile->avatar_public_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Upload New Avatar
        |--------------------------------------------------------------------------
        */

        $upload = $this->mediaService->uploadImage(
            file: $avatar,
            folder: 'avatars',
            publicId: 'user_'.$user->id.'/profile'
        );

        /*
        |--------------------------------------------------------------------------
        | Save Public ID
        |--------------------------------------------------------------------------
        */

        $profile->update([
            'avatar_public_id' => $upload['public_id'],
        ]);

        return $user->fresh()->load('profile');
    }

    /**
     * Delete authenticated user's avatar.
     */
    public function deleteAvatar(
        User $user
    ): User {

        $profile = $user->profile;

        if (!$profile->avatar_public_id) {
            return $user->fresh()->load('profile');
        }

        /*
        |--------------------------------------------------------------------------
        | Delete From Cloudinary
        |--------------------------------------------------------------------------
        */

        $this->mediaService->deleteImage(
            $profile->avatar_public_id
        );

        /*
        |--------------------------------------------------------------------------
        | Clear Database
        |--------------------------------------------------------------------------
        */

        $profile->update([
            'avatar_public_id' => null,
        ]);

        return $user->fresh()->load('profile');
    }

    /**
     * Change authenticated user's password.
     */
    public function changePassword(
        User $user,
        array $data
    ): void {

        if (! Hash::check(
            $data['current_password'],
            $user->password
        )) {
            throw new InvalidCurrentPasswordException();
        }

        $user->update([
            'password' => Hash::make(
                $data['password']
            ),
        ]);
    }
}
