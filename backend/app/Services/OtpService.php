<?php

namespace App\Services;

use App\Enums\OtpType;
use App\Exceptions\InvalidOtpException;
use App\Exceptions\OtpCooldownException;
use App\Exceptions\OtpExpiredException;
use App\Exceptions\OtpNotVerifiedException;
use App\Exceptions\OtpRateLimitException;
use App\Mail\PasswordResetOtpMail;
use App\Mail\VerifyEmailOtpMail;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    /**
     * Generate and send OTP.
     */
    public function generateAndSend(
        User $user,
        OtpType $type
    ): void {
        DB::transaction(function () use ($user, $type) {

            $otp = $this->createOtp(
                $user,
                $type
            );

            $this->sendOtpMail(
                $user,
                $type,
                $otp
            );
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
        $otpRecord = $this->getOtpRecord(
            $user,
            $type
        );

        if (
            $otpRecord->expires_at === null ||
            now()->greaterThan($otpRecord->expires_at)
        ) {
            throw new OtpExpiredException();
        }

        if (! hash_equals($otpRecord->otp, $otp)) {
            throw new InvalidOtpException();
        }
    }

    /**
     * Mark OTP as verified.
     */
    public function markVerified(
        User $user,
        OtpType $type
    ): void {
        $otpRecord = $this->getOtpRecord(
            $user,
            $type
        );

        $otpRecord->update([
            'verified_at' => now(),
        ]);
    }

    /**
     * Ensure OTP has been verified.
     */
    public function ensureVerified(
        User $user,
        OtpType $type
    ): void {
        $otpRecord = $this->getOtpRecord(
            $user,
            $type
        );

        if ($otpRecord->verified_at === null) {
            throw new OtpNotVerifiedException();
        }
    }

    /**
     * Delete OTP.
     */
    public function delete(
        User $user,
        OtpType $type
    ): void {
        Otp::where('user_id', $user->id)
            ->where('type', $type->value)
            ->delete();
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
        | Cooldown Check
        |--------------------------------------------------------------------------
        */

        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            throw new OtpCooldownException(
                RateLimiter::availableIn($cooldownKey)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Attempts Check
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
     * Get OTP record.
     */
    private function getOtpRecord(
        User $user,
        OtpType $type
    ): Otp {
        $otpRecord = Otp::where('user_id', $user->id)
            ->where('type', $type->value)
            ->first();

        if (! $otpRecord) {
            throw new InvalidOtpException();
        }

        return $otpRecord;
    }

    /**
     * Send OTP email.
     */
    private function sendOtpMail(
        User $user,
        OtpType $type,
        string $otp
    ): void {
        switch ($type) {

            case OtpType::EMAIL_VERIFICATION:

                Mail::to($user->email)
                    ->send(
                        new VerifyEmailOtpMail(
                            $user->name,
                            $otp
                        )
                    );

                break;

            case OtpType::PASSWORD_RESET:

                Mail::to($user->email)
                    ->send(
                        new PasswordResetOtpMail(
                            $user->name,
                            $otp
                        )
                    );

                break;
        }
    }

    /**
     * Generate a 6-digit OTP.
     */
    private function generateOtp(): string
    {
        return (string) random_int(
            100000,
            999999
        );
    }

    /**
     * Record OTP request.
     */
    private function recordOtpRequest(
        string $email
    ): void {
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
