<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
