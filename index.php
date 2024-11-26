<?php

use Symfony\Component\HttpFoundation\File\UploadedFile;
use MBsoft\FileGallery\Drivers\SqliteDatabaseDriver;
use MBsoft\FileGallery\Enums\FileExtension;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\FileGallery;
use MBsoft\FileGallery\FileSystem\Disk;
use MBsoft\FileGallery\FileSystem\FileStorage;
use MBsoft\Settings\Enums\ConfigFormat;

use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageException;
use Nette\Utils\ImageType;

session_start();

include 'vendor/autoload.php';

try {
    /*$settings = MBsoft\Settings\Settings::loadFromFile('config/file-gallery.php', format: ConfigFormat::PHP);

    //$diskConfig = $settings->get('disks.local');
    $diskConfig = $settings->get('disks.public');

    $storage = new FileStorage(new Disk($diskConfig));

    $gallery = new FileGallery($settings, $storage, new SqliteDatabaseDriver());

    $image = generateImage();

    $uploadedFile = new UploadedFile(
        './storage/tmp/image.png',
        'image.png',
        FileExtension::getMimeType(FileExtension::PNG)
    );

    $gallery->storeFile(
        $uploadedFile
    );

    // List gallery dir files
    $list = $storage->listFiles();
    $listGallery = $storage->listFiles('gallery');

    dump("List: ",$list,$listGallery,);*/

    $gallery = FileGallery::new();

    dump($gallery->listFiles());

    $image = generateImage();

    $uploadedFile = new UploadedFile(
        './storage/tmp/image.png',
        'image.png',
        FileExtension::getMimeType(FileExtension::PNG)
    );

    $fileData = $gallery->storeFile(
        $uploadedFile
    );

    dump($gallery->listFiles());
    dump($gallery->getAllFiles());

} catch (InvalidFileExtension $e) {
    dump("InvalidFileExtension: ", $e->getMessage());
} catch (ImageException $e) {
    dump('ImageException: ', $e->getMessage());
} catch (JsonException $e) {
    dump('JsonException: ', $e->getMessage());
} catch (Exception $e) {
    dump('Exception: ', $e->getMessage());
}


/**
 * @throws ImageException
 */
function generateImage($path = 'storage/tmp', $name = null): Image
{
    $image = Image::fromBlank(150, 150, ImageColor::rgb(190, 115, 24));
    $image->save($path . DIRECTORY_SEPARATOR . 'image.png');
    return $image;
}
