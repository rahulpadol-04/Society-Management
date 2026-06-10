<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // utf8mb4 index key-length safety.
        Schema::defaultStringLength(191);

        // Bootstrap 5 markup for paginator links.
        Paginator::useBootstrapFive();

        // Catch lazy-loading / mass-assignment mistakes early outside production.
        Model::shouldBeStrict(! $this->app->isProduction());

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // General API limiter: 60 req/min per authenticated user (or IP).
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));

        // Tighter limiter for auth endpoints to slow credential stuffing.
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)
            ->by($request->input('email').'|'.$request->ip()));
    }
}
