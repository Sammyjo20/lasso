<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Tasks\Publish;

use Exception;
use Illuminate\Support\Str;
use Sammyjo20\Lasso\Helpers\Git;
use Sammyjo20\Lasso\Tasks\BaseJob;
use Sammyjo20\Lasso\Helpers\Bundle;
use Sammyjo20\Lasso\Helpers\Webhook;
use Sammyjo20\Lasso\Helpers\Compiler;
use Sammyjo20\Lasso\Exceptions\GitHashException;

final class PublishJob extends BaseJob
{
    /**
     * Bundle ID
     */
    protected string $bundleId;

    /**
     * Should Lasso use Git?
     */
    protected bool $usesGit = true;

    /**
     * Should Lasso use the latest commit?
     */
    protected bool $useCommit = false;

    /**
     * The latest commit
     */
    protected ?string $commit = null;

    /**
     * PublishJob constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->deleteLassoDirectory();
    }

    /**
     * Run the "publish" job
     */
    public function run(): void
    {
        $artisan = $this->artisan;
        $filesystem = $this->filesystem;
        $cloud = $this->cloud;

        try {
            $this->generateBundleId();

            $artisan->note('⏳ Compiling assets...');

            // Start with the compiler. This will run the "script" which
            // has been defined in the config file (e.g. npm run production).

            $compiler = (new Compiler)
                ->setCommand(config('lasso.compiler.script'))
                ->setTimeout(config('lasso.compiler.timeout', 600))
                ->execute();

            $artisan->note(sprintf(
                '✅ Compiled assets in %s seconds.',
                $compiler->getCompilationTime()
            ));

            $artisan->note('⏳ Copying and zipping compiled assets...');

            // Once we have compiled all of our assets, we need to "bundle"
            // them up.

            (new BundleJob)
                ->setBundleId($this->bundleId)
                ->run();

            $zipBundlePath = base_path('.lasso/dist/' . $this->bundleId . '.zip');

            $artisan->note('✅ Successfully copied and zipped assets.');

            // Now we want to create the data which will go inside the
            // "lasso-bundle.json" file. After that, we will create a Zip file
            // with all the assets inside.

            $bundle = (new Bundle)
                ->setBundleId($this->bundleId)
                ->setZipPath($zipBundlePath)
                ->create();

            $artisan->note(
                sprintf('⏳ Uploading asset bundle to "%s" filesystem...', $filesystem->getCloudDisk())
            );

            $bundlePath = base_path('.lasso/dist/lasso-bundle.json');

            // Now it's time to upload our Bundle to the filesystem.
            // this uses stream to ensure we don't run out of memory
            // while uploading the file.

            $cloud->uploadFile($zipBundlePath, $bundle['file']);

            // Has the user requested to use/not use Git? Here
            // we will create the lasso-bundle.json in the right
            // place.

            if ($this->usesGit) {
                $filesystem->createFreshLocalBundle($bundle);
            } else {
                $filesystem->deleteLocalBundle();

                $filesystem->put($bundlePath, (string)json_encode($bundle));

                $cloud->uploadFile($bundlePath, config('lasso.storage.prefix') . 'lasso-bundle.json');
            }

            // Done! Let's run some cleanup, and dispatch all the
            // Webhook URLs defined in the "publish" array.

            $this->cleanUp();
            $webhooks = config('lasso.webhooks.publish', []);
            $this->dispatchWebhooks($webhooks);
        } catch (Exception $ex) {
            $this->rollBack($ex);
        }
    }


    /**
     * Clean up the publish
     */
    public function cleanUp(): void
    {
        $this->deleteLassoDirectory();
    }

    /**
     * Roll back the publish
     *
     * @throws Exception
     */
    private function rollBack(Exception $exception): void
    {
        $this->deleteLassoDirectory();

        throw $exception;
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
            Webhook::send($webhook, 'publish');
        }

        $this->artisan->note('✅ Webhooks dispatched.');
    }

    /**
     * Generate the Bundle ID
     *
     * @throws GitHashException
     */
    private function generateBundleId(): void
    {
        $id = Str::random(20);

        if ($this->useCommit) {
            $id = Git::getCommitHash();
        }

        if ($this->commit) {
            $id = $this->commit;
        }

        $this->bundleId = $id;
    }

    /**
     * Delete the Lasso directory
     */
    private function deleteLassoDirectory(): void
    {
        $this->filesystem->deleteBaseLassoDirectory();
    }

    /**
     * Disable Git with Lasso
     *
     * @return $this
     */
    public function dontUseGit(): self
    {
        $this->usesGit = false;

        return $this;
    }

    /**
     * Should Lasso use the latest commit from Git?
     *
     * @return $this
     */
    public function useCommit(): self
    {
        $this->useCommit = true;

        return $this;
    }

    /**
     * Specify a commit that is used for the bundle
     *
     * @return $this
     */
    public function withCommit(string $commitHash): self
    {
        $this->commit = $commitHash;

        return $this;
    }
}
