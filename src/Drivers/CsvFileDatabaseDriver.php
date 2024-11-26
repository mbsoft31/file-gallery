<?php

namespace MBsoft\FileGallery\Drivers;

use Exception;
use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;

class CsvFileDatabaseDriver implements DatabaseHandlerInterface
{
    public string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function getDatabaseDriver(): string
    {
        return 'csv';
    }

    public function getTableName(): string
    {
        return 'file_gallery'; // Not applicable for CSV, but keeping for consistency
    }

    public function getColumns(): array
    {
        return ['uuid', 'original_name', 'filename', 'path', 'extension', 'size', 'disk', 'mime_type', 'created_at', 'updated_at'];
    }

    public function initialize(): void
    {
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (! file_exists($this->filePath)) {
            // Create a CSV file with headers if it doesn't exist
            $headers = $this->getColumns();
            $file = fopen($this->filePath, 'w');
            fputcsv($file, $headers);  // Write header row
            fclose($file);
        }
    }

    public function addFile(array $fileData): bool
    {
        // Ensure that the necessary fields are present and properly formatted
        $fileData['created_at'] = $fileData['created_at']->toISOString();
        $fileData['updated_at'] = $fileData['updated_at']->toISOString();

        // Open the CSV file and append the data
        $file = fopen($this->filePath, 'a');
        if ($file) {
            dump($fileData);
            // Write the row as an array
            fputcsv($file, $fileData);
            fclose($file);

            return true;
        }

        return false;
    }

    public function getAllFiles(): array
    {
        $rows = [];
        $file = fopen($this->filePath, 'r');
        if ($file) {
            $header = fgetcsv($file);  // Read the header
            while (($data = fgetcsv($file)) !== false) {
                $row = array_combine($header, $data);  // Combine header with row data
                $rows[] = $row;
            }
            fclose($file);
        }

        return $rows;
    }

    public function deleteFile(int|string $identifier): bool
    {
        $rows = [];
        $fileFound = false;

        // Read the existing CSV data
        $file = fopen($this->filePath, 'r');
        if ($file) {
            $header = fgetcsv($file);  // Read the header row
            $rows[] = $header;  // Keep the header row

            while (($data = fgetcsv($file)) !== false) {
                $row = array_combine($header, $data);
                if ($row['id'] == $identifier) {
                    $fileFound = true;  // File found, skip it

                    continue;
                }
                $rows[] = $row;  // Add the row to the new data
            }
            fclose($file);

            // Rewrite the CSV file with the remaining rows
            $file = fopen($this->filePath, 'w');
            foreach ($rows as $row) {
                fputcsv($file, $row);  // Ensure each row is an array
            }
            fclose($file);
        }

        return $fileFound;
    }

    /**
     * @throws Exception
     */
    public function getFileRow(int|string $identifier): ?array
    {
        $file = fopen($this->filePath, 'r');
        if (! $file) {
            throw new Exception("Unable to open file for reading: $this->filePath");
        }

        $header = fgetcsv($file); // Get the header row
        if (! $header) {
            fclose($file);

            return null; // No headers, return null
        }

        while (($data = fgetcsv($file)) !== false) {
            if (count($header) !== count($data)) {
                // Log an error or handle this case (e.g., skip this row)
                continue;
            }
            $row = array_combine($header, $data);
            if ($row['id'] == $identifier) {
                fclose($file);

                return $row; // Return the matched row
            }
        }
        fclose($file);

        return null; // Return null if not found
    }

    /**
     * @throws Exception
     */
    private function writeToFile(array $data): bool
    {
        $file = fopen($this->filePath, false ? 'w' : 'a');
        if (! $file) {
            throw new Exception("Unable to open file for writing: $this->filePath");
        }

        if (is_array($data)) {
            // Writing rows
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
        } else {
            // Writing a single row (string)
            fwrite($file, $data);
        }

        fclose($file);

        return true;
    }
}
