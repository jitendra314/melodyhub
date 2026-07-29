<?php

use App\Http\Controllers\Api\V1\ArtistController;

Route::prefix('artists')->group(function () {

    /**
     * Public Routes
     */
    Route::get('/', [ArtistController::class, 'index']);
    Route::get('/{artist}', [ArtistController::class, 'show']);

    /**
     * Protected Routes
     */
    Route::middleware('auth:api')->group(function () {

        Route::post('/', [ArtistController::class, 'store']);
        Route::put('/{artist}', [ArtistController::class, 'update']);
        Route::delete('/{artist}', [ArtistController::class, 'destroy']);

    });

});
