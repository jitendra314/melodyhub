<?php

namespace App\Exceptions;

use Exception;

class InvalidCurrentPasswordException extends Exception
{
    protected $message = 'Current password is incorrect.';
}
