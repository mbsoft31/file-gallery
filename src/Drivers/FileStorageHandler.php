<?php

namespace MBsoft\FileGallery\Drivers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MBsoft\FileGallery\Contracts\FileStorageHandlerInterface;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\FileExtension;
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
    public function storeFile(UploadedFile $file, ?string $path = null): array
    {
        $file = $this->validateFile($file);
        $uuid = Str::uuid()->toString();
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $this->getFullFilePath($uuid, $extension, $path);
        $pathOnDisk = $file->storeAs($this->diskFolder, $filename, $this->disk);

        return FileModel::create([
            'uuid' => $uuid,
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $pathOnDisk,
            'extension' => $extension,
            'size' => $file->getSize(),
            'disk' => $this->disk,
            'mime_type' => $file->getClientMimeType(),
        ]);
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
