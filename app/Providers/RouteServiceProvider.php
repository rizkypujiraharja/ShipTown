<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const string HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            $this->mapPublicRoutes();
        });
    }

    /**
     * Define the "public" web routes for the application.
     *
     * These routes are PUBLICLY accessible !!!!
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapPublicRoutes(): void
    {
        Route::middleware(['web'])
            ->group(base_path('routes/public.php'));
    }
}
