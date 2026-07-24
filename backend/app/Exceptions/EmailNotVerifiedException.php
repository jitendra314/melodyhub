<?php

namespace App\Exceptions;

use Exception;

class EmailNotVerifiedException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Please verify your email before logging in.'
        );
    }
}
