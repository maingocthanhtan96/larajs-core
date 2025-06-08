<?php

namespace LaraJS\Core\Commands;

use Illuminate\Support\Str;

class GenerateControllerCommand extends GeneratorCommand
{
    protected $signature = 'larajs:make:controller {name : The name of the controller}';

    protected $description = 'Generate a new controller';

    protected string $successMessage = 'Controller created successfully!';

    protected string $alreadyExistsMessage = 'Controller already exists!';

    private string $path;

    protected function directoryPath(): string
    {
        $this->path = app_path("Http/Controllers/Api/{$this->argument('name')}Controller.php");

        return $this->path;
    }

    protected function generateFiles(): void
    {
        $this->generateController();
    }

    private function generateController(): void
    {
        $path = app_path('Http/Controllers');

        $this->generateFile(
            'controller',
            [
                'name' => $this->argument('name'),
                'variable' => Str::camel($this->argument('name')),
                'variables' => Str::plural(Str::camel($this->argument('name'))),
            ],
            $this->path
        );
    }
}
