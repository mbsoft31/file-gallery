<?php

namespace MBsoft\FileGallery\Drivers;

use MBsoft\FileGallery\Contracts\DatabaseHandlerInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SqliteDatabaseDriver implements DatabaseHandlerInterface
{
    protected string $tableName;

    public function __construct()
    {
        $this->tableName = 'file_gallery';
    }

    public function initialize(): void
    {
        if (!Schema::hasTable($this->tableName)) {
            Schema::create($this->tableName, function ($table) {
                $table->id();
                $table->string('filename');
                $table->string('path');
                $table->timestamps();
            });
        }
    }

    public function getDatabaseDriver(): string
    {
        return 'sqlite';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function addFile(array $fileData): bool
    {
        return DB::table($this->tableName)->insert($fileData);
    }

    public function getFileRow(int|string $identifier): ?array
    {
        $file = DB::table($this->tableName)->where('id', $identifier)->first();
        return $file ? (array)$file : null;
    }

    public function deleteFile(int|string $identifier): bool
    {
        return DB::table($this->tableName)->where('id', $identifier)->delete() > 0;
    }

    public function getColumns(): array
    {
        return Schema::getColumnListing($this->tableName);
    }

    public function getAllFiles(): array
    {
        return DB::table($this->tableName)->get()->toArray();
    }
}
