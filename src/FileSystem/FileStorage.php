<?php
namespace MBsoft\FileGallery\FileSystem;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use InvalidArgumentException;
use MBsoft\FileGallery\Contracts\FileStorageHandlerInterface;
use MBsoft\FileGallery\Enums\FileExtension;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\Models\FileModel;

class FileStorage implements FileStorageHandlerInterface
{
    protected Disk $disk;

    public function __construct(Disk $disk)
    {
        $this->disk = $disk;
    }

    /**
     * List all files in the given directory.
     *
     * @param string $directory
     * @return array
     * @throws InvalidFileExtension
     */
    public function listFiles(string $directory = ''): array
    {
        return $this->disk->files($directory);
    }

    /**
     * Get the visibility of files on this disk.
     *
     * @return string
     */
    public function getVisibility(): string
    {
        return $this->disk->getVisibility();
    }

    /**
     * Get the permissions for the disk.
     *
     * @return int
     */
    public function getPermissions(): int
    {
        return $this->disk->getPermissions();
    }

    /**
     * Get the URL base for this disk.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->disk->getUrl();
    }

    /**
     * Check if a file exists.
     *
     * @param string $path
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        try {
            return $this->disk->delete($path);
        } catch (InvalidArgumentException $e) {
            // Handle error (e.g., log or rethrow)
            return false;
        }
    }

    /**
     * Get the contents of a file.
     *
     * @param string $path
     * @return string|null
     */
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

    /**
     * @throws InvalidFileExtension
     */
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

    /**
     * Store file content on the disk.
     */
    private function storeFileContent(string $filePath, string $content): bool
    {
        $fullPath = $this->disk->getRoot() . DIRECTORY_SEPARATOR . $filePath;
        return file_put_contents($fullPath, $content) !== false;
    }
}
