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
     * @var bool
     */
    protected $pushToGit = false;

    /**
     * PublishJob constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->generateBundleId()
            ->deleteLassoDirectory();

        if (config('lasso.storage.push_to_git', false) === true) {
            $this->shouldPushToGit();
        }
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->artisan->note('⏳ Compiling assets...');

        // Start with the compiler. This will run the "script" which
        // has been defined in the config file (e.g npm run production).

        (new Command())
            ->setScript(config('lasso.compiler.script'))
            ->setTimeout(config('lasso.compiler.timeout'))
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

        // Has the user enabled automatic pushing to git?
        // If they have, commit that file now.

        if ($this->pushToGit) {
            (new Command())
                ->setScript('git add "lasso-bundle.json" || git commit -m"Lasso Assets 🐎" --author="Lasso <>" || git push')
                ->setTimeout(60)
                ->run();
        }

        // Done! Let's run some cleanup, and dispatch all of the
        // Webhook URLs defined in the "publish" array.

        $this->cleanUp();

        $this->artisan->note('✅ Successfully published assets.')
            ->note('⏳ Dispatching webhooks...');

        $this->dispatchWebhooks();

        $this->artisan->note('✅ Webhooks dispatched.');
    }

    /**
     * @return void
     */
    public function cleanUp(): void
    {
        $this->deleteLassoDirectory();
    }

    /**
     * @return void
     */
    public function dispatchWebhooks(): void
    {
        $webhooks = config('lasso.webhooks.publish', []);

        foreach ($webhooks as $webhook) {
            Webhook::send($webhook, 'publish');
        }
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

    /**
     * @return $this
     */
    public function shouldPushToGit(): self
    {
        $this->pushToGit = true;

        return $this;
    }
}
