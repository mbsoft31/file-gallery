<?php

namespace MBsoft\FileGallery;

use Intervention\Image\Drivers\AbstractDriver;
use MBsoft\FileGallery\FileSystem\FileStorage;
use MBsoft\FileGallery\Traits\FileOperationsTrait;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;
use MBsoft\FileGallery\Traits\ImageOperationsTrait;
use MBsoft\FileGallery\Traits\PDFOperationsTrait;
use MBsoft\Settings\Settings;

class FileGallery
{
    use FileOperationsTrait;
    use ImageOperationsTrait;
    use PDFOperationsTrait;

    protected FileStorage               $fileStorageHandler;
    protected ?DatabaseHandlerInterface $databaseHandler = null;

    /**
     */
    public function __construct(
        protected Settings $settings,
    ) {
        $this->fileStorageHandler = initFileStorage($this->settings);
        $this->databaseHandler = initDatabase($this->settings);
        $this->databaseHandler->initialize();
        $this->imageManager = initImageManager($this->settings);
    }

    public static function new(Settings $settings): FileGallery
    {
        return new self($settings);
    }
}
