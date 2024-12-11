<?php

namespace MBsoft\FileGallery\Traits;

use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FileOperationsTrait
{

    /**
     * @throws InvalidFileExtension
     */
    public function storeFile(UploadedFile $file, string $path = ''): array
    {
        $fileData = $this->fileStorageHandler->storeFile($file, $path);
        $this->databaseHandler->addFile($fileData);
        return $fileData;
    }

    /**
     * Retrieve a file's contents using FileStorageHandlerInterface.
     */
    public function getFile(string $path): ?string
    {
        return $this->fileStorageHandler->getFile($path);
    }

    /**
     * Delete a file using FileStorageHandlerInterface.
     */
    public function deleteFile(string $path): bool
    {
        return $this->fileStorageHandler->deleteFile($path);
    }

    /**
     * Check if a file exists using FileStorageHandlerInterface.
     */
    public function fileExists(string $path): bool
    {
        return $this->fileStorageHandler->fileExists($path);
    }

    /**
     * List files in a directory using FileStorageHandlerInterface.
     * @throws InvalidFileExtension
     */
    public function listFiles(string $directory = ''): array
    {
        return $this->fileStorageHandler->listFiles($directory);
    }

    public function getColumns(): array
    {
        return $this->databaseHandler->getColumns();
    }

    public function addFile(array $fileData): bool
    {
        return $this->databaseHandler->addFile($fileData);
    }

    public function getFileRow(string $identifier): ?array
    {
        return $this->databaseHandler->getFileRow($identifier);
    }

    public function getAllFiles(): array
    {
        return $this->databaseHandler->getAllFiles();
    }

}
