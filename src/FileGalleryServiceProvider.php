<?php

namespace MBsoft\FileGallery;

use Intervention\Image\ImageManager;
use MBsoft\FileGallery\Commands\FileGalleryCommand;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;
use MBsoft\FileGallery\Drivers\CsvFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\JsonFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\SqliteDatabaseDriver;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FileGalleryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $this->app->singleton(DatabaseHandlerInterface::class, function ($app) {
            // Choose driver based on configuration or explicitly
            $driver = config('file-gallery.driver', 'sqlite'); // 'sqlite' is default

            return match ($driver) {
                'json' => new JsonFileDatabaseDriver(storage_path('file_gallery.json')),
                'csv' => new CsvFileDatabaseDriver(storage_path('file_gallery.csv')),
                default => new SqliteDatabaseDriver(),
            };
        });

        $this->app->singleton(ImageManager::class, function ($app) {
            $current = config('file-gallery.image.driver', 'gd'); // Default to 'gd' if not set
            $driver = match ($current) {
                'imagick' => \Intervention\Image\Drivers\Imagick\Driver::class,
                default => \Intervention\Image\Drivers\Gd\Driver::class,
            };
            return new ImageManager(driver: $driver);
        });

        $package
            ->name('file-gallery')
            ->hasConfigFile(configFileName: "file-gallery")
            ->hasViews()
            ->hasMigration(migrationFileName: 'create_file_gallery_table')
            ->hasCommand(commandClassName: FileGalleryCommand::class);
    }

}
