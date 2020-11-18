<?php

namespace Sammyjo20\Lasso\Tasks\Publish;

use Illuminate\Support\Str;
use Sammyjo20\Lasso\Helpers\Bundle;
use Sammyjo20\Lasso\Tasks\BaseJob;
use Sammyjo20\Lasso\Tasks\Command;
use Sammyjo20\Lasso\Tasks\Webhook;

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
     * PublishJob constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->generateBundleId()
            ->deleteLassoDirectory();
    }

    /**
     * @return void
     */
    public function run(): void
    {
        try {
            $this->artisan->note('⏳ Compiling assets...');

            // Start with the compiler. This will run the "script" which
            // has been defined in the config file (e.g npm run production).

            (new Command())
                ->setScript(config('lasso.compiler.script'))
                ->setTimeout(config('lasso.compiler.timeout', 600))
                ->run();

            $this->artisan->note('✅ Compiled assets.')
                ->note('⏳ Copying and zipping compiled assets...');

            // Once we have compiled all of our assets, we need to "bundle"
            // them up. Todo: Remove this step in the future.

            (new BundleJob())
                ->setBundleId($this->bundleId)
                ->run();

            $zipBundlePath = base_path('.lasso/dist/' . $this->bundleId . '.zip');

            $this->artisan->note('✅ Successfully copied and zipped assets.');

            // Now we want to create the data which will go inside of the
            // "lasso-bundle.json" file. After that, we will create a Zip file
            // with all of the assets inside.

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

                $this->cloud->uploadFile($bundlePath, 'lasso-bundle.json');
            }

            // Done! Let's run some cleanup, and dispatch all of the
            // Webhook URLs defined in the "publish" array.

            $this->cleanUp();
            $webhooks = config('lasso.webhooks.publish', []);
            $this->dispatchWebhooks($webhooks);
        } catch (\Exception $ex) {
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
     * @param \Exception $exception
     * @throws \Exception
     */
    private function rollBack(\Exception $exception)
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

        foreach ($webhooks as $webhook) {
            Webhook::send($webhook, 'publish');
        }

        $this->artisan->note('✅ Webhooks dispatched.');
    }

    /**
     * @return $this
     */
    private function generateBundleId(): self
    {
        $this->bundleId = Str::random(20);

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
}
