<?php

namespace MBsoft\FileGallery\Commands;

use Illuminate\Console\Command;

class FileGalleryCommand extends Command
{
    public $signature = 'file-gallery';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
