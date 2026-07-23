<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/academy.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/player.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // JWT failures otherwise bubble up as raw 500s with a stack trace.
        // Map them to a clean 401 + machine-readable error_code so API clients
        // (mobile app) can tell "access token expired, try refresh" apart from
        // "refresh/token itself is dead, force re-login". Order matters:
        // TokenBlacklistedException extends TokenInvalidException, so the
        // more specific one must be registered first.
        $exceptions->render(function (TokenBlacklistedException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Token has been invalidated.',
                'error_code' => 'token_blacklisted',
            ], 401);
        });

        $exceptions->render(function (TokenExpiredException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired.',
                'error_code' => 'token_expired',
            ], 401);
        });

        $exceptions->render(function (TokenInvalidException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid.',
                'error_code' => 'token_invalid',
            ], 401);
        });

        $exceptions->render(function (JWTException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Token is missing or malformed.',
                'error_code' => 'token_absent',
            ], 401);
        });
    })->create();
