<?php

namespace MBsoft\FileGallery;

enum FileExtension: string
{
    // Common image extensions
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case PNG = 'png';
    case GIF = 'gif';
    case BMP = 'bmp';
    case SVG = 'svg';
    case WEBP = 'webp';

    // Common document extensions
    case PDF = 'pdf';
    case DOC = 'doc';
    case DOCX = 'docx';
    case TXT = 'txt';

    // Audio and video extensions
    case MP3 = 'mp3';
    case WAV = 'wav';
    case MP4 = 'mp4';
    case AVI = 'avi';

    // Archive extensions
    case ZIP = 'zip';
    case TAR = 'tar';
    case GZ = 'gz';
    case RAR = 'rar';

    /**
     * Returns an array of all possible file extensions as strings.
     */
    public static function getAllExtensions(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Checks if a given extension is a valid case of FileExtension enum.
     */
    public static function isValidExtension(string $extension): bool
    {
        return in_array($extension, self::getAllExtensions(), true);
    }

    /**
     * Returns the extension instance for a given string value, if valid.
     */
    public static function fromString(string $extension): ?FileExtension
    {
        foreach (self::cases() as $case) {
            if ($case->value === strtolower($extension)) {
                return $case;
            }
        }

        return null;
    }
}
