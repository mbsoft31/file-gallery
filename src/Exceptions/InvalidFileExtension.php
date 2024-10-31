<?php

namespace MBsoft\FileGallery\Exceptions;

use Throwable;

class InvalidFileExtension extends \Exception
{
    public function __construct(
        string $message = "",
        int $code = 0,
        Throwable|null $previous = null
    )
    {
        $message = $message ?: "Invalid file extension";
        parent::__construct($message, $code, $previous);
    }
}
