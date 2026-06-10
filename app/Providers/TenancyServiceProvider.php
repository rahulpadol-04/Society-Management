<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\Tenancy\TenantManager;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantManager::class, fn () => new TenantManager);
        $this->app->alias(TenantManager::class, 'tenancy');
    }
}
