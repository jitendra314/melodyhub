<?php

use App\Http\Controllers\Api\V1\ProfileController;

Route::middleware('auth:api')->group(function () {

    Route::prefix('profile')->group(function () {

        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar']);
        Route::put('/change-password', [ProfileController::class, 'changePassword']);

    });

});
