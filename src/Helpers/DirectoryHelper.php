<?php

namespace Sammyjo20\Lasso\Helpers;

final class DirectoryHelper
{
    /**
     * Returns the Lasso upload directory. You can specify a file
     * to create a fully qualified URL.
     *
     * @param string|null $file
     * @param string|null $environment
     * @return string
     */
    public static function getFileDirectory(string $file = null, string $environment = null): string
    {
        // Set the environment. If it's been set by the Console, we want to prefer
        // this, but if no command has been set, use the default.

        if (is_null($environment)) {
            $environment = config('lasso.storage.environment', null) ?? 'global';
        }

        $dir = sprintf(
            '%s/%s',
            config('lasso.storage.upload_to'),
            $environment,
        );

        if (is_null($file)) {
            return $dir;
        }

        return $dir . '/' . ltrim($file, '/');
    }
}
