<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register a new user.
     */
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

    /**
     * Login user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login(
            $request->validated()
        );

        $data['user'] = new UserResource($data['user']);

        return $this->successResponse(
            data: $data,
            message: 'Login successful.'
        );
    }

    /**
     * Verify user's email.
     */
    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        $this->authService->verifyEmail(
            $request->validated()
        );

        return $this->successResponse(
            message: 'Email verified successfully.'
        );
    }

    /**
     * Resend verification OTP.
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $this->authService->resendOtp(
            $request->validated()
        );

        return $this->successResponse(
            message: 'A new verification OTP has been sent to your email.'
        );
    }

    /**
     * Get authenticated user.
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->me();

        return $this->successResponse(
            data: new UserResource($user),
            message: 'Authenticated user fetched successfully.'
        );
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): JsonResponse
    {
        $data = $this->authService->refresh();

        return $this->successResponse(
            data: $data,
            message: 'Token refreshed successfully.'
        );
    }

    /**
     * Logout user.
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->successResponse(
            message: 'Logged out successfully.'
        );
    }
}
