<?php

// config for MBsoft/FileGallery
return [
    'database' => [
        'enabled' => env('FILEGALLERY_DATABASE_ENABLED', false),
        'provider' => env('FILEGALLERY_DATABASE_PROVIDER', 'sqlite'),
        'url' => env('FILEGALLERY_DATABASE_URL', 'database/filegallery.sqlite'),
        'table_name' => env('FILEGALLERY_DATABASE_TABLE_NAME', 'file_gallery_table'),
    ],

    'disk' => env('FILEGALLERY_DISK', 'public'),
    'disk_folder' => env('FILEGALLERY_DISK_FOLDER', 'gallery'),

    'allowed_file_extensions' => explode(',', env('FILEGALLERY_ALLOWED_FILE_EXTENSIONS', 'jpg,png,gif,bmp,pdf,docx,xlsx')),

    'image' => [
        'driver' => env('FILEGALLERY_IMAGE_DRIVER', 'gd'),
    ],
];
