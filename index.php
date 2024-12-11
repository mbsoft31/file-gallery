<?php

use Symfony\Component\HttpFoundation\File\UploadedFile;
use MBsoft\FileGallery\Enums\FileExtension;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\FileGallery;

use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Nette\Utils\ImageException;

session_start();

include 'vendor/autoload.php';

try {

    $settings = getSettings();

    $gallery = new FileGallery($settings);

    dump($gallery->listFiles());

    $image = generateImage();

    $uploadedFile = new UploadedFile(
        './storage/tmp/image.jpg',
        'image.jpg',
        FileExtension::getMimeType(FileExtension::JPEG)
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
    $image->save($path . DIRECTORY_SEPARATOR . 'image.jpg');
    return $image;
}
