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
        if (! file_exists($this->filePath)) {
            file_put_contents($this->filePath, json_encode([]));
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
        return ['id', 'filename', 'path', 'created_at', 'updated_at'];
    }

    public function addFile(array $fileData): bool
    {
        $data = $this->getAllFiles();
        $data[] = $fileData;

        return file_put_contents($this->filePath, json_encode($data)) !== false;
    }

    public function getFileRow(string $identifier): ?array
    {
        $files = $this->getAllFiles();
        foreach ($files as $file) {
            if ($file['id'] == $identifier) {
                return $file;
            }
        }

        return null;
    }

    public function getAllFiles(): array
    {
        $data = file_get_contents($this->filePath);

        return json_decode($data, true) ?: [];
    }

    public function deleteFile(string $identifier): bool
    {
        $files = $this->getAllFiles();
        $updatedFiles = array_filter($files, fn ($file) => $file['id'] != $identifier);

        // Re-index the array to prevent gaps in JSON file after filtering
        $updatedFiles = array_values($updatedFiles);

        return file_put_contents($this->filePath, json_encode($updatedFiles)) !== false;
    }
}
