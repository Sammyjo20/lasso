<?php

namespace Sammyjo20\Lasso\Helpers;

final class DirectoryHelper
{
    /**
     * Returns the Lasso upload directory. You can specify a file
     * to create a fully qualified URL.
     *
     * @param string|null $file
     * @return string
     */
    public static function getFileDirectory(string $file = null): string
    {
        $dir = sprintf(
            '%s/%s',
            config('lasso.storage.upload_to'),
            config('lasso.storage.environment', null) ?? 'global',
        );

        if (is_null($file)) {
            return $dir;
        }

        return $dir . '/' . ltrim($file, '/');
    }
}
