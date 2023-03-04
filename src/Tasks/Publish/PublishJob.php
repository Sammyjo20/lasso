<?php

namespace Sammyjo20\Lasso\Tasks\Publish;

use Exception;
use Illuminate\Support\Str;
use Sammyjo20\Lasso\Helpers\Git;
use Sammyjo20\Lasso\Tasks\BaseJob;
use Sammyjo20\Lasso\Tasks\Webhook;
use Sammyjo20\Lasso\Helpers\Bundle;
use Sammyjo20\Lasso\Actions\Compiler;
use Sammyjo20\Lasso\Exceptions\GitHashException;

final class PublishJob extends BaseJob
{
    /**
     * @var string
     */
    protected $bundleId;

    /**
     * @var bool
     */
    protected $usesGit = true;

    /**
     * @var bool
     */
    protected $useCommit = false;

    /**
     * @var string?
     */
    protected $commit = null;

    /**
     * PublishJob constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->deleteLassoDirectory();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function run(): void
    {
        try {
            $this->generateBundleId();

            $this->artisan->note('⏳ Compiling assets...');

            // Start with the compiler. This will run the "script" which
            // has been defined in the config file (e.g. npm run production).

            $compiler = (new Compiler())
                ->setCommand(config('lasso.compiler.script'))
                ->setTimeout(config('lasso.compiler.timeout', 600))
                ->execute();

            $this->artisan->note(sprintf(
                '✅ Compiled assets in %s seconds.',
                $compiler->getCompilationTime()
            ));

            $this->artisan->note('⏳ Copying and zipping compiled assets...');

            // Once we have compiled all of our assets, we need to "bundle"
            // them up. Todo: Remove this step in the future.

            (new BundleJob())
                ->setBundleId($this->bundleId)
                ->run();

            $zipBundlePath = base_path('.lasso/dist/' . $this->bundleId . '.zip');

            $this->artisan->note('✅ Successfully copied and zipped assets.');

            // Now we want to create the data which will go inside the
            // "lasso-bundle.json" file. After that, we will create a Zip file
            // with all the assets inside.

            $bundle = (new Bundle())
                ->setBundleId($this->bundleId)
                ->setZipPath($zipBundlePath)
                ->create();

            $this->artisan->note(
                sprintf('⏳ Uploading asset bundle to "%s" filesystem...', $this->filesystem->getCloudDisk())
            );

            $bundlePath = base_path('.lasso/dist/lasso-bundle.json');

            // Now it's time to upload our Bundle to the filesystem.
            // this uses stream to ensure we don't run out of memory
            // while uploading the file.

            $this->cloud->uploadFile($zipBundlePath, $bundle['file']);

            // Has the user requested to use/not use Git? Here
            // we will create the lasso-bundle.json in the right
            // place.

            if ($this->usesGit) {
                $this->filesystem->createFreshLocalBundle($bundle);
            } else {
                $this->filesystem->deleteLocalBundle();

                $this->filesystem->put($bundlePath, json_encode($bundle));

                $this->cloud->uploadFile($bundlePath, config('lasso.storage.prefix') . 'lasso-bundle.json');
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
     * @return void
     */
    public function cleanUp(): void
    {
        $this->deleteLassoDirectory();
    }

    /**
     * @param Exception $exception
     * @throws Exception
     */
    private function rollBack(Exception $exception)
    {
        $this->deleteLassoDirectory();

        throw $exception;
    }

    /**
     * @param array $webhooks
     */
    public function dispatchWebhooks(array $webhooks = []): void
    {
        if (! count($webhooks)) {
            return;
        }

        $this->artisan->note('⏳ Dispatching webhooks...');

        foreach ($this->filterWebhooksToRun($webhooks) as $webhook) {
            Webhook::send($webhook, 'publish');
        }

        $this->artisan->note('✅ Webhooks dispatched.');
    }

    /**
     * @return $this
     * @throws GitHashException
     */
    private function generateBundleId(): self
    {
        $id = Str::random(20);

        if ($this->useCommit) {
            $id = Git::getCommitHash();
        }

        if ($this->commit) {
            $id = $this->commit;
        }

        $this->bundleId = $id;

        return $this;
    }

    /**
     * @return $this
     */
    private function deleteLassoDirectory(): self
    {
        $this->filesystem->deleteBaseLassoDirectory();

        return $this;
    }

    /**
     * @return $this
     */
    public function dontUseGit(): self
    {
        $this->usesGit = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function useCommit(): self
    {
        $this->useCommit = true;

        return $this;
    }

    public function withCommit(string $commitHash): self
    {
        $this->commit = $commitHash;

        return $this;
    }
}
