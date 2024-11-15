<?php

namespace MBsoft\FileGallery\Traits;

use Illuminate\Contracts\Container\BindingResolutionException;
use Intervention\Image\Drivers;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

trait ImageOperationsTrait
{
    public static string $GD = Drivers\Gd\Driver::class;

    public static string $IMAGICK = Drivers\Imagick\Driver::class;

    protected ImageManager $imageManager;

    /**
     * @throws BindingResolutionException
     */
    public function initializeImageManager(): void
    {
        $this->imageManager = app()->make(ImageManager::class);
    }

    public function readImage(string $filePath): ImageInterface
    {
        return $this->imageManager->read($filePath);
    }

    public function resizeImage($path, $width, $height): ImageInterface
    {
        return $this->readImage($path)
            ->resize($width, $height)
            ->save($path);
    }

    public function cropImage($path, $width, $height, $x = null, $y = null): ImageInterface
    {
        return $this->readImage($path)
            ->crop($width, $height, $x, $y)
            ->save($path);
    }

    public function flipImage($path, $mode = 'h'): ImageInterface
    {
        return $this->readImage($path)
            ->flip($mode)
            ->save($path);
    }

    public function rotateImage($path, $angle): ImageInterface
    {
        return $this->readImage($path)
            ->rotate($angle)
            ->save($path);
    }

    public function grayscaleImage($path): ImageInterface
    {
        return $this->readImage($path)
            ->greyscale()
            ->save($path);
    }

    public function blurImage($path, $amount = 5): ImageInterface
    {
        return $this->readImage($path)
            ->blur($amount)
            ->save($path);
    }
}
