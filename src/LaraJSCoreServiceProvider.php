<?php

namespace LaraJS\Core;

use Illuminate\Support\ServiceProvider;
use LaraJS\Core\Commands\SetupCommand;

class LaraJSCoreServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     */
    protected bool $defer = false;

    public function boot()
    {
        $this->app->singleton('larajs.setup', function () {
            return new SetupCommand;
        });
        $this->commands('larajs.setup');
    }

    public function register()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api-v1.php');
    }
}
