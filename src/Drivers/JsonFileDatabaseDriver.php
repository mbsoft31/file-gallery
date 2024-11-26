<?php

namespace MBsoft\FileGallery\Drivers;

use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;

class JsonFileDatabaseDriver implements DatabaseHandlerInterface
{
    public string $filePath;

    public function __construct(string $filePath = 'storage/file_gallery.json')
    {
        $this->filePath = $filePath;
        $this->initialize();
    }

    public function initialize(): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (! file_exists($this->filePath)) {
            // Initialize empty JSON array if the file doesn't exist
            $file = fopen($this->filePath, 'w');
            fwrite($file, json_encode([], JSON_PRETTY_PRINT, JSON_UNESCAPED_SLASHES));
            fclose($file);
        }
    }

    public function getDatabaseDriver(): string
    {
        return 'json';
    }

    public function getTableName(): string
    {
        return 'file_gallery_json';
    }

    public function getColumns(): array
    {
        return ['uuid', 'original_name', 'filename', 'path', 'extension', 'size', 'disk', 'mime_type', 'created_at', 'updated_at'];
    }

    public function addFile(array $fileData): bool
    {
        // Ensure the datetime fields are in proper format
        $fileData['created_at'] = $fileData['created_at']->toISOString();
        $fileData['updated_at'] = $fileData['updated_at']->toISOString();

        $data = $this->getAllFiles();
        $data[] = $fileData; // Add new file

        return file_put_contents($this->filePath, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }

    public function getFileRow(string $identifier): ?array
    {
        $files = $this->getAllFiles();
        foreach ($files as $file) {
            if ($file['id'] == $identifier) {
                return $file;
            }
        }

        return null; // Return null if not found
    }

    public function getAllFiles(): array
    {
        $data = file_get_contents($this->filePath);

        return json_decode($data, true) ?: []; // Return empty array if JSON is invalid
    }

    public function deleteFile(string $identifier): bool
    {
        $files = $this->getAllFiles();
        $updatedFiles = array_filter($files, fn ($file) => $file['id'] != $identifier);

        // Re-index the array to prevent gaps in the JSON file after filtering
        $updatedFiles = array_values($updatedFiles);

        return file_put_contents($this->filePath, json_encode($updatedFiles, JSON_PRETTY_PRINT)) !== false;
    }
}
