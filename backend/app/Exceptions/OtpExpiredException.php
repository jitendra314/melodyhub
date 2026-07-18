<?php

namespace App\Exceptions;

use Exception;

class OtpExpiredException extends Exception
{
    protected $message = 'OTP has expired. Please request a new OTP.';
}
