<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use App\Services\SecretManagerService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prefer the JWT secret from AWS Secrets Manager, but never let a
        // Secrets Manager outage / IAM misconfiguration take down the whole API:
        // fall back to the env-based jwt.secret (config/jwt.php) instead.
        $secretName = env('JWT_SECRET_NAME');

        if ($secretName) {
            try {
                $secret = SecretManagerService::getSecret($secretName);

                if (! empty($secret['JWT_SECRET'])) {
                    config(['jwt.secret' => $secret['JWT_SECRET']]);
                }
            } catch (\Throwable $e) {
                Log::error('Failed to load JWT secret from Secrets Manager; falling back to env JWT_SECRET: '.$e->getMessage());
            }
        }

        // Password policy shared by player set-password and reset-password flows.
        // Matches the mobile UI: min length + uppercase + number + special character.
        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols();
        });
    }
}
