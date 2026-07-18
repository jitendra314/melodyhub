<?php

namespace App\Exceptions;

use Exception;

class InvalidOtpException extends Exception
{
    protected $message = 'Invalid OTP.';
}
