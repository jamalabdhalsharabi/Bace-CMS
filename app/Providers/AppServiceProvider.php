<?php

namespace App\Providers;

use Illuminate\Auth\RequestGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

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
        // Extend auth to create a request-based guard driver
        Auth::extend('request', function ($app, $name, array $config) {
            return new RequestGuard(
                fn ($request) => $request->user(),
                $app['request'],
                $app['auth']->createUserProvider($config['provider'] ?? null)
            );
        });
    }
}
