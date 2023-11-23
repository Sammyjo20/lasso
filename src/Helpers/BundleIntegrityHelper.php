<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

class BundleIntegrityHelper
{
    public const ALGORITHM = 'md5';

    
    public static function generateChecksum(string $path): string
    {
        return hash_file(self::ALGORITHM, $path);
    }

    
    public static function verifyChecksum(string $path, string $checksum): bool
    {
        return self::generateChecksum($path) === $checksum;
    }
}
