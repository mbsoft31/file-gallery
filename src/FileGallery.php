<?php

namespace MBsoft\FileGallery;

use Illuminate\Contracts\Container\BindingResolutionException;
use MBsoft\FileGallery\Drivers\CsvFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\JsonFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\SqliteDatabaseDriver;
use MBsoft\FileGallery\FileSystem\Disk;
use MBsoft\FileGallery\FileSystem\FileStorage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\Traits\ImageOperationsTrait;
use MBsoft\FileGallery\Traits\PDFOperationsTrait;
use MBsoft\Settings\Settings;

class FileGallery
{
    use ImageOperationsTrait;
    use PDFOperationsTrait;

    public function __construct(
        protected Settings $configService,
        protected FileStorage $fileStorageHandler,
        protected ?DatabaseHandlerInterface $databaseHandler = null,
    ) {}

    public static function defaultConfig(?string $configPath = null): Settings
    {
        $configFilePath = !is_null($configPath) && file_exists($configPath)
            ? $configPath
            : app_dir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'file-gallery.php';

        $config = require $configFilePath;

        return Settings::fromArray($config);
    }

    public static function initSettings(array $config = null): Settings
    {
        return self::defaultConfig($config);
    }

    public static function initFileStorage(Settings $config): FileStorage
    {
        $disk = $config->get("disk");
        $diskConfig = $config->getScoped("disks", $disk);
        $diskConfig['root'] = $config->get("storage_path") . '/' . $diskConfig['root'];
        return new FileStorage(new Disk($diskConfig));
    }

    public static function initDatabase(Settings $config): DatabaseHandlerInterface
    {
        $database = $config->get("database");
        $driver = $config->getScoped("drivers", $database);

        $table_name = $config->get("storage_path") . '/' . $driver['path'] . '/' . $driver['table'];

        return match ($database) {
            'sqlite' => new SqliteDatabaseDriver($table_name),
            'json' => new JsonFileDatabaseDriver($table_name),
            'csv' => new CsvFileDatabaseDriver($table_name),
        };
    }

    public static function new(array $config = null): FileGallery
    {
        $settings = self::initSettings($config);
        $fileStorage = self::initFileStorage($settings);
        $database = self::initDatabase($settings);
        $database->initialize();
        return new self($settings, $fileStorage, $database);
    }

    /**
     * @throws BindingResolutionException
     * @throws InvalidFileExtension
     */
    public function initGallery(): bool
    {
        $this->initializeImageManager();

        if ($this->configService->get('database.enabled') && $this->databaseHandler) {
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

    /**
     * @throws InvalidFileExtension
     */
    private function configureFileStorage(): void
    {
        $this->fileStorageHandler->listFiles($this->configService->get('disk_folder', 'gallery'));
    }

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
