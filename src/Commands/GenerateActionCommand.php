<?php

namespace LaraJS\Core\Commands;

class GenerateActionCommand extends GeneratorCommand
{
    protected $signature = 'larajs:make:action
                            {name : The name of the action}';

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
            ],
            "{$this->directoryPath()}/Update{$this->argument('name')}Action.php"
        );
    }
}
