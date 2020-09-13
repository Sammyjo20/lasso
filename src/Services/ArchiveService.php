<?php

namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Helpers\Unzipper;
use Sammyjo20\Lasso\Helpers\Zip;

final class ArchiveService
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

    /**
     * @param string $source
     * @param string $destination
     */
    public static function extract(string $source, string $destination)
    {
        (new Unzipper($source, $destination))
            ->run();
    }
}
