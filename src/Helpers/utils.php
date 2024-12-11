<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;
use MBsoft\FileGallery\Drivers\CsvFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\JsonFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\SqliteDatabaseDriver;
use MBsoft\FileGallery\FileSystem\Disk;
use MBsoft\FileGallery\FileSystem\FileStorage;
use MBsoft\Settings\Settings;

function app_dir(): string
{
    return dirname(__DIR__, 2);
}

function storage_dir($path = null): string
{
    return app_dir() . '/storage' . ($path == null ? '' : '/'.$path);
}

function defaultConfig(?string $configPath = null): Settings
{
    $configFilePath = !is_null($configPath) && file_exists($configPath)
        ? $configPath
        : app_dir() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'file-gallery.php';

    $config = require $configFilePath;

    return Settings::fromArray($config);
}

function getSettings(array $config = null): Settings
{
    return defaultConfig($config);
}

function initDatabase(Settings $config): DatabaseHandlerInterface
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

function initFileStorage(Settings $config): FileStorage
{
    $disk = $config->get("disk");
    $diskConfig = $config->getScoped("disks", $disk);
    $diskConfig['root'] = $config->get("storage_path") . '/' . $diskConfig['root'];

    return new FileStorage(new Disk($diskConfig));
}

function initImageManager(Settings $settings): ImageManager
{
    $driver = $settings->get('image.driver','gd');
    return new ImageManager(match ($driver) {
        GdDriver::class => new GdDriver(),
        ImagickDriver::class => new ImagickDriver(),
    }, $settings->get('image.options', []));
}
