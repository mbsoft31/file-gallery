<?php

namespace MBsoft\FileGallery;

class Config {
    public function __construct(
        public bool   $database = false,
        public string $database_provider = "sqlite",
        public string $database_url = "database/filegallery.sqlite",
        public string $database_table_name = "file_gallery_table",
        public string $disk = "public",
        public string $disk_folder = "gallery",
        public array  $allowed_file_extensions = ['jpg', 'png', 'gif', 'bmp', 'pdf', 'docx', 'xlsx'], // Expanded file types
    ){}
}
