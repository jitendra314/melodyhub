<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProfileResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    /**
     * Display the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->profileService->show(
            $request->user()
        );

        return $this->successResponse(
            data: new ProfileResource($user),
            message: 'Profile retrieved successfully.'
        );
    }

    public function update(
        UpdateProfileRequest $request
    ): JsonResponse {

        $user = $this->profileService->update(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            data: new ProfileResource($user),
            message: 'Profile updated successfully.'
        );
    }

    /**
     * Update authenticated user's avatar.
     */
    public function updateAvatar(
        UpdateAvatarRequest $request
    ): JsonResponse {

        $user = $this->profileService->updateAvatar(
            $request->user(),
            $request->file('avatar')
        );

        return $this->successResponse(
            data: new ProfileResource($user),
            message: 'Avatar updated successfully.'
        );
    }

    /**
     * Remove authenticated user's avatar.
     */
    public function deleteAvatar(): JsonResponse
    {
        $user = $this->profileService->deleteAvatar(
            auth()->user()
        );

        return $this->successResponse(
            data: new ProfileResource($user),
            message: 'Avatar removed successfully.'
        );
    }

    /**
     * Change authenticated user's password.
     */
    public function changePassword(
        ChangePasswordRequest $request
    ): JsonResponse {

        $this->profileService->changePassword(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            message: 'Password changed successfully.'
        );
    }
}
