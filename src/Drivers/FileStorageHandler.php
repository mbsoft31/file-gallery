<?php

namespace MBsoft\FileGallery\Drivers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use MBsoft\FileGallery\Contracts\FileStorageHandlerInterface;
use MBsoft\FileGallery\Enums\FileExtension;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\Models\FileModel;

class FileStorageHandler implements FileStorageHandlerInterface
{
    protected string $disk;

    protected string $diskFolder;

    public function __construct(
        protected array $allowedExtensions = [],
        string $disk = 'public',
        string $diskFolder = 'file-gallery'
    ) {
        $this->allowedExtensions = $allowedExtensions ?: FileExtension::getAllExtensions();
        $this->disk = $disk;
        $this->diskFolder = $diskFolder;
    }

    /**
     * @throws InvalidFileExtension
     */
    public function validateFile(UploadedFile $file): UploadedFile
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! FileExtension::isValidExtension($extension) || ! in_array($extension, $this->allowedExtensions, true)) {
            throw new InvalidFileExtension("Invalid file extension: $extension");
        }

        return $file;
    }

    /**
     * @throws InvalidFileExtension
     */
    public function storeFile(UploadedFile $file, ?string $path = null): FileModel
    {
        $file = $this->validateFile($file);
        $uuid = $this->generateUuid();
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $this->getFullFilePath($uuid, $extension, $path);

        // Store the file content on the disk
        $fileContent = $file->getContent();
        $filePathOnDisk = $this->diskFolder . DIRECTORY_SEPARATOR . $filename;

        // Simulate storing the file on the disk
        $this->storeFileContent($filePathOnDisk, $fileContent);

        return FileModel::create([
            'uuid' => $uuid,
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $filePathOnDisk,
            'extension' => $extension,
            'size' => $file->getSize(),
            'disk' => $this->disk,
            'mime_type' => $file->getClientMimeType(),
        ]);
    }

    /**
     * Store file content on the disk.
     */
    private function storeFileContent(string $filePath, string $content): bool
    {
        $fullPath = $this->disk->getRoot() . DIRECTORY_SEPARATOR . $filePath;
        return file_put_contents($fullPath, $content) !== false;
    }

    /**
     * Generate a UUID.
     */
    private function generateUuid(): string
    {
        return uniqid('', true); // Or use a more sophisticated UUID generation method
    }

    public function getFullFilePath(string $uuid, string $extension, ?string $path = null): string
    {
        $separator = (! $path || str_ends_with($path, DIRECTORY_SEPARATOR)) ? '' : DIRECTORY_SEPARATOR;

        return "$path$separator$uuid.$extension";
    }

    public function getFile(string $path): ?string
    {
        return Storage::disk($this->disk)->get($path);
    }

    public function deleteFile(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function fileExists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    public function listFiles(string $directory): array
    {
        return Storage::disk($this->disk)->files($directory);
    }
}
