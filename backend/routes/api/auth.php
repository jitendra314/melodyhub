<?php

use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-email',[AuthController::class, 'verifyEmail']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/logout', [AuthController::class, 'logout']);

    });

});
