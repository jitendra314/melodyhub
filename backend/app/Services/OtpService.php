<?php

namespace App\Services;

use App\Exceptions\InvalidOtpException;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpExpiredException;
use App\Mail\VerifyEmailOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    /**
     * Generate and send email verification OTP.
     */
    public function generateAndSend(User $user): void
    {
        $otp = $this->generateOtp();

        $user->update([
            'email_verification_otp' => $otp,
            'email_verification_otp_expires_at' => now()->addMinutes(
                config('auth.email_otp_expiry')
            ),
        ]);

        Mail::to($user->email)
            ->send(new VerifyEmailOtpMail(
                $user->name,
                $otp
            ));
    }

    /**
     * Validate OTP.
     */
    public function validate(User $user, string $otp): void
    {
        if (! hash_equals(
            (string) $user->email_verification_otp,
            $otp
        )) {
            throw new InvalidOtpException();
        }

        if (
            $user->email_verification_otp_expires_at === null ||
            now()->greaterThan(
                $user->email_verification_otp_expires_at
            )
        ) {
            throw new OtpExpiredException();
        }
    }

    /**
     * Ensure resend cooldown.
     */
    public function ensureCooldown(string $email): void
    {
        $key = 'otp:' . strtolower($email);

        if (RateLimiter::tooManyAttempts($key, 1)) {
            throw new OtpCooldownException(
                RateLimiter::availableIn($key)
            );
        }

        RateLimiter::hit(
            $key,
            config('auth.otp_cooldown')
        );
    }

    /**
     * Generate a 6-digit OTP.
     */
    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }
}
