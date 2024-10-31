<?php

namespace MBsoft\FileGallery;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MBsoft\FileGallery\Exceptions\InvalidFileExtension;
use MBsoft\FileGallery\Traits\ImageOperationsTrait;

class FileGallery {

    use ImageOperationsTrait;

    public function __construct(
        public ?Config $config = null,
    ) {
        $this->config = $config ?? new Config();
    }

    /**
     * @throws BindingResolutionException
     */
    public function initGallery() : bool {
        $this->initializeImageManager();

        if($this->config->database) {
            $this->configureDatabase();
        } else {
            $this->configureFileStorage();
        }
        return true;
    }

    private function configureDatabase(): void
    {
        // Database configuration logic
        if ($this->config->database_provider === 'sqlite') {
            config(['database.connections.sqlite.database' => $this->config->database_url]);
        }
        if (!Schema::hasTable($this->config->database_table_name)) {
            Schema::create($this->config->database_table_name, function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->timestamps();
            });
        }
    }

    private function configureFileStorage(): void
    {
        // File storage configuration logic
        Storage::disk($this->config->disk)->makeDirectory($this->config->disk_folder);
    }

    /**
     * @throws InvalidFileExtension
     */
    public function validateFile(UploadedFile $file) : UploadedFile
    {
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, $this->config->allowed_file_extensions)) {
            throw new InvalidFileExtension();
        }

        // more validation

        return $file;
    }

    public function getFullFilePath($uuid, $extension, $path = "") : string
    {
        $path_separator = ( $path == "" && !str_ends_with($path, DIRECTORY_SEPARATOR) ) ? DIRECTORY_SEPARATOR : "";
        return sprintf("%s%s%s.%s", $path, $path_separator, $uuid, $extension);
    }

    /**
     * @throws InvalidFileExtension
     */
    public function storeFile(UploadedFile $file, string $path = ""): array
    {
        $file = $this->validateFile($file);

        // generate a random string uuid
        $uuid = Str::uuid()->toString();
        $extension = $file->getClientOriginalExtension();
        $filename = $this->getFullFilePath($uuid, $extension, $path);
        $path_on_disk = $file->storeAs($this->config->disk_folder, $filename, $this->config->disk);

        return [
            'uuid' => $uuid,
            'extension' => $extension,
            'filename' => $filename,
            'path' => $path_on_disk,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ];
    }

    public function optimizePDF($path) {
        // Add PDF optimization logic here
    }
}
