<?php

namespace Sammyjo20\Lasso\Helpers;

class BundleIntegrityHelper
{
    public const ALGORITHM = 'md5';

    /**
     * @param string $path
     * @return string
     */
    public static function generateChecksum(string $path): string
    {
        return hash_file(self::ALGORITHM, $path);
    }

    /**
     * @param string $path
     * @param string $checksum
     * @return bool
     */
    public static function verifyChecksum(string $path, string $checksum): bool
    {
        return self::generateChecksum($path) === $checksum;
    }
}
