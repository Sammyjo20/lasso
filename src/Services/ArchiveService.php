<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Helpers\Zip;
use Sammyjo20\Lasso\Helpers\Unzipper;

final class ArchiveService
{
    
    public static function create(string $sourceDirectory, string $destinationDirectory): void
    {
        (new Zip($destinationDirectory))
            ->addFilesFromDirectory($sourceDirectory)
            ->closeZip();
    }

    
    public static function extract(string $source, string $destination)
    {
        (new Unzipper($source, $destination))
            ->run();
    }
}
