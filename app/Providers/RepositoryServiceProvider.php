<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Binds every repository contract to its Eloquent implementation by convention:
 *
 *   App\Repositories\Contracts\XxxRepositoryInterface
 *        -> App\Repositories\Eloquent\XxxRepository
 *
 * New modules simply drop the two files in and they are wired automatically —
 * no central registration edits required (avoids merge churn across modules).
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $contractsPath = app_path('Repositories/Contracts');

        if (! is_dir($contractsPath)) {
            return;
        }

        foreach (glob($contractsPath.'/*RepositoryInterface.php') ?: [] as $file) {
            $name = basename($file, '.php');                       // XxxRepositoryInterface
            $interface = "App\\Repositories\\Contracts\\{$name}";
            $concrete  = 'App\\Repositories\\Eloquent\\'.str_replace('Interface', '', $name);

            if (interface_exists($interface) && class_exists($concrete)) {
                $this->app->bind($interface, $concrete);
            }
        }
    }
}
