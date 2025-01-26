<?php

declare(strict_types=1);

namespace LaraJS\Core\Commands;

use Illuminate\Support\Facades\File;

trait HasStubs
{
    private const string BASE_PATH = __DIR__ . '/../../stubs';

    protected function getStub(string $name, array $replacements = []): string
    {
        $stub = File::get($this->stubPath($name));

        foreach ($replacements as $search => $replace) {
            $stub = str_replace("{{ $search }}", $replace, $stub);
        }

        return $stub;
    }

    protected function stubPath(string $name): string
    {
        return self::BASE_PATH . "/$name.stub";
    }
}
