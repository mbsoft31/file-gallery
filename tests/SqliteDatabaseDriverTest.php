<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use MBsoft\FileGallery\Drivers\CsvFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\JsonFileDatabaseDriver;
use MBsoft\FileGallery\Drivers\SqliteDatabaseDriver;

// Helper data for testing
$fileData = [
    'id' => 1,
    'uuid' => '1234-1235-4567',
    'original_name' => 'test_file',
    'filename' => 'test_file.jpg',
    'path' => '/uploads/test_file.jpg',
    'extension' => 'jpg',
    'size' => '100025',
    'disk' => 'public',
    'mime_type' => '',
    'created_at' => now(),
    'updated_at' => now(),
];

// Common assertion function
function assertFileExistsInDriver($driver, $fileData): void
{
    $fileRow = $driver->getFileRow($fileData['id']);
    expect($fileRow)->not->toBeNull()
        ->and($fileRow['filename'])->toBe($fileData['filename']);
}

// SQLite driver tests
it('initializes SQLite driver and creates table', function () {
    $sqliteDriver = new SqliteDatabaseDriver;
    $sqliteDriver->initialize();
    expect(Schema::hasTable($sqliteDriver->getTableName()))->toBeTrue();
    DB::table($sqliteDriver->getTableName())->truncate(); // Cleanup after test
});

it('adds, retrieves, and deletes file in SQLite driver', function () use ($fileData) {
    $sqliteDriver = new SqliteDatabaseDriver;
    $sqliteDriver->initialize();

    $addResult = $sqliteDriver->addFile($fileData);
    expect($addResult)->toBeTrue();
    assertFileExistsInDriver($sqliteDriver, $fileData);

    $deleteResult = $sqliteDriver->deleteFile($fileData['id']);
    expect($deleteResult)->toBeTrue()
        ->and($sqliteDriver->getFileRow($fileData['id']))->toBeNull();
    DB::table($sqliteDriver->getTableName())->truncate(); // Cleanup after test
});

// JSON driver tests
it('initializes JSON driver and creates file if not exists', function () {
    Storage::fake('local');
    $jsonDriver = new JsonFileDatabaseDriver(Storage::disk('local')->path('file_gallery.json'));

    $jsonDriver->initialize();
    expect(file_exists($jsonDriver->filePath))->toBeTrue();
});

it('adds, retrieves, and deletes file in JSON driver', function () use ($fileData) {
    Storage::fake('local');
    $jsonDriver = new JsonFileDatabaseDriver(Storage::disk('local')->path('file_gallery.json'));

    $addResult = $jsonDriver->addFile($fileData);
    expect($addResult)->toBeTrue();
    assertFileExistsInDriver($jsonDriver, $fileData);

    // Attempt to delete the file and check for success
    $deleteResult = $jsonDriver->deleteFile($fileData['id']);
    expect($deleteResult)->toBeTrue();

    // Verify the file is actually deleted
    $fileRowAfterDeletion = $jsonDriver->getFileRow($fileData['id']);
    expect($fileRowAfterDeletion)->toBeNull();
});

// Csv driver tests
it('initializes CSV driver and creates file if not exists', function () {
    Storage::fake('local');
    $csvDriver = new CsvFileDatabaseDriver(Storage::disk('local')->path('file_gallery.csv'));

    $csvDriver->initialize();
    expect(file_exists($csvDriver->filePath))->toBeTrue();
});

it('adds, retrieves, and deletes file in CSV driver', function () use ($fileData) {
    Storage::fake('local');
    $csvDriver = new CsvFileDatabaseDriver(Storage::disk('local')->path('file_gallery.csv'));
    $csvDriver->initialize();

    $addResult = $csvDriver->addFile($fileData);
    expect($addResult)->toBeTrue();

    // Verify the file was added
    $fileRow = $csvDriver->getFileRow($fileData['id']);
    expect($fileRow)->not->toBeNull()
        ->and($fileRow['filename'])->toBe($fileData['filename']);

    $deleteResult = $csvDriver->deleteFile($fileData['id']);
    expect($deleteResult)->toBeTrue()
        ->and($csvDriver->getFileRow($fileData['id']))->toBeNull();
});
