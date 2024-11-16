<?php

namespace MBsoft\FileGallery;

use Intervention\Image\ImageManager;
use MBsoft\FileGallery\Commands\FileGalleryCommand;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;
use MBsoft\FileGallery\Drivers\CsvFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\FileStorageHandler;
use MBsoft\FileGallery\Drivers\JsonFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\SqliteDatabaseDriver;
use MBsoft\FileGallery\Services\GalleryConfigService;
use Spatie\LaravelPackageTools\Exceptions\InvalidPackage;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FileGalleryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('file-gallery')
            ->hasConfigFile('file-gallery')
            ->hasViews()
            ->hasMigration('create_file_gallery_table')
            ->hasCommand(FileGalleryCommand::class);
    }

    /**
     * @throws InvalidPackage
     */
    public function register(): void
    {
        parent::register();

        // Bind GalleryConfigService
        $this->app->singleton(GalleryConfigService::class, function ($app) {
            return new GalleryConfigService(config('file-gallery'));
        });

        // Register DatabaseHandlerInterface binding
        $this->app->singleton(DatabaseHandlerInterface::class, function ($app) {
            /** @var GalleryConfigService $configService */
            $configService = $app->make(GalleryConfigService::class);
            $driver = $configService->get('database.provider', 'sqlite');

            return match ($driver) {
                'json' => new JsonFileDatabaseDriver(storage_path('file_gallery.json')),
                'csv' => new CsvFileDatabaseDriver(storage_path('file_gallery.csv')),
                default => new SqliteDatabaseDriver,
            };
        });

        // Register ImageManager binding
        $this->app->singleton(ImageManager::class, function ($app) {
            /** @var GalleryConfigService $configService */
            $configService = $app->make(GalleryConfigService::class);
            $driver = $configService->get('image.driver', 'gd');

            return new ImageManager($driver);
        });

        $this->app->bind(\MBsoft\FileGallery\FileGallery::class, function ($app) {
            $configService = $app->make(GalleryConfigService::class);
            $driver = $configService->get('database.provider', 'sqlite');
            $db = match ($driver) {
                'json' => new JsonFileDatabaseDriver(storage_path('file_gallery.json')),
                'csv' => new CsvFileDatabaseDriver(storage_path('file_gallery.csv')),
                default => new SqliteDatabaseDriver,
            };
            return new FileGallery($configService, new FileStorageHandler(), $db);
        });
    }
}
