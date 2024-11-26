<?php

namespace MBsoft\FileGallery\Enums;

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
     * Returns an array of image file extensions as strings.
     */
    public static function getImageExtensions(): array
    {
        return [
            self::JPG->value,
            self::JPEG->value,
            self::PNG->value,
            self::GIF->value,
            self::BMP->value,
            self::SVG->value,
            self::WEBP->value,
        ];
    }

    /**
     * Returns an array of document file extensions as strings.
     */
    public static function getDocumentExtensions(): array
    {
        return [
            self::PDF->value,
            self::DOC->value,
            self::DOCX->value,
            self::TXT->value,
        ];
    }

    /**
     * Returns an array of audio file extensions as strings.
     */
    public static function getAudioExtensions(): array
    {
        return [
            self::MP3->value,
            self::WAV->value,
        ];
    }

    /**
     * Returns an array of video file extensions as strings.
     */
    public static function getVideoExtensions(): array
    {
        return [
            self::MP4->value,
            self::AVI->value,
        ];
    }

    /**
     * Returns an array of archive file extensions as strings.
     */
    public static function getArchiveExtensions(): array
    {
        return [
            self::ZIP->value,
            self::TAR->value,
            self::GZ->value,
            self::RAR->value,
        ];
    }

    /**
     * @throws \Exception
     */
    public static function getMimeType(FileExtension $extension): string
    {
        return match ($extension) {
            self::JPEG => 'image/jpeg',
            self::PNG => 'image/png',
            self::GIF => 'image/gif',
            self::BMP => 'image/bmp',
            self::SVG => 'image/svg+xml',
            self::WEBP => 'image/webp',

            self::PDF => 'application/pdf',
            self::DOC => 'application/msword',
            self::DOCX => 'application/vnd.ms-excel',
            self::TXT => 'text/plain',
            self::MP3 => 'audio/mpeg',
            self::WAV => 'audio/x-wav',
            self::MP4 => 'video/mp4',
            self::AVI => 'video/avi',
            self::ZIP => 'application/zip',
            self::TAR => 'application/x-tar',
            self::GZ => 'application/x-gzip',
            self::RAR => 'application/x-rar',
            default => throw new \Exception('Unexpected match value'),
        };
    }

    /**
     * Checks if a given extension is a valid case of FileExtension enum.
     */
    public static function isValidExtension(string $extension): bool
    {
        return in_array(strtolower($extension), self::getAllExtensions(), true);
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
