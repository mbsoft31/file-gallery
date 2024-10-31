<?php

namespace MBsoft\FileGallery;

use Intervention\Image\ImageManager;
use MBsoft\FileGallery\Commands\FileGalleryCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FileGalleryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('file-gallery')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_file_gallery_table')
            ->hasCommand(FileGalleryCommand::class);
    }

    public function boot()
    {
        parent::boot();

        $this->app->singleton(\Intervention\Image\ImageManager::class, function ($app, $driver) {
            return new ImageManager($driver);
        });
    }
}
