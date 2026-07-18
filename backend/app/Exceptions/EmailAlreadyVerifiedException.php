<?php

namespace App\Exceptions;

use Exception;

class EmailAlreadyVerifiedException extends Exception
{
    protected $message = 'Email is already verified.';
}
