<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

abstract class Controller extends BaseController
{
    use ApiResponseTrait;

    /**
     * Get pagination size.
     */
    protected function perPage(
        Request $request
    ): int {

        return min(

            max(
                $request->integer(
                    'per_page',
                    15
                ),
                1
            ),

            100

        );
    }
}
