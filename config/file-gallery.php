<?php

// config for MBsoft/FileGallery
use MBsoft\FileGallery\Enums\FileExtension;
use MBsoft\FileGallery\FileGallery;

return [
    'storage_path' => storage_dir(),
    "drivers" => [
        "sqlite" => [
            "driver" => "sqlite",
            "path" => 'data/sqlite',
            "table" => "file_gallery.sqlite",
        ],
        "json" => [
            "driver" => "json",
            "path" => 'data/json',
            "table" => "file_gallery.json",
        ],
        "csv" => [
            "driver" => "csv",
            "path" => 'data/csv',
            "table" => "file_gallery.csv",
        ]
    ],
    "disks" => [
        'local' => [
            'driver' => 'local',
            'root' => 'app/private',
            'url' => null,
            'visibility' => 'private',
            'permissions' => '0644',
            'serve' => true,
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => 'app/public',
            'url' => '/storage',
            'visibility' => 'public',
            'permissions' => '0644',
            'throw' => false,
        ],
    ],

    'database' => "csv",
    'disk' => 'public',

    'allowed_file_extensions' => explode(
        separator: ',',
        string: implode(separator: ',', array: array_merge(
            FileExtension::getImageExtensions(),
            FileExtension::getVideoExtensions(),
            FileExtension::getDocumentExtensions(),
        )),
    ),

    'image' => [
        'driver' => FileGallery::$GD,
        'options' => []
    ],
];
