<?php

namespace MBsoft\FileGallery\Contracts;

use Illuminate\Http\UploadedFile;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;

interface FileStorageHandlerInterface
{
    /**
     * Store a file and return its storage path.
     *
     * @param  mixed  $file  The file to be stored.
     * @param  string|null  $path  Optional specific path.
     * @return array The path where the file is stored.
     *
     * @throws InvalidFileExtension
     */
    public function storeFile(UploadedFile $file, ?string $path = null): array;

    /**
     * Retrieve a file by its path.
     *
     * @param  string  $path  The path to the file.
     * @return mixed The file content or resource.
     */
    public function getFile(string $path): mixed;

    /**
     * Delete a file by its path.
     *
     * @param  string  $path  The path to the file to delete.
     * @return bool True on successful deletion, false otherwise.
     */
    public function deleteFile(string $path): bool;

    /**
     * Check if a file exists at a given path.
     *
     * @param  string  $path  The path to check.
     * @return bool True if the file exists, false otherwise.
     */
    public function fileExists(string $path): bool;

    /**
     * List files in a directory.
     *
     * @param  string  $directory  The directory path.
     * @return array An array of file paths.
     */
    public function listFiles(string $directory): array;

    public function validateFile(UploadedFile $file): UploadedFile;

    public function getFullFilePath(string $uuid, string $extension, ?string $path = null): string;
}
