<?php

namespace MBsoft\FileGallery;

use Intervention\Image\ImageManager;
use MBsoft\Settings\Enums\ConfigFormat;
use MBsoft\Settings\Settings;
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
            //->hasViews()
            ->hasMigration('create_file_gallery_table');
    }

    /**
     * @throws InvalidPackage
     */
    public function register(): void
    {
        parent::register();

        // Bind Settings
        $this->app->singleton(Settings::class, function ($app) {
            return Settings::loadFromFile('config/file-gallery.php', ConfigFormat::PHP);
        });

        // Register ImageManager binding
        $this->app->singleton(ImageManager::class, function ($app) {
            /** @var Settings $configService */
            $configService = $app->make(Settings::class);
            $driver = $configService->get('image.driver', 'gd');

            return new ImageManager($driver);
        });

    }
}
