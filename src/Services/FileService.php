<?php

namespace LaraJS\Core\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

readonly class FileService
{
    private Filesystem $storage;

    public function __construct(?Filesystem $storage = null)
    {
        $this->storage = $storage ?? Storage::disk();
    }

    public function singleCreate(UploadedFile $file, string $folder): false|string
    {
        return $this->storage->putFile($folder, $file);
    }

    public function singleUpdate(UploadedFile $file, string $folder, ?string $oldFile): false|string
    {
        $singleCreate = $this->singleCreate($file, $folder);
        $this->singleDelete($oldFile);

        return $singleCreate;
    }

    public function singleDelete(?string $path): bool
    {
        return $path && $this->exist($path) && $this->storage->delete(parse_url($path, PHP_URL_PATH));
    }

    public function exist(?string $path): bool
    {
        return $path && $this->storage->fileExists(parse_url($path, PHP_URL_PATH));
    }
}
