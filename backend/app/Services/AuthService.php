<?php

namespace App\Services;

use App\Exceptions\EmailAlreadyVerifiedException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\EmailNotVerifiedException;
use App\Enums\OtpType;

class AuthService
{
    public function __construct(
        private readonly OtpService $otpService
    ) {}

    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'],
            ]);

            $this->otpService->generateAndSendEmailVerificationOtp($user);

            return $user->fresh();
        });
    }

    /**
     * Authenticate user and generate JWT token.
     */
    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])
            ->first();

        if (! $user) {
            throw new InvalidCredentialsException();
        }

        if (! Hash::check($data['password'], $user->password)) {
            throw new InvalidCredentialsException();
        }

        if ($user->email_verified_at === null) {
            throw new EmailNotVerifiedException();
        }

        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }

        /**
         * Verify user's email using OTP.
     */
    public function verifyEmail(array $data): void
    {
        DB::transaction(function () use ($data) {

            $user = $this->getUnverifiedUser($data['email']);

            $this->otpService->validate(
                $user,
                OtpType::EMAIL_VERIFICATION,
                $data['otp']
            );

            $user->update([
                'email_verified_at' => now(),
            ]);
        });
    }

    /**
     * Resend verification OTP.
     */
    public function resendOtp(array $data): void
    {
        DB::transaction(function () use ($data) {

            $user = $this->getUnverifiedUser($data['email']);

            $this->otpService->ensureOtpRequestAllowed(
                $user->email
            );

            $this->otpService->generateAndSendEmailVerificationOtp($user);
        });
    }

    /**
     * Get authenticated user.
     */
    public function me(): User
    {
        return auth('api')->user();
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): array
    {
        $token = auth('api')->refresh();

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ];
    }

    /**
     * Logout.
     */
    public function logout(): void
    {
        auth('api')->logout();
    }

    /**
     * Get an unverified user by email.
     */
    private function getUnverifiedUser(string $email): User
    {
        $user = User::where('email', $email)
            ->firstOrFail();

        if ($user->email_verified_at !== null) {
            throw new EmailAlreadyVerifiedException();
        }

        return $user;
    }
}
