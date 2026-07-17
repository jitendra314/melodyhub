<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->authService->register(
            $request->validated()
        );

        return $this->successResponse(
            data: $data,
            message: 'Registration successful.',
            status: 201
        );
    }
}
