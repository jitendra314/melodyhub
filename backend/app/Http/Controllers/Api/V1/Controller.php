<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;
use App\Traits\ApiResponseTrait;

abstract class Controller extends BaseController
{
    use ApiResponseTrait;
}
