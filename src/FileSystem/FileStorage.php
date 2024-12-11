<?php
namespace MBsoft\FileGallery\FileSystem;

use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use InvalidArgumentException;
use MBsoft\FileGallery\Contracts\FileStorageHandlerInterface;

class FileStorage implements FileStorageHandlerInterface
{
    public function __construct(protected Disk $disk){}

    public function listFiles(string $directory = ''): array
    {
        try {
            return $this->disk->files($directory);
        } catch (InvalidFileExtension $e) {
            // handle the error
        }
    }

    public function getVisibility(): string
    {
        return $this->disk->getVisibility();
    }

    public function getPermissions(): int
    {
        return $this->disk->getPermissions();
    }

    public function getUrl(): string
    {
        return $this->disk->getUrl();
    }

    public function fileExists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    public function deleteFile(string $path): bool
    {
        try {
            return $this->disk->delete($path);
        } catch (InvalidArgumentException $e) {
            // Handle error (e.g., log or rethrow)
            return false;
        }
    }

    public function getFile(string $path): ?string
    {
        try {
            return $this->disk->file($path);
        } catch (InvalidArgumentException $e) {
            // Handle error (e.g., log or rethrow)
            return null;
        }
    }

    public function storeFile(UploadedFile $file, ?string $path = null): array
    {
        $file = $this->validateFile($file);
        $uuid = $this->generateUuid();
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $this->getFullFilePath($uuid, $extension, $path);

        // Store the file content on the disk
        $fileContent = $file->getContent();
        $filePathOnDisk = $filename;

        // Simulate storing the file on the disk
        $this->storeFileContent($filePathOnDisk, $fileContent);

        return [
            'uuid' => $uuid,
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $filePathOnDisk,
            'extension' => $extension,
            'size' => $file->getSize(),
            'disk' => $this->disk->getRoot(),
            'mime_type' => $file->getClientMimeType(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function validateFile(UploadedFile $file): UploadedFile
    {
        $extension = strtolower($file->getClientOriginalExtension());

        /*if (! FileExtension::isValidExtension($extension) || ! in_array($extension, $this->allowedExtensions, true)) {
            throw new InvalidFileExtension("Invalid file extension: $extension");
        }*/

        return $file;
    }

    public function getFullFilePath(string $uuid, string $extension, ?string $path = null): string
    {
        $separator = (! $path || str_ends_with($path, DIRECTORY_SEPARATOR)) ? '' : DIRECTORY_SEPARATOR;

        return "$path$separator$uuid.$extension";
    }

    private function generateUuid(): string
    {
        return uniqid('', true); // Or use a more sophisticated UUID generation method
    }

    private function storeFileContent(string $filePath, string $content): bool
    {
        $fullPath = $this->disk->getRoot() . DIRECTORY_SEPARATOR . $filePath;
        return file_put_contents($fullPath, $content) !== false;
    }
}
