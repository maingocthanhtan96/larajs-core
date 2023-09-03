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
            return new SetupCommand();
        });
        $this->commands('larajs.setup');
        $this->publishes(
            [
                __DIR__ . '/../config/generator.php' => config_path('generator.php'),
            ],
            'larajs-core-config',
        );
        $this->publishes(
            [
                __DIR__ . '/../config/generator-mono.php' => config_path('generator.php'),
            ],
            'larajs-core-config-mono',
        );
        $this->mergeConfigFrom(__DIR__ . '/../config/generator.php', 'generator');
        $this->publishes(
            [
                __DIR__ . '/../public' => public_path('vendor'),
            ],
            'larajs-core-public',
        );
        $this->publishes(
            [
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ],
            'larajs-core-migrations',
        );
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
