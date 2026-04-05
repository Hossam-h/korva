<?php

use App\Http\Controllers\Api\Player\AcademyController;
use App\Http\Controllers\Api\Player\AuthController;
use App\Http\Controllers\Api\Player\HomeController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'player',
], function () {
    // Public routes (no auth)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('check-otp', [AuthController::class, 'checkOtp']);
    Route::post('login', [AuthController::class, 'login']);

    // Protected routes (requires player JWT)
    Route::middleware('auth:player')->group(function () {
        Route::post('set-password', [AuthController::class, 'setPassword']);
        Route::post('complete-profile', [AuthController::class, 'completeProfile']);
        Route::post('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);

        // Home
        Route::get('home', [HomeController::class, 'index']);

        // Academies
        Route::get('academies/search', [AcademyController::class, 'search']);
        Route::get('academies/{academy}', [AcademyController::class, 'show']);
        Route::post('academies/{academy}/review', [AcademyController::class, 'addReview']);
    });
});
