<?php

// config for MBsoft/FileGallery
return [
    'database' => [
        'enabled' => true,
        'provider' => 'json',
        'url' => 'database/file_gallery.json',
        'table_name' => 'files',
    ],

    "disks" => [
        'local' => [
            'driver' => 'local',
            'root' => storage_dir() . '/app/private',
            'url' => null,
            'visibility' => 'private',
            'permissions' => '0644',
            'serve' => true,
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_dir() . '/app/public',
            'url' => '/storage',
            'visibility' => 'public',
            'permissions' => '0644',
            'throw' => false,
        ],
    ],

    'disk' => 'public',

    'allowed_file_extensions' => explode(
        separator: ',',
        string: implode(separator: ',', array: array_merge(
            \MBsoft\FileGallery\Enums\FileExtension::getImageExtensions(),
            \MBsoft\FileGallery\Enums\FileExtension::getVideoExtensions(),
            \MBsoft\FileGallery\Enums\FileExtension::getDocumentExtensions(),
        )),
    ),

    'image' => [
        'driver' => \MBsoft\FileGallery\FileGallery::$GD,
    ],
];
