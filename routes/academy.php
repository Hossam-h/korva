<?php

use App\Http\Controllers\Api\Academy\AuthController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'academy',
], function () {
    // Public routes (no auth)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('check-otp', [AuthController::class, 'checkOtp']);

    // Protected routes (requires academy JWT)
    Route::middleware('auth:academy')->group(function () {
        Route::post('onboarding', [AuthController::class, 'onBoarding']);
    });
});
