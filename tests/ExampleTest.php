<?php

use Intervention\Image\ImageManager;
use MBsoft\FileGallery\Config;
use MBsoft\FileGallery\FileGallery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

// Test for database configuration
it('configures database if enabled', function() {
    $config = new Config(
        database: true,
        database_provider: "sqlite",
        database_url: ":memory:",
    );
    $gallery = new FileGallery($config);

    $result = $gallery->initGallery();

    expect($result)->toBeTrue()
        ->and(Schema::hasTable($config->database_table_name))->toBeTrue();
});

// Test for file storage configuration
it('configures file storage if database is disabled', function() {
    Storage::fake('public');

    $config = new Config(database: false);
    $gallery = new FileGallery($config);

    $result = $gallery->initGallery();

    expect($result)->toBeTrue();
    Storage::disk($config->disk)->assertExists($config->disk_folder);
});

// Test for image resizing
it('resizes images correctly', function() {
    Storage::fake('public');
    $imagePath = Storage::disk('public')->path('test.jpg');
    $config = new Config();
    $gallery = new FileGallery($config);

    $manager = app()->make(ImageManager::class);

    // Create a fake image
    $manager->create(100, 100)->save($imagePath);

    // Resize the image
    $gallery->resizeImage($imagePath, 50, 50);

    // Assert the image has been resized
    $image = $manager->read($imagePath);
    expect($image->width())->toBe(50)
        ->and($image->height())->toBe(50);
});
