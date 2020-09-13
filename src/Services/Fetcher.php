<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Exceptions\FetchCommandFailed;
use Sammyjo20\Lasso\Factories\ZipExtractor;
use Sammyjo20\Lasso\Helpers\BundleIntegrityHelper;
use Sammyjo20\Lasso\Helpers\DirectoryHelper;
use Sammyjo20\Lasso\Helpers\FileLister;

final class Fetcher
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
     * @var
     */
    protected $use_git;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var Console
     */
    protected $console;

    /**
     * Fetcher constructor.
     */
    public function __construct()
    {
        $this->local_filesystem = new Filesystem();
        $this->lasso_disk = config('lasso.storage.disk');
        $this->environment = config('lasso.storage.environment') ?? 'global';
        $this->console = resolve(Console::class);

        $this->lasso_path = DirectoryHelper::getFileDirectory();

        $this->backup_service = new BackupService($this->local_filesystem, base_path('.lasso/backup'));
        $this->use_git = false;
    }

    public function execute()
    {
        // Delete the old .lasso directory locally, if it exists
        // and create a new one.

        $this->cleanLassoDirectory();

        $this->console->info('⏳ Reading Bundle meta file...');

        $bundle_info = $this->retrieveLatestBundleMeta();

        if (!isset($bundle_info['file']) || !isset($bundle_info['checksum'])) {
            $this->rollBack(
                FetchCommandFailed::because('The bundle info was missing the required data.')
            );
        }

        $this->console->info('⏳ Downloading bundle...');

        // Grab the Zip.
        $bundle = $this->retrieveBundle($bundle_info['file'], $bundle_info['checksum']);

        $this->console->info('✅ Successfully downloaded bundle.');

        // Now it's time to roll. We should make a backup, so in case
        // anything goes wrong - we can roll back easily.

        $this->console->info('⏳ Updating assets...');

        try {
            if ($this->backup_service->startBackup()) {
                $public_path = rtrim(config('lasso.public_path'), '/');

                // Now it's time to unzip!
                (new ZipExtractor($bundle))
                    ->extractTo(base_path('.lasso/bundle'));

                $files = (new FileLister(base_path('.lasso/bundle')))
                    ->getFinder();

                foreach ($files as $file) {
                    $source = $file->getRealPath();
                    $destination = sprintf('%s/%s', $public_path, $file->getRelativePathName());
                    $directory = sprintf('%s/%s', $public_path, $file->getRelativePath());

                    $this->local_filesystem
                        ->ensureDirectoryExists($directory);

                    $this->local_filesystem
                        ->copy($source, $destination);
                }
            }
        } catch (\Exception $ex) {
            // If anything goes wrong inside this try block,
            // we will "roll back" which means we will restore
            // our backup.

            $this->rollBack($ex);
        }

        $bundle_path = DirectoryHelper::getFileDirectory($bundle_info['file']);
        VersioningService::appendNewVersion($bundle_path);

        // If it's all successful, it's time to clean everything up.
        $this->deleteLassoDirectory();

        // Done. Send webhooks
        $this->sendWebhooks();
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
            $this->use_git = true;
            $file = $this->local_filesystem->get(base_path('lasso-bundle.json'));
            return json_decode($file, true);
        }

        // If there isn't a "lasso-bundle.json" file in the root directory,
        // that's okay - this means that the commit is in "non-git" mode. So
        // let's just grab that file.If we don't have a file on the server
        // however; we need to throw an exception.

        if (!$filesystem->has($base_path . '/lasso-bundle.json')) {
            $this->rollBack(
                FetchCommandFailed::because('A valid "lasso-bundle.json" file could not be found for the current environment.')
            );
        }

        $file = $filesystem->get($base_path . '/lasso-bundle.json');
        return json_decode($file, true);
    }

    /**
     * @param string $file
     * @param string $checksum
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Sammyjo20\Lasso\Exceptions\BaseException
     */
    private function retrieveBundle(string $file, string $checksum): string
    {
        $local_path = base_path('.lasso') . '/bundle.zip';
        $filesystem_path = DirectoryHelper::getFileDirectory($file);

        if (!Storage::disk($this->lasso_disk)->exists($filesystem_path)) {
            $this->rollBack(
                FetchCommandFailed::because('The bundle zip does not exist. If you are using a specific environment, please make sure the LASSO_ENV is the same in your .env file.')
            );
        }

        $zip = Storage::disk($this->lasso_disk)
            ->get($filesystem_path);

        if (!$zip) {
            $this->rollBack(
                FetchCommandFailed::because('The bundle Zip could not be found or was inaccessible. If you are using a specific environment, please make sure the LASSO_ENV is the same in your .env file.')
            );
        }

        try {
            $this->local_filesystem->put($local_path, $zip);
        } catch (\Exception $ex) {
            $this->rollBack(
                FetchCommandFailed::because('An error occurred while writing to the local path.')
            );
        }

        // Now we want to check if the integrity of the bundle is okay.
        // If the integrity is incorrect, it could have been downloaded
        // incorrectly or tampered with!

        if (!BundleIntegrityHelper::verifyChecksum($local_path, $checksum)) {
            $this->rollBack(
                FetchCommandFailed::because('The bundle Zip\'s checksum is incorrect.')
            );
        }

        return $local_path;
    }

    /**
     * @param array $data
     */
    private function sendWebhooks(array $data = [])
    {
        $this->console->info('⏳ Dispatching Webhooks...');

        $webhooks = config('lasso.webhooks.pull', []);

        foreach($webhooks as $webhook) {
            Webhook::send($webhook, Webhook::PULL_EVENT, $data);
        }

        $this->console->info('✅ Webhooks dispatched.');
    }
}
