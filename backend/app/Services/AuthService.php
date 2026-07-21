<?php

namespace App\Services;

use App\Exceptions\EmailAlreadyVerifiedException;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
     * Verify user's email using OTP.
     */
    public function verifyEmail(array $data): void
    {
        DB::transaction(function () use ($data) {

            $user = $this->getUnverifiedUser($data['email']);

            $this->otpService->validate(
                $user,
                $data['otp']
            );

            $user->update([
                'email_verified_at' => now(),
                'email_verification_otp' => null,
                'email_verification_otp_expires_at' => null,
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
