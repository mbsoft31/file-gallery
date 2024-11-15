<?php

// config for Mouadh Bekhouche/FileGallery
return [

    'driver' => env('FILEGALLERY_DRIVER', 'sqlite'), // Default to 'sqlite'

    'disk' => env('FILEGALLERY_DISK', 'public'),
    'disk_folder' => env('FILEGALLERY_DISK_FOLDER', 'file-gallery'),

    'image' => [
        'driver' => 'gd',
    ],

];
