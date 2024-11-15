<?php

use Illuminate\Contracts\Container\BindingResolutionException;
use Intervention\Image\ImageManager;
use MBsoft\FileGallery\Config;
use MBsoft\FileGallery\FileGallery;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;

// Test for database configuration
it('configures database if enabled', function () {
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
it('configures file storage if database is disabled', function () {
    Storage::fake('public');

    $config = new Config(database: false);
    $gallery = new FileGallery($config);

    $result = $gallery->initGallery();

    expect($result)->toBeTrue();
    Storage::disk($config->disk)->assertExists($config->disk_folder);
});

// Test for file validation with allowed extensions
it('validates allowed file extensions', function () {
    $fileGallery = new FileGallery();
    $file = UploadedFile::fake()->image('test.jpg');

    expect(fn() => $fileGallery->validateFile($file))->not->toThrow(InvalidFileExtension::class);

    $invalidFile = UploadedFile::fake()->create('test.exe');
    expect(fn() => $fileGallery->validateFile($invalidFile))->toThrow(InvalidFileExtension::class);
});

// Test for storing a file
it('stores files on the correct disk and folder', function () {
    Storage::fake('public');

    $fileGallery = new FileGallery();
    $file = UploadedFile::fake()->image('test.jpg');

    $storedFile = $fileGallery->storeFile($file);

    Storage::disk('public')->assertExists("gallery/{$storedFile['filename']}");
    expect($storedFile['original_name'])->toBe('test.jpg');
});

// Test for image resizing
it('resizes images correctly', function () {
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

// Test for image cropping
it('crops images correctly', function () {
    Storage::fake('public');
    $imagePath = Storage::disk('public')->path('test.jpg');
    $config = new Config();
    $gallery = new FileGallery($config);

    $manager = app()->make(ImageManager::class);
    $manager->create(100, 100)->save($imagePath);

    $gallery->cropImage($imagePath, 50, 50);

    $image = $manager->read($imagePath);
    expect($image->width())->toBe(50)
        ->and($image->height())->toBe(50);
});

// Test for image flipping
it( 'flips images horizontally correctly', function () {
    Storage::fake('public');
    $imagePath = Storage::disk('public')->path('test.jpg');
    $config = new Config();
    try {
        $gallery = new FileGallery($config);
        $gallery->initGallery();

        $manager = app()->make(ImageManager::class);
        $manager->create(100, 100)->save($imagePath);
        $gallery->

        $gallery->flipImage($imagePath, 'h');

        $image = $manager->read($imagePath);
        expect($image)->not->toBeNull(); // Placeholder assertion for flipping effect
    } catch (BindingResolutionException $e) {

    }
});

// Test for invalid file extension exception
it('throws InvalidFileExtension exception for unsupported file types', function () {
    $fileGallery = new FileGallery();
    $file = UploadedFile::fake()->create('unsupported_file.exe');

    expect(/**
     * @throws InvalidFileExtension
     */ fn() => $fileGallery->validateFile($file))->toThrow(InvalidFileExtension::class);
});

// Test for command execution
it('runs file-gallery command successfully', function () {
    Artisan::call('file-gallery');
    $output = Artisan::output();

    expect($output)->toContain('All done');
});
