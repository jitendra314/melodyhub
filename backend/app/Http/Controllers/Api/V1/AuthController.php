<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register(
            $request->validated()
        );

        return $this->successResponse(
            data: new UserResource($user),
            message: 'Registration successful. Please verify your email using the OTP sent to your email.',
            status: 201
        );
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $this->authService->verifyEmail(
            $request->validated()
        );

        return $this->successResponse(
            message: 'Email verified successfully.'
        );
    }
}
