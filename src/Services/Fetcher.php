<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Sammyjo20\Lasso\Exceptions\FetchCommandFailed;
use Sammyjo20\Lasso\Factories\ZipExtractor;
use Sammyjo20\Lasso\Helpers\BundleIntegrityHelper;
use Sammyjo20\Lasso\Helpers\FileLister;

class Fetcher
{
    /**
     * @var Filesystem
     */
    protected $local_filesystem;

    /**
     * @var string
     */
    protected $lasso_disk;

    /**
     * @var string
     */
    protected $lasso_path;

    /**
     * @var
     */
    protected $backup_service;

    /**
     * Fetcher constructor.
     */
    public function __construct()
    {
        $this->local_filesystem = new Filesystem();
        $this->lasso_disk = config('lasso.storage.disk');

        $this->lasso_path = sprintf(
            '%s/%s',
            rtrim(config('lasso.storage.upload_to'), '/'),
            config('lasso.storage.environment')
        );

        $this->backup_service = new Backup($this->local_filesystem, base_path('.lasso/backup'));
    }

    /**
     * @return void
     */
    private function deleteLassoDirectory(): void
    {
        $this->local_filesystem->deleteDirectory(base_path('.lasso'));
    }

    /**
     * @return void
     */
    private function cleanLassoDirectory(): void
    {
        $this->deleteLassoDirectory();
        $this->local_filesystem->ensureDirectoryExists(base_path('.lasso'));
    }

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    private function rollBack(\Exception $exception)
    {
        // Run when something really bad happens.

        if ($this->backup_service->getStatus() === 'success') {
            $this->backup_service->restoreBackup();
        }

        $this->deleteLassoDirectory();

        throw $exception;
    }

    /**
     * Grab the latest bundle meta and if we have one,
     * decode the data and give it to us as an array.
     *
     * @return array
     * @throws FetchCommandFailed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function retrieveLatestBundleMeta(): array
    {
        $filesystem = Storage::disk($this->lasso_disk);
        $base_path = $this->lasso_path;

        // Firstly, let's check if the local filesystem has a "lasso-bundle.json"
        // file in it's root directory.

        if ($this->local_filesystem->exists(base_path('lasso-bundle.json'))) {
            $file = $this->local_filesystem->get(base_path('lasso-bundle.json'));
            return (array)json_decode($file, true);
        }

        // Secondly, let's check if the filesystem has a "bundle-meta.next.json"
        // file in it's directory. If we have this, we will want to prefer this,
        // as Lasso has created this for our next deployment.

        if ($filesystem->has($base_path . '/bundle-meta.next.json')) {
            $file = $filesystem->get($base_path . '/bundle-meta.next.json');
            return (array)json_decode($file, true);
        }

        // If there isn't a "bundle-meta.next.json" file, that's okay - we probably
        // have a "bundle-meta.json" file from a previous deployment. Let's just grab that.
        // If we don't however. We need to throw an exception.

        if (!$filesystem->has($base_path . '/bundle-meta.json')) {
            throw FetchCommandFailed::because('A valid "bundle-meta.json" file could not be found for the current environment.');
        }

        $file = $filesystem->get($base_path . '/bundle-meta.json');
        return (array)json_decode($file, true);
    }

    /**
     * @param string $id
     * @param string $checksum
     * @return string
     * @throws FetchCommandFailed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function retrieveBundle(string $id, string $checksum): string
    {
        $filesystem_path = sprintf('%s/%s.zip', $this->lasso_path, $id);
        $local_path = base_path('.lasso') . '/bundle.zip';

        $zip = Storage::disk($this->lasso_disk)
            ->get($filesystem_path);

        if (!$zip) {
            throw FetchCommandFailed::because('The bundle Zip could not be found or was inaccessible.');
        }

        try {
            $this->local_filesystem->put($local_path, $zip);
        } catch (\Exception $ex) {
            throw FetchCommandFailed::because('An error occurred while writing to the local path.');
        }

        // Now we want to check if the integrity of the bundle is okay.
        // If the integrity is incorrect, it could have been downloaded
        // incorrectly or tampered with!

        if (!BundleIntegrityHelper::verifyChecksum($local_path, $checksum)) {
            throw FetchCommandFailed::because('The bundle Zip\'s checksum is incorrect.');
        }

        return $local_path;
    }

    public function execute()
    {
        // Delete the old .lasso directory locally, if it exists
        // and create a new one.

        $this->cleanLassoDirectory();

        $bundle_info = $this->retrieveLatestBundleMeta();

        if (!isset($bundle_info['id']) || !isset($bundle_info['checksum'])) {
            throw FetchCommandFailed::because('The bundle info was missing the required data.');
        }

        // Grab the Zip.
        $bundle = $this->retrieveBundle($bundle_info['id'], $bundle_info['checksum']);

        // Now it's time to roll. We should make a backup, so in case
        // anything goes wrong - we can roll back easily.

        try {
            if ($this->backup_service->startBackup()) {
                $public_path = rtrim(config('lasso.public_path'), '/');

                // Now it's time to unzip!
                (new ZipExtractor($bundle))
                    ->extractTo(base_path('.lasso/bundle'));

                $files = (new FileLister(base_path('.lasso/bundle')))
                    ->getFinder();

                foreach($files as $file) {
                    $relative_path = $file->getRelativePathName();
                    $path = $public_path . '/' . $relative_path;

                    if ($this->local_filesystem->exists($path)) {
                        $this->local_filesystem->delete($path);
                    }

                    $this->local_filesystem->ensureDirectoryExists(
                        $public_path . '/' . $file->getRelativePath()
                    );

                    $this->local_filesystem->put(
                        $path, $file->getContents()
                    );
                }
            }
        } catch (\Exception $ex) {
            // If anything goes wrong inside this try block,
            // we will "roll back" which means we will restore
            // our backup.

            $this->rollBack($ex);
        }

        // If it's all successful, it's time to clean everything up.
        $this->deleteLassoDirectory();

        // Now we should go and delete the old bundles. We will do that tomorrow.
    }
}
