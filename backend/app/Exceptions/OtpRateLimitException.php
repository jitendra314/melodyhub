<?php

namespace App\Exceptions;

use Exception;

class OtpRateLimitException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Maximum OTP requests reached. Please try again after 10 minutes.'
        );
    }
}
