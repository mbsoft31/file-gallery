<?php

namespace MBsoft\FileGallery\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MBsoft\FileGallery\FileGallery
 */
class FileGallery extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MBsoft\FileGallery\FileGallery::class;
    }
}
