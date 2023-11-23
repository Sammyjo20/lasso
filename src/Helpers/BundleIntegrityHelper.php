<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

class BundleIntegrityHelper
{
    /**
     * Hashing Algorithm
     */
    public const ALGORITHM = 'md5';

    /**
     * Generate the checksum
     */
    public static function generateChecksum(string $path): string
    {
        return hash_file(self::ALGORITHM, $path);
    }

    /**
     * Verify the checksum
     */
    public static function verifyChecksum(string $path, string $checksum): bool
    {
        return self::generateChecksum($path) === $checksum;
    }
}
