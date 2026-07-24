<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProfileResource;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Profile\UpdateProfileRequest;

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
}
