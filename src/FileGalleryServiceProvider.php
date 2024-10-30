<?php

namespace MBsoft\FileGallery;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MBsoft\FileGallery\Commands\FileGalleryCommand;

class FileGalleryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('file-gallery')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_file_gallery_table')
            ->hasCommand(FileGalleryCommand::class);
    }
}
