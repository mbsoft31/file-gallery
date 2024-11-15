<?php

namespace MBsoft\FileGallery;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\UploadedFile;
use MBsoft\FileGallery\Contracts\FileStorageHandlerInterface;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\Traits\ImageOperationsTrait;

class FileGallery
{
    use ImageOperationsTrait;

    public function __construct(
        protected FileStorageHandlerInterface $fileStorageHandler,
        protected ?DatabaseHandlerInterface $databaseHandler = null,
        protected Config $config = new Config()
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function initGallery(): bool
    {
        $this->initializeImageManager();

        if ($this->config->database && $this->databaseHandler) {
            $this->configureDatabase();
        } else {
            $this->configureFileStorage();
        }
        return true;
    }

    private function configureDatabase(): void
    {
        // Database configuration logic handled by DatabaseHandlerInterface
        $this->databaseHandler->initialize();
    }

    private function configureFileStorage(): void
    {
        // File storage configuration handled by FileStorageHandlerInterface
        $this->fileStorageHandler->listFiles($this->config->disk_folder); // Example call to ensure folder exists
    }

    /**
     * @throws InvalidFileExtension
     */
    public function storeFile(UploadedFile $file, string $path = ""): array
    {
        // Validate and store the file using FileStorageHandlerInterface
        return $this->fileStorageHandler->storeFile($file, $path);
    }

    /**
     * Retrieve a file's contents using FileStorageHandlerInterface.
     */
    public function getFile(string $path): mixed
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
     */
    public function listFiles(string $directory): array
    {
        return $this->fileStorageHandler->listFiles($directory);
    }

    public function optimizePDF(string $path): void
    {
        // PDF optimization logic (if needed)
    }
}
