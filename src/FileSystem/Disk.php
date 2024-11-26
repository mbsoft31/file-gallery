<?php
namespace MBsoft\FileGallery\FileSystem;

use InvalidArgumentException;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;

class Disk
{
    protected string $path;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->config['root'] = rtrim($this->config['root'], '/') . '/';
        $this->path = $this->config['root'];
    }

    /**
     * List all files in the given directory.
     *
     * @param string $directory
     * @return array
     * @throws InvalidFileExtension
     */
    public function files(string $directory = ''): array
    {
        $fullPath = $this->path . $directory;

        if (!is_dir($fullPath)) {
            throw new InvalidFileExtension("The directory $fullPath does not exist.");
        }

        $files = scandir($fullPath);
        return array_filter($files, function ($file) {
            return $file !== '.' && $file !== '..';
        });
    }

    /**
     * Check if a file or directory exists on the disk.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        $fullPath = $this->path . $path;
        return file_exists($fullPath);
    }

    /**
     * Delete a file from the disk.
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        $fullPath = $this->path . $path;

        if ($this->exists($path)) {
            return unlink($fullPath); // Deletes the file
        }

        throw new InvalidArgumentException("The file $fullPath does not exist.");
    }

    /**
     * Get the contents of a file.
     *
     * @param string $path
     * @return string|null
     */
    public function file(string $path): ?string
    {
        $fullPath = $this->path . $path;

        if ($this->exists($path)) {
            return file_get_contents($fullPath); // Read the file contents
        }

        throw new InvalidArgumentException("The file $fullPath does not exist.");
    }

    /**
     * Get the root dir of the disk.
     *
     * @return string
     */
    public function getRoot(): string
    {
        return $this->config['root'];
    }

    /**
     * Get the visibility of the disk's files.
     *
     * @return string
     */
    public function getVisibility(): string
    {
        return $this->config['visibility'];
    }

    /**
     * Get the permissions of the disk's files.
     *
     * @return int
     */
    public function getPermissions(): int
    {
        return $this->config['permissions'];
    }

    /**
     * Get the URL base for the disk.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->config['url'];
    }
}
