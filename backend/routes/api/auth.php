<?php

use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email',[AuthController::class, 'verifyEmail']);

});
