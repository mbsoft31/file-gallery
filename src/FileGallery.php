<?php

namespace MBsoft\FileGallery;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\UploadedFile;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;
use MBsoft\FileGallery\Contracts\FileStorageHandlerInterface;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\Services\GalleryConfigService;
use MBsoft\FileGallery\Traits\ImageOperationsTrait;

class FileGallery
{
    use ImageOperationsTrait;

    protected GalleryConfigService $configService;

    public function __construct(
        GalleryConfigService $configService,
        protected FileStorageHandlerInterface $fileStorageHandler,
        protected ?DatabaseHandlerInterface $databaseHandler = null,
    ) {
        $this->configService = $configService;
    }

    /**
     * @throws BindingResolutionException
     */
    public function initGallery(): bool
    {
        $this->initializeImageManager();

        if ($this->configService->usesDatabase() && $this->databaseHandler) {
            $this->configureDatabase();
        } else {
            $this->configureFileStorage();
        }

        return true;
    }

    private function configureDatabase(): void
    {
        $this->databaseHandler->initialize();
    }

    private function configureFileStorage(): void
    {
        $this->fileStorageHandler->listFiles($this->configService->get('disk_folder', 'gallery'));
    }

    /**
     * @throws InvalidFileExtension
     */
    public function storeFile(UploadedFile $file, string $path = ''): array
    {
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
