<?php

namespace App\Exceptions;

use Exception;

class OtpCooldownException extends Exception
{
    public function __construct(int $seconds)
    {
        parent::__construct(
            "Please wait {$seconds} seconds before requesting another OTP."
        );
    }
}
