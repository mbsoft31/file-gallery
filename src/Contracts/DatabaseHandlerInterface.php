<?php

namespace MBsoft\FileGallery\Contracts;

interface DatabaseHandlerInterface
{
    public function initialize(): void;

    public function getDatabaseDriver(): string;

    public function getTableName(): string;

    public function getColumns(): array;

    public function addFile(array $fileData): bool;

    public function getFileRow(string $identifier): ?array;

    public function getAllFiles(): array;

    public function deleteFile(string $identifier): bool;
}
