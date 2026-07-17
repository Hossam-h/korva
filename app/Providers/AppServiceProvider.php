<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        $secretName = env('JWT_SECRET_NAME');
       $secret = SecretManagerService::getSecret($secretName);

       config(['jwt.secret' => $secret['JWT_SECRET']]);

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
