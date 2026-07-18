<?php

namespace App\Services;

use App\Mail\VerifyEmailOtpMail;
use App\Exceptions\EmailAlreadyVerifiedException;
use App\Exceptions\InvalidOtpException;
use App\Exceptions\OtpExpiredException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $otp = random_int(100000, 999999);

            $user = User::create([
                'name'                              => $data['name'],
                'email'                             => $data['email'],
                'password'                          => $data['password'],
                'email_verification_otp'            => $otp,
                'email_verification_otp_expires_at' => now()->addMinutes(10),
            ]);

            $this->sendVerificationOtp($user);

            return $user;
        });
    }

    public function verifyEmail(array $data): void
    {
        DB::transaction(function () use ($data) {

            $user = User::where('email', $data['email'])
                ->firstOrFail();

            if ($user->email_verified_at !== null) {
                throw new EmailAlreadyVerifiedException();
            }

            if (! hash_equals(
                $user->email_verification_otp,
                $data['otp']
            )) {
                throw new InvalidOtpException();
            }

            if (
                $user->email_verification_otp_expires_at === null ||
                now()->greaterThan($user->email_verification_otp_expires_at)
            ) {
                throw new OtpExpiredException();
            }

            $user->update([
                'email_verified_at' => now(),
                'email_verification_otp' => null,
                'email_verification_otp_expires_at' => null,
            ]);
        });
    }

    /**
     * Send verification OTP email.
     */
    private function sendVerificationOtp(User $user): void
    {
        Mail::to($user->email)
            ->send(new VerifyEmailOtpMail(
                $user->name,
                (string) $user->email_verification_otp
            ));
    }
}
