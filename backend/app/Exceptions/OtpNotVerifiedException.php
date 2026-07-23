<?php

namespace App\Exceptions;

use Exception;

class OtpNotVerifiedException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Please verify the OTP before resetting your password.'
        );
    }
}
