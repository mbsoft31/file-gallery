<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use MBsoft\Settings\Settings;

function app_dir(): string
{
    return dirname(__DIR__, 2);
}

function storage_dir($path = null): string
{
    return app_dir() . '/storage' . ($path == null ? '' : '/'.$path);
}

function getImageManager(string|DriverInterface $driver, mixed ...$options): ImageManager
{
    return new ImageManager(match ($driver) {
        GdDriver::class => new GdDriver(),
        ImagickDriver::class => new ImagickDriver(),
    }, $options);
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
