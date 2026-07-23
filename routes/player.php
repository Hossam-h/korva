<?php

use App\Http\Controllers\Api\Player\AcademyController;
use App\Http\Controllers\Api\Player\AuthController;
use App\Http\Controllers\Api\Player\BookingController;
use App\Http\Controllers\Api\Player\HomeController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'player',
], function () {
    // Public routes (no auth)
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::post('resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:6,1');
    Route::post('check-otp', [AuthController::class, 'checkOtp'])->middleware('throttle:6,1');
    Route::post('login', [AuthController::class, 'login']);

    // Password recovery
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:6,1');
    Route::post('forgot-password/verify-otp', [AuthController::class, 'verifyResetOtp']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // Social login (Google / Apple)
    Route::post('social-login', [AuthController::class, 'socialLogin']);

    // Refresh must NOT sit behind auth:player — that middleware rejects an
    // expired access token before the request ever reaches the controller,
    // which defeats the one case refresh exists for. JWTAuth::refresh() does
    // its own validation (signature, blacklist, refresh_ttl window).
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('throttle:20,1');

    // Protected routes (requires player JWT)
    Route::middleware('auth:player')->group(function () {
        Route::post('set-password', [AuthController::class, 'setPassword']);
        Route::post('complete-profile', [AuthController::class, 'completeProfile']);
        Route::post('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        // Home
        Route::get('home', [HomeController::class, 'index']);

        // Academies
        Route::get('academies/search', [AcademyController::class, 'search']);
        Route::get('academies/{academy}', [AcademyController::class, 'show']);
        Route::post('academies/{academy}/review', [AcademyController::class, 'addReview']);

        // Bookings
        Route::post('bookings', [BookingController::class, 'store']);
        Route::get('bookings', [BookingController::class, 'index']);
        Route::get('bookings/{id}', [BookingController::class, 'show']);
        Route::post('bookings/{id}/cancel', [BookingController::class, 'cancel']);
        Route::post('bookings/{id}/confirm', [BookingController::class, 'confirm']);
        Route::get('groups/{groupId}/slots', [BookingController::class, 'availableSlots']);
    });
});
