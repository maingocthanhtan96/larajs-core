<?php

namespace LaraJS\Core\Commands;

class GenerateRepositoryCommand extends GeneratorCommand
{
    protected $signature = 'larajs:make:repository {name : The name of the repository}';

    protected $description = 'Generate a new repository';

    protected string $successMessage = 'Repository created successfully!';

    protected string $alreadyExistsMessage = 'Repository already exists!';

    protected function directoryPath(): string
    {
        return app_path("Repositories/{$this->argument('name')}");
    }

    protected function generateFiles(): void
    {
        $this->generateReadRepository();
        $this->generateReadInterfaceRepository();
        $this->generateWriteRepository();
        $this->generateWriteInterfaceRepository();
    }

    private function generateReadRepository(): void
    {
        $this->generateFile(
            'read.repository',
            [
                'name' => $this->argument('name'),
            ],
            "{$this->directoryPath()}/{$this->argument('name')}ReadRepository.php"
        );
    }

    private function generateReadInterfaceRepository(): void
    {
        $this->generateFile(
            'read.repository.interface',
            [
                'name' => $this->argument('name'),
            ],
            "{$this->directoryPath()}/{$this->argument('name')}ReadRepositoryInterface.php"
        );
    }

    private function generateWriteRepository(): void
    {
        $this->generateFile(
            'write.repository',
            [
                'name' => $this->argument('name'),
            ],
            "{$this->directoryPath()}/{$this->argument('name')}WriteRepository.php"
        );
    }

    private function generateWriteInterfaceRepository(): void
    {
        $this->generateFile(
            'write.repository.interface',
            [
                'name' => $this->argument('name'),
            ],
            "{$this->directoryPath()}/{$this->argument('name')}WriteRepositoryInterface.php"
        );
    }
}
