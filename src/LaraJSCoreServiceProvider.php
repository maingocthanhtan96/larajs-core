<?php

namespace LaraJS\Core;

use Illuminate\Support\ServiceProvider;
use LaraJS\Core\Commands\GenerateActionCommand;
use LaraJS\Core\Commands\GenerateControllerCommand;
use LaraJS\Core\Commands\GenerateRepositoryCommand;
use LaraJS\Core\Commands\SetupCommand;

class LaraJSCoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootCommands();
    }

    private function bootCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateActionCommand::class,
                GenerateControllerCommand::class,
                GenerateRepositoryCommand::class,
                SetupCommand::class,
            ]);
        }
    }
}
