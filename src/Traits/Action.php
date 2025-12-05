<?php

namespace LaraJS\Core\Traits;

use Illuminate\Support\Fluent;

trait Action
{
    public static function make(): static
    {
        return app(static::class);
    }

    public static function run(...$arguments): mixed
    {
        return static::make()->handle(...$arguments);
    }

    public static function runValidate(...$arguments): mixed
    {
        $app = static::make();
        if (method_exists($app, 'validate')) {
            $app->validate(...$arguments);
        }

        return $app->handle(...$arguments);
    }

    public static function runIf(bool $boolean, ...$arguments): mixed
    {
        return $boolean ? static::run(...$arguments) : new Fluent;
    }

    public static function runIfValidate(bool $boolean, ...$arguments): mixed
    {
        return $boolean ? static::runValidate(...$arguments) : new Fluent;
    }

    public static function runUnless(bool $boolean, ...$arguments): mixed
    {
        return static::runIf(!$boolean, ...$arguments);
    }

    public static function runUnlessValidate(bool $boolean, ...$arguments): mixed
    {
        return static::runValidate(!$boolean, ...$arguments);
    }
}
