<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use App\Http\Responses\LoginResponse;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;

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
        // This line tells Filament to use your custom LoginResponse
        $this->app->bind(LoginResponseContract::class, LoginResponse::class);

        // Optionally force HTTPS locally when env says so
        if (env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // Performance: Prevent lazy loading of relationships in production
        if (env('APP_ENV') === 'production') {
            Model::shouldBeStrict();
        }

        // Enable query optimization for database
        if (env('APP_ENV') === 'production') {
            // Prevent N+1 queries by warning in development
            Model::preventLazyLoading(!env('APP_DEBUG', false));
        }
    }
}