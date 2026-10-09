<?php

namespace App\Providers;

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
        // CP-26: fail closed when the app is running in production with
        // debug mode on. Laravel would otherwise leak stack traces, file
        // paths, code snippets and sometimes env values to visitors.
        //
        // To debug against a production-like environment, set
        // APP_ENV=staging instead — production alone means "no debug".
        if ($this->app->environment('production') && config('app.debug')) {
            throw new \RuntimeException(
                'Refusing to boot: APP_DEBUG=true in production. Set APP_DEBUG=false in .env.'
            );
        }
    }
}