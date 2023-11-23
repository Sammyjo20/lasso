<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Helpers\Zip;
use Sammyjo20\Lasso\Helpers\Unzipper;

final class ArchiveService
{
    /**
     * Create a Zip File
     */
    public static function create(string $sourceDirectory, string $destinationDirectory): void
    {
        (new Zip($destinationDirectory))
            ->addFilesFromDirectory($sourceDirectory)
            ->closeZip();
    }

    /**
     * Extract a Zip File
     */
    public static function extract(string $source, string $destination): void
    {
        (new Unzipper($source, $destination))->run();
    }
}
