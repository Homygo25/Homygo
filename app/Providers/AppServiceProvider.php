<?php

namespace App\Providers;

use App\Services\CacheService;
use App\Services\SecurityService;
use App\Services\ErrorHandlingService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register our custom services
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService();
        });

        $this->app->singleton(SecurityService::class, function ($app) {
            return new SecurityService();
        });

        $this->app->singleton(ErrorHandlingService::class, function ($app) {
            return new ErrorHandlingService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL compatibility
        Schema::defaultStringLength(191);
        
        // Register middleware aliases in boot method
        $router = $this->app['router'];
        $router->aliasMiddleware('rate.limit', \App\Http\Middleware\RateLimitMiddleware::class);
    }
}
