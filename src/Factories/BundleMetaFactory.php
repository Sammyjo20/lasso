<?php

namespace Sammyjo20\Lasso\Factories;

use Sammyjo20\Lasso\Helpers\BundleIntegrityHelper;
use Sammyjo20\Lasso\Helpers\DirectoryHelper;

class BundleMetaFactory
{
    /**
     * @param string $bundle_id
     * @param string $bundle_path
     * @return false|string
     */
    public static function create(string $bundle_id, string $bundle_path)
    {
        // Now we will generate a checksum for the file. This is useful
        // to do, just in case something goes wrong in uploading the file
        // and also to check against the download later on.

        $checksum = BundleIntegrityHelper::generateChecksum($bundle_path);
        $path = DirectoryHelper::getFileDirectory($bundle_id . '.zip');

        return json_encode(['path' => $path, 'checksum' => $checksum]);
    }
}
