<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Sammyjo20\Lasso\Exceptions\BundleHistoryException;

class BundleHistory
{
    /**
     * @var Filesystem
     */
    public $local;

    /**
     * @var string
     */
    public $cloud_disk;

    /**
     * @var string
     */
    public $history_path;

    /**
     * BundleHistory constructor.
     */
    public function __construct()
    {
        $this->local = new Filesystem();
        $this->cloud_disk = config('lasso.storage.disk');

        $base_directory = rtrim(config('lasso.storage.upload_to'), '/');
        $this->history_path = $base_directory . '/' . config('lasso.storage.environment') . '/history.json';
    }

    /**
     * @return array
     * @throws BundleHistoryException
     */
    public function getHistory(): array
    {
        $disk = $this->cloud_disk;
        $path = $this->history_path;

        if (!Storage::disk($disk)->exists($path)) {
            return [];
        }

        try {
            $file = Storage::disk($disk)->get($path);
            return (array)json_decode($file);
        } catch (\Exception $ex) {
            throw new BundleHistoryException('Could not read history.json file!');
        }
    }

    /**
     * @param string $bundle_id
     * @return bool
     * @throws BundleHistoryException
     */
    public function appendToHistory(string $bundle_id): bool
    {
        $disk = $this->cloud_disk;
        $path = $this->history_path;

        $history = $this->getHistory();

        // Todo: Refactor this by retrieving the full path from an argument.
        $base_directory = rtrim(config('lasso.storage.upload_to'), '/');
        $bundle_path = $base_directory . '/' . config('lasso.storage.environment') . '/' . $bundle_id . '.zip';

        try {
            $history[] = $bundle_path;
            return Storage::disk($disk)->put($path, json_encode($history));
        } catch (\Exception $ex) {
            throw new BundleHistoryException('Failed to update the history.json file!');
        }
    }

    /**
     * @return bool
     * @throws BundleHistoryException
     */
    public function cleanFromHistory(): bool
    {
        $disk = $this->cloud_disk;
        $path = $this->history_path;

        $history = $this->getHistory();
        $bundles_to_keep = config('lasso.storage.bundles_to_keep');

        if (count($history) > $bundles_to_keep) {
            $delete = array_slice($history, 0, $bundles_to_keep);

            foreach ($delete as $bundle) {
                // Attempt to delete the bundle.
                Storage::disk($disk)->delete($bundle);
            }

            $keep = array_slice($history, $bundles_to_keep);

            try {
                return Storage::disk($disk)->put($path, json_encode($keep));
            } catch (\Exception $ex) {
                throw new BundleHistoryException('Failed to update the history.json file!');
            }
        }

        return false;
    }
}
