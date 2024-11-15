<?php

namespace MBsoft\FileGallery\Commands;

use Illuminate\Console\Command;

class FileGalleryCommand extends Command
{
    public $signature = 'file-gallery:list';

    public $description = 'List all files in the gallery';

    public function handle(): int
    {
        $this->info('Listing files...');

        return self::SUCCESS;
    }
}
