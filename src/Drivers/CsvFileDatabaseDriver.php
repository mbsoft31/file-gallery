<?php

namespace MBsoft\FileGallery\Drivers;

use Illuminate\Support\Facades\Storage;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;

class CsvFileDatabaseDriver implements DatabaseHandlerInterface
{
    public string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function initialize(): void
    {
        if (!file_exists($this->filePath)) {
            // Create a CSV file with headers if it doesn't exist
            $headers = ['id', 'filename', 'path', 'created_at', 'updated_at'];
            file_put_contents($this->filePath, implode(',', $headers) . PHP_EOL);
        }
    }

    public function getDatabaseDriver(): string
    {
        return 'csv';
    }

    public function getTableName(): string
    {
        return 'file_gallery'; // Not applicable for CSV, but keeping for consistency
    }

    public function addFile(array $fileData): bool
    {
        $fileData['created_at'] = $fileData['created_at']->toISOString();
        $fileData['updated_at'] = $fileData['updated_at']->toISOString();
        $row = implode(',', $fileData) . PHP_EOL;
        return file_put_contents($this->filePath, $row, FILE_APPEND) !== false;
    }

    public function getFileRow(int|string $identifier): ?array
    {
        $file = fopen($this->filePath, 'r');
        $header = fgetcsv($file); // Get the header row
        while (($data = fgetcsv($file)) !== false) {
            $row = array_combine($header, $data);
            if ($row['id'] == $identifier) {
                fclose($file);
                return $row; // Return the matched row
            }
        }
        fclose($file);
        return null; // Return null if not found
    }

    public function deleteFile(int|string $identifier): bool
    {
        $rows = [];
        $fileFound = false;

        // Read current data
        $file = fopen($this->filePath, 'r');
        $header = fgetcsv($file);
        $rows[] = $header; // Preserve header

        while (($data = fgetcsv($file)) !== false) {
            $row = array_combine($header, $data);
            if ($row['id'] == $identifier) {
                $fileFound = true; // We found the file, do not add to new rows
                continue;
            }
            $rows[] = $row; // Add other rows
        }
        fclose($file);

        // Write back the rows except the deleted one
        $file = fopen($this->filePath, 'w');
        foreach ($rows as $row) {
            fputcsv($file, $row);
        }
        fclose($file);

        return $fileFound; // Return true if a file was deleted
    }

    public function getColumns(): array
    {
        return ['id', 'filename', 'path', 'created_at', 'updated_at'];
    }

    public function getAllFiles(): array
    {
        // TODO: Implement getAllFiles() method.
    }
}
