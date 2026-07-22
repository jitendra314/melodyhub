<?php

namespace App\Services;

use App\Enums\OtpType;
use App\Exceptions\InvalidOtpException;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpExpiredException;
use App\Exceptions\OtpRateLimitException;
use App\Mail\VerifyEmailOtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    /**
     * Generate and send email verification OTP.
     */
    public function generateAndSendEmailVerificationOtp(User $user): void
    {
        DB::transaction(function () use ($user) {

            $otp = $this->createOtp(
                $user,
                OtpType::EMAIL_VERIFICATION
            );

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
    public function validate(
        User $user,
        OtpType $type,
        string $otp
    ): void {
        $otpRecord = Otp::where('user_id', $user->id)
            ->where('type', $type->value)
            ->first();

        if (! $otpRecord) {
            throw new InvalidOtpException();
        }

        if (
            $otpRecord->expires_at === null ||
            now()->greaterThan($otpRecord->expires_at)
        ) {
            throw new OtpExpiredException();
        }

        if (! hash_equals($otpRecord->otp, $otp)) {
            throw new InvalidOtpException();
        }

        $otpRecord->update([
            'verified_at' => now(),
        ]);
    }

    /**
     * Ensure user can request another OTP.
     */
    public function ensureOtpRequestAllowed(string $email): void
    {
        $email = strtolower($email);

        $cooldownKey = "otp-cooldown:{$email}";
        $attemptKey = "otp-attempts:{$email}";

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            throw new OtpCooldownException(
                RateLimiter::availableIn($cooldownKey)
            );
        }

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
     * Create or update OTP.
     */
    private function createOtp(
        User $user,
        OtpType $type
    ): string {
        $otp = $this->generateOtp();

        Otp::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type->value,
            ],
            [
                'otp' => $otp,
                'expires_at' => now()->addMinutes(
                    config('auth.email_otp_expiry')
                ),
                'verified_at' => null,
            ]
        );

        return $otp;
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
