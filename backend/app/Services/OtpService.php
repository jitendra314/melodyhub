<?php

namespace App\Services;

use App\Exceptions\InvalidOtpException;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpExpiredException;
use App\Mail\VerifyEmailOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\OtpRateLimitException;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    /**
     * Generate and send email verification OTP.
     */
    public function generateAndSendEmailVerificationOtp(User $user): void
    {
        DB::transaction(function () use ($user) {

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
        });

        $this->recordOtpRequest($user->email);
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
     * Ensure user can request another OTP.
     */
    public function ensureOtpRequestAllowed(string $email): void
    {
        $email = strtolower($email);

        $cooldownKey = "otp-cooldown:{$email}";
        $attemptKey = "otp-attempts:{$email}";

        /*
        |--------------------------------------------------------------------------
        | Cooldown Check (60 Seconds)
        |--------------------------------------------------------------------------
        */

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            throw new OtpCooldownException(
                RateLimiter::availableIn($cooldownKey)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Attempts Check (3 in 10 Minutes)
        |--------------------------------------------------------------------------
        */

        if (
            RateLimiter::tooManyAttempts(
                $attemptKey,
                config('auth.otp_max_attempts')
            )
        ) {
            throw new OtpRateLimitException();
        }
    }

    /**
     * Generate a 6-digit OTP.
     */
    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * Record a successful OTP request.
     */
    private function recordOtpRequest(string $email): void
    {
        $email = strtolower($email);

        RateLimiter::hit(
            "otp-cooldown:{$email}",
            config('auth.otp_cooldown')
        );

        RateLimiter::hit(
            "otp-attempts:{$email}",
            config('auth.otp_decay_minutes') * 60
        );
    }
}
