<?php

namespace LaraJS\Core\Services;

class FileService
{
    /**
     * @author tanmnt
     */
    public static function createFile($path, $fileName, $contents)
    {
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
        $path .= $fileName;
        file_put_contents($path, $contents);
    }

    /**
     * @return bool
     *
     * @author tanmnt
     */
    public static function createFileReal($path, $contents)
    {
        if (!file_exists($path)) {
            return false;
        }
        file_put_contents($path, $contents);
    }

    /**
     * @param  bool  $replace
     *
     * @author tanmnt
     */
    public static function createDirectoryIfNotExist($path, $replace = false)
    {
        if (file_exists($path) && $replace) {
            rmdir($path);
        }
        if (!file_exists($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
    }

    /**
     * @return bool
     *
     * @author tanmnt
     */
    public static function deleteFile($path, $fileName)
    {
        if (file_exists($path.$fileName)) {
            return unlink($path.$fileName);
        }

        return false;
    }
}
