<?php

namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Helpers\Zip;

class ArchiveService
{
    /**
     * @param string $sourceDirectory
     * @param string $destinationDirectory
     */
    public static function create(string $sourceDirectory, string $destinationDirectory): void
    {
        (new Zip($destinationDirectory))
            ->addFilesFromDirectory($sourceDirectory)
            ->closeZip();
    }

    public static function extract(string $source, string $destination)
    {
        // Extract a Zip file from a directory...
    }
}
