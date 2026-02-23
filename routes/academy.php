<?php

use App\Http\Controllers\Api\Academy\AuthController;
use App\Http\Controllers\Api\Academy\CoachController;
use App\Http\Controllers\Api\Academy\FieldController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'academy',
], function () {
    // Public routes (no auth)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('check-otp', [AuthController::class, 'checkOtp']);

    Route::post('forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('check-otp-forget', [AuthController::class, 'checkOtpForget']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // Protected routes (requires academy JWT)
    Route::middleware('auth:academy')->group(function () {
        Route::post('onboarding', [AuthController::class, 'onBoarding']);

        // Fields
        Route::apiResource('fields', FieldController::class);

        // Groups
        Route::apiResource('groups', GroupController::class);

        // Coaches
        Route::apiResource('coaches', CoachController::class);
    });
});
