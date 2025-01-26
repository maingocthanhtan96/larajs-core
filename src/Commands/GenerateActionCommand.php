<?php

namespace LaraJS\Core\Commands;

use Illuminate\Support\Str;

class GenerateActionCommand extends GeneratorCommand
{
    protected $signature = 'larajs:make:action {name : The name of the action class} {--repository : Include a repository interface in the action class}';

    protected $description = 'Generate a new action';

    protected string $successMessage = 'Action created successfully!';

    protected string $alreadyExistsMessage = 'Action already exists!';

    protected function directoryPath(): string
    {
        return app_path("Actions/{$this->argument('name')}");
    }

    protected function generateFiles(): void
    {
        $this->generateCreateAction();
        $this->generateDeleteAction();
        $this->generateFindAllAction();
        $this->generateFindOneAction();
        $this->generateUpdateAction();
    }

    private function generateCreateAction(): void
    {
        $this->generateFile(
            'create.action',
            [
                'name' => $this->argument('name'),
                'use' => $this->option('repository') ? $this->generateUse($this->argument('name'), 'Write') : '',
                'repository' => $this->option('repository') ? $this->generateRepository($this->argument('name'), 'Write') : '',
            ],
            "{$this->directoryPath()}/Create{$this->argument('name')}Action.php"
        );
    }

    private function generateDeleteAction(): void
    {
        $this->generateFile(
            'delete.action',
            [
                'name' => $this->argument('name'),
                'use' => $this->option('repository') ? $this->generateUse($this->argument('name'), 'Write') : '',
                'repository' => $this->option('repository') ? $this->generateRepository($this->argument('name'), 'Write') : '',
            ],
            "{$this->directoryPath()}/Delete{$this->argument('name')}Action.php"
        );
    }

    private function generateFindAllAction(): void
    {
        $this->generateFile(
            'find-all.action',
            [
                'name' => $this->argument('name'),
                'use' => $this->option('repository') ? $this->generateUse($this->argument('name'), 'Read') : '',
                'repository' => $this->option('repository') ? $this->generateRepository($this->argument('name'), 'Read') : '',
            ],
            "{$this->directoryPath()}/FindAll{$this->argument('name')}Action.php"
        );
    }

    private function generateFindOneAction(): void
    {
        $this->generateFile(
            'find-one.action',
            [
                'name' => $this->argument('name'),
                'use' => $this->option('repository') ? $this->generateUse($this->argument('name'), 'Read') : '',
                'repository' => $this->option('repository') ? $this->generateRepository($this->argument('name'), 'Read') : '',
            ],
            "{$this->directoryPath()}/FindOne{$this->argument('name')}Action.php"
        );
    }

    private function generateUpdateAction(): void
    {
        $this->generateFile(
            'update.action',
            [
                'name' => $this->argument('name'),
                'use' => $this->option('repository') ? $this->generateUse($this->argument('name'), 'Write') : '',
                'repository' => $this->option('repository') ? $this->generateRepository($this->argument('name'), 'Write') : '',
            ],
            "{$this->directoryPath()}/Update{$this->argument('name')}Action.php"
        );
    }

    private function generateRepository(string $name, string $prefix): string
    {
        $camelName = Str::camel($name);
        return
    <<<TEMPLATE

        /**
         * @param  {$name}{$prefix}RepositoryInterface<{$name}>  \${$camelName}{$prefix}Repository
         */
        public function __construct(private {$name}{$prefix}RepositoryInterface \${$camelName}{$prefix}Repository) {}

    TEMPLATE;
    }

    private function generateUse(string $name, string $prefix): string
    {
        return <<<TEMPLATE
        use App\Repositories\\{$name}\\{$name}{$prefix}RepositoryInterface;
        use App\Models\\{$name};
        TEMPLATE;
    }
}
