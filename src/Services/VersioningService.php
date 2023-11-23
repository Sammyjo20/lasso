<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Services;

use Exception;
use Sammyjo20\Lasso\Helpers\Cloud;
use Illuminate\Support\Facades\Storage;
use Sammyjo20\Lasso\Exceptions\VersioningFailed;

/**
 * @internal
 */
final class VersioningService
{
    /**
     * Append a new version to the history
     *
     * @throws \Sammyjo20\Lasso\Exceptions\BaseException
     */
    public static function appendNewVersion(string $bundleUrl): void
    {
        // 1. We need to fetch the current history
        $history = self::getHistoryFromDisk();

        // 2. We then need to append the new version to the history, keyed with a unix timestamp
        if (! in_array($bundleUrl, $history, true)) {
            $history[time()] = $bundleUrl;
        }

        // 3. Then we need to delete any old bundles.
        $history = self::deleteExpiredBundles($history);

        // 4. Update the history.json, if there's not been any changes.
        self::updateHistory($history);
    }

    /**
     * Get the versioning file from the Filesystem Disk.
     * This is a file Lasso stores to keep track of its versions.
     *
     * @return array<mixed, mixed>
     * @throws \Sammyjo20\Lasso\Exceptions\BaseException
     */
    private static function getHistoryFromDisk(): array
    {
        $disk = self::getDisk();
        $path = self::getFileDirectory();

        // If there is no history to be found in the Filesystem,
        // that's completely fine. Let's just return an empty
        // array, ready to be populated!

        if (! Storage::disk($disk)->exists($path)) {
            return [];
        }

        // If the history file does exist, let's try and
        // retrieve it and convert the JSON file back into
        // an array.

        try {
            $history_file = Storage::disk($disk)->get($path);
            $history = json_decode($history_file, true) ?? [];

            ksort($history);

            return $history;
        } catch (Exception $ex) {
            throw VersioningFailed::because(
                'Lasso could not retrieve the history.json file from the Filesystem.'
            );
        }
    }

    /**
     * Delete expired bundles
     *
     * @param array<mixed, mixed> $bundles
     * @return array<mixed, mixed>
     */
    private static function deleteExpiredBundles(array $bundles): array
    {
        $bundleLimit = config('lasso.storage.max_bundles');

        // If we haven't exceeded our bundle Limit, let's just return the bundles.
        // There's nothing more we can do here.

        if (count($bundles) <= $bundleLimit) {
            return $bundles;
        }

        // However, if there's a bundle to be removed we need to go Ghostbuster on that bundle.

        $deletable_count = count($bundles) - $bundleLimit;
        $deletable = array_slice($bundles, 0, $deletable_count, true);

        // Now let's delete those bundles!

        $deleted = self::deleteBundles(array_values($deletable));

        // Finally, we want to return a new array, with the bundles that have been deleted removed.

        return array_diff($bundles, $deleted);
    }

    /**
     * Delete bundles
     *
     * @param array<mixed, mixed> $deletable
     * @return array<mixed, mixed>
     */
    private static function deleteBundles(array $deletable): array
    {
        $disk = self::getDisk();
        $deleted = [];

        // Attempt to delete the bundle. If something goes wrong, Lasso isn't precious about it.
        // We will simply try to delete the next directory and move on.

        foreach ($deletable as $bundle_key => $bundle) {
            try {
                $success = Storage::disk($disk)->delete($bundle);

                if ($success) {
                    $deleted[$bundle_key] = $bundle;
                }
            } catch (Exception) {
                continue;
            }
        }

        return $deleted;
    }

    /**
     * Update history file
     *
     * @param array<mixed, mixed> $history
     * @throws \Sammyjo20\Lasso\Exceptions\BaseException
     */
    private static function updateHistory(array $history): void
    {
        try {
            Storage::disk(self::getDisk())->put(self::getFileDirectory(), json_encode($history, JSON_THROW_ON_ERROR));
        } catch (Exception) {
            throw VersioningFailed::because(
                'Lasso could not update the history.json on the Filesystem.'
            );
        }
    }

    /**
     * Get the file directory
     */
    private static function getFileDirectory(): string
    {
        return (new Cloud)->getUploadPath('history.json');
    }

    /**
     * Get the disk
     */
    private static function getDisk(): string
    {
        return config('lasso.storage.disk');
    }
}
