<?php

namespace MBsoft\FileGallery;

use Intervention\Image\Drivers\AbstractDriver;
use MBsoft\FileGallery\Drivers\CsvFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\JsonFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\SqliteDatabaseDriver;
use MBsoft\FileGallery\FileSystem\Disk;
use MBsoft\FileGallery\FileSystem\FileStorage;
use MBsoft\FileGallery\Traits\FileOperationsTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\Traits\ImageOperationsTrait;
use MBsoft\FileGallery\Traits\PDFOperationsTrait;
use MBsoft\Settings\Settings;

class FileGallery
{
    use FileOperationsTrait;
    use ImageOperationsTrait;
    use PDFOperationsTrait;

    /**
     */
    public function __construct(
        protected Settings                  $settings,
        protected FileStorage               $fileStorageHandler,
        protected ?DatabaseHandlerInterface $databaseHandler = null,
    ) {
        $driver = $this->settings->getTyped('image.driver', AbstractDriver::class ,'gd');
        $options = $this->settings->getTyped('image.options', 'array', []);
        $this->initializeImageManager($driver, $options);
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

    public static function new(Settings $settings): FileGallery
    {
        $fileStorage = self::initFileStorage($settings);
        $database = self::initDatabase($settings);
        $database->initialize();
        return new self($settings, $fileStorage, $database);
    }
}
