<?php

// config for MBsoft/FileGallery
return [
    'database' => [
        'enabled' => env('FILEGALLERY_DATABASE_ENABLED', false),
        'provider' => env('FILEGALLERY_DATABASE_PROVIDER', 'sqlite'),
        'url' => env('FILEGALLERY_DATABASE_URL', 'database/filegallery.sqlite'),
        'table_name' => env('FILEGALLERY_DATABASE_TABLE_NAME', 'files'),
    ],

    'disk' => env('FILEGALLERY_DISK', 'public'),
    'disk_folder' => env('FILEGALLERY_DISK_FOLDER', 'gallery'),

    'allowed_file_extensions' => explode(
        separator: ',',
        string: env(
            key: 'FILEGALLERY_ALLOWED_FILE_EXTENSIONS',
            default: implode(separator: ',', array: array_merge(
                \MBsoft\FileGallery\FileExtension::getImageExtensions(),
                \MBsoft\FileGallery\FileExtension::getVideoExtensions(),
                \MBsoft\FileGallery\FileExtension::getDocumentExtensions(),
            ))
        )
    ),

    'image' => [
        'driver' => env('FILEGALLERY_IMAGE_DRIVER', \MBsoft\FileGallery\FileGallery::$GD),
    ],
];
