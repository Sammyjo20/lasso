<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Tasks\Pull;

use Exception;
use Sammyjo20\Lasso\Helpers\Git;
use Sammyjo20\Lasso\Tasks\BaseJob;
use Sammyjo20\Lasso\Helpers\Webhook;
use Sammyjo20\Lasso\Helpers\FileLister;
use Sammyjo20\Lasso\Services\BackupService;
use Sammyjo20\Lasso\Services\ArchiveService;
use Sammyjo20\Lasso\Exceptions\PullJobFailed;
use Sammyjo20\Lasso\Services\VersioningService;
use Sammyjo20\Lasso\Helpers\BundleIntegrityHelper;

final class PullJob extends BaseJob
{
    /**
     * Backup Service
     */
    protected BackupService $backup;

    /**
     * Should Lasso use the latest commit from Git?
     */
    protected bool $useCommit = false;

    /**
     * Specify commit hash to use
     */
    protected ?string $commitHash = null;

    /**
     * PullJob constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->backup = new BackupService($this->filesystem);
    }

    /**
     * Run the "pull" job
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Sammyjo20\Lasso\Exceptions\BaseException
     */
    public function run(): void
    {
        $this->cleanLassoDirectory();

        $this->artisan->note('⏳ Reading Bundle meta file...');

        $bundleInfo = $this->getLatestBundleInfo();

        $this->artisan->note('⏳ Downloading bundle...');

        $bundleZipPath = $this->downloadBundleZip($bundleInfo['file'], $bundleInfo['checksum']);

        $this->artisan->note('✅ Successfully downloaded bundle.')
            ->note('⏳ Creating backup...');

        try {
            $this->runBackup();

            $this->artisan->note('✅ Backed up.')
                ->note('⏳ Updating assets...');

            $publicPath = $this->filesystem->getPublicPath();

            ArchiveService::extract($bundleZipPath, base_path('.lasso/bundle'));

            $files = (new FileLister(base_path('.lasso/bundle')))
                ->getFinder();

            foreach ($files as $file) {
                $source = $file->getRealPath();

                $destination = sprintf('%s/%s', $publicPath, $file->getRelativePathName());
                $directory = sprintf('%s/%s', $publicPath, $file->getRelativePath());

                $this->filesystem
                    ->ensureDirectoryExists($directory);

                $this->filesystem
                    ->copy($source, $destination);
            }
        } catch (Exception $ex) {
            // If anything goes wrong inside this try block,
            // we will "roll back" which means we will restore
            // our backup.

            $this->rollBack($ex);
        }

        VersioningService::appendNewVersion(
            $this->cloud->getUploadPath($bundleInfo['file'])
        );

        $this->cleanUp();

        $webhooks = config('lasso.webhooks.pull', []);

        $this->dispatchWebhooks($webhooks);
    }

    /**
     * Cleanup the command
     */
    public function cleanUp(): void
    {
        $this->filesystem->deleteBaseLassoDirectory();
    }

    /**
     * Dispatch the webhooks
     *
     * @param array<int, string> $webhooks
     */
    public function dispatchWebhooks(array $webhooks = []): void
    {
        if (! count($webhooks)) {
            return;
        }

        $this->artisan->note('⏳ Dispatching webhooks...');

        foreach ($webhooks as $webhook) {
            Webhook::send($webhook, 'pull');
        }

        $this->artisan->note('✅ Webhooks dispatched.');
    }

    /**
     * Get the latest bundle info
     *
     * @return array<mixed, mixed>
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getLatestBundleInfo(): array
    {
        $localPath = base_path('lasso-bundle.json');
        $cloudPath = $this->cloud->getUploadPath(config('lasso.storage.prefix') . 'lasso-bundle.json');

        // Firstly, let's check if the local filesystem has a "lasso-bundle.json" file in its root directory.

        if ($this->filesystem->exists($localPath)) {
            $file = $this->filesystem->get($localPath);
            $bundle = json_decode($file, true);

            $this->validateBundle($bundle);

            return $bundle;
        }

        // If there isn't a "lasso-bundle.json" file in the root directory, that's okay - this means
        // that the commit is in "non-git" mode. So let's just grab that file. If we don't have a
        // file on the server however; we need to throw an exception.

        if (! $this->cloud->exists($cloudPath)) {
            $this->rollBack(
                PullJobFailed::because('A valid "lasso-bundle.json" file could not be found in the Filesystem for the current environment.')
            );
        }

        $file = $this->cloud->get($cloudPath);
        $bundle = json_decode($file, true);

        $this->validateBundle($bundle);

        return $bundle;
    }

    /**
     * Validate the bundle checksum
     *
     * @param array<mixed, mixed> $bundle
     * @throws \Exception
     */
    private function validateBundle(array $bundle): bool
    {
        if (! isset($bundle['file'], $bundle['checksum'])) {
            $this->rollBack(
                PullJobFailed::because('The bundle info was missing the required data.')
            );
        }

        return true;
    }

    /**
     * Download Zip bundle file
     */
    private function downloadBundleZip(string $file, string $checksum): string
    {
        $bundlePath = $this->getBundlePath($file);
        $localBundlePath = base_path('.lasso/bundle.zip');

        if (! $this->cloud->exists($bundlePath)) {
            $this->rollBack(
                PullJobFailed::because('The bundle zip does not exist. If you are using a specific environment, please make sure the LASSO_ENV is the same in your .env file.')
            );
        }

        try {
            $bundleZip = $this->cloud
                ->readStream($bundlePath);

            $this->filesystem
                ->putStream($bundleZip, $localBundlePath);

            if (is_resource($bundleZip)) {
                fclose($bundleZip);
            }
        } catch (Exception $ex) {
            $this->rollBack($ex);
        }

        // Now we want to check if the integrity of the bundle is okay.
        // If the integrity is incorrect, it could have been downloaded
        // incorrectly or tampered with!

        if (! BundleIntegrityHelper::verifyChecksum($localBundlePath, $checksum)) {
            if ($this->useCommit || $this->commitHash) {
                $this->artisan->note('Could not verify checksum using commit hash for bundle id ...');

                return $localBundlePath;
            }

            $this->rollBack(
                PullJobFailed::because('The bundle Zip\'s checksum is incorrect.')
            );
        }

        return $localBundlePath;
    }

    /**
     * Roll back and throw an exception
     *
     * @throws \Exception
     */
    private function rollBack(Exception $exception): Exception
    {
        if ($this->backup->hasBackup()) {
            $this->artisan->note('⏳ Restoring backup...');

            $this->backup->restoreBackup($this->filesystem->getPublicPath());

            $this->artisan->note('✅ Successfully restored backup.');
        }

        $this->filesystem->deleteBaseLassoDirectory();

        throw $exception;
    }

    /**
     * Backup the source data
     */
    private function runBackup(): void
    {
        $this->backup->createBackup($this->filesystem->getPublicPath(), base_path('.lasso/backup'));
    }

    /**
     * Cleanup the Lasso directory
     */
    private function cleanLassoDirectory(): void
    {
        $this->filesystem->deleteBaseLassoDirectory();

        $this->filesystem->ensureDirectoryExists(base_path('.lasso'));
    }

    /**
     * Get the Lasso bundle path
     */
    private function getBundlePath(string $file): string
    {
        if ($this->commitHash) {
            return $this->cloud->getUploadPath($this->commitHash . '.zip');
        }

        if ($this->useCommit) {
            return $this->cloud->getUploadPath(Git::getCommitHash() . '.zip');
        }

        return $this->cloud->getUploadPath($file);
    }

    /**
     * Should Lasso use the latest commit from Git?
     */
    public function useCommit(): self
    {
        $this->useCommit = true;

        return $this;
    }

    /**
     * Specify a custom commit hash
     */
    public function withCommit(string $commitHash): self
    {
        $this->commitHash = $commitHash;

        return $this;
    }
}
