<?php

namespace MBsoft\FileGallery\Handlers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MBsoft\FileGallery\Contracts\FileStorageHandlerInterface;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;

class FileStorageHandler implements FileStorageHandlerInterface
{
    public function __construct(
        protected array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'],
        protected string $disk = "public",
        protected string $diskFolder = "file-gallery",
    ) {}

    /**
     * @throws InvalidFileExtension
     */
    public function validateFile(UploadedFile $file): UploadedFile
    {
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new InvalidFileExtension("Invalid file extension: {$extension}");
        }
        return $file;
    }

    /**
     * @throws InvalidFileExtension
     */
    public function storeFile(UploadedFile $file, ?string $path = null): array
    {
        $file = $this->validateFile($file);
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $filename = $this->getFullFilePath($uuid, $extension, $path);
        $pathOnDisk = $file->storeAs($this->diskFolder, $filename, $this->disk);

        return [
            'uuid' => $uuid,
            'extension' => $extension,
            'filename' => $filename,
            'path' => $pathOnDisk,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ];
    }

    public function getFullFilePath(string $uuid, string $extension, ?string $path = null): string
    {
        $separator = (!$path || str_ends_with($path, DIRECTORY_SEPARATOR)) ? "" : DIRECTORY_SEPARATOR;
        return "{$path}{$separator}{$uuid}.{$extension}";
    }

    public function getFile(string $path): ?string
    {
        // Retrieve the file contents from storage
        return Storage::disk($this->disk)->get($path);
    }

    public function deleteFile(string $path): bool
    {
        // Delete the file from storage
        return Storage::disk($this->disk)->delete($path);
    }

    public function fileExists(string $path): bool
    {
        // Check if the file exists on the specified disk
        return Storage::disk($this->disk)->exists($path);
    }

    public function listFiles(string $directory): array
    {
        // List all files in the specified directory
        return Storage::disk($this->disk)->files($directory);
    }
}
