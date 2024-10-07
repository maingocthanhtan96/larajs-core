<?php

namespace LaraJS\Core\Traits;

use Illuminate\Support\Fluent;

trait Action
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @see static::handle()
     *
     * @param  mixed  ...$arguments
     */
    public static function run(...$arguments): mixed
    {
        return static::make()->handle(...$arguments);
    }

    /**
     * @see static::handle()
     *
     * @param  mixed  ...$arguments
     */
    public static function runValidate(...$arguments): mixed
    {
        $app = static::make();
        if (method_exists($app, 'validate')) {
            $app->validate(...$arguments);
        }

        return $app->handle(...$arguments);
    }

    public static function runIf($boolean, ...$arguments): mixed
    {
        return $boolean ? static::run(...$arguments) : new Fluent;
    }

    /**
     * @return mixed|Fluent
     */
    public static function runUnless($boolean, ...$arguments): mixed
    {
        return static::runIf(!$boolean, ...$arguments);
    }
}
