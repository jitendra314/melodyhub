<?php

use App\Http\Controllers\Api\V1\ProfileController;

Route::middleware('auth:api')->group(function () {

    Route::prefix('profile')->group(function () {

        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);

    });

});
