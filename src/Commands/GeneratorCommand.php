<?php

declare(strict_types=1);

namespace LaraJS\Core\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

abstract class GeneratorCommand extends Command
{
    use HasStubs;

    protected string $alreadyExistsMessage = '';

    protected string $successMessage = '';

    public function handle(): int
    {
        if (File::exists($this->directoryPath())) {
            $this->error($this->alreadyExistsMessage);

            return Command::FAILURE;
        }

        $this->makeDirectory();

        $this->generateFiles();

        $this->info($this->successMessage);

        return Command::SUCCESS;
    }

    abstract protected function directoryPath(): string;

    abstract protected function generateFiles(): void;

    protected function generateFile(string $stub, array $replacements, string $path): void
    {
        $stub = $this->getStub($stub, $replacements);

        File::put($path, $stub);
    }

    private function makeDirectory(): void
    {
        $folder = $this->directoryPath();
        if (Str::endsWith($this->directoryPath(), '.php')) {
            $folder = dirname($folder);
        }
        if (!File::exists($folder)) {
            File::makeDirectory($folder, 0777, true);
        }
    }
}
