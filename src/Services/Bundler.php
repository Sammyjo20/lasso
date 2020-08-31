<?php

namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Container\Console;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Sammyjo20\Lasso\Factories\BundleMetaFactory;
use Sammyjo20\Lasso\Factories\ZipFactory;
use Sammyjo20\Lasso\Helpers\DirectoryHelper;
use Symfony\Component\Finder\Finder;

final class Bundler
{
    /**
     * @var Compiler
     */
    protected $compiler;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $bundle_id;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var Console
     */
    protected $console;

    /**
     * Bundler constructor.
     * @param string|null $environment
     */
    public function __construct(string $environment = null)
    {
        $this->compiler = new Compiler();
        $this->filesystem = new Filesystem();
        $this->bundle_id = Str::random(20);
        $this->console = resolve(Console::class);

        if (is_null($environment)) {
            $this->environment = config('lasso.storage.environment') ?? 'global';
        } else {
            $this->environment = $environment;
        }

        $this->deleteLassoDirectory();
    }

    /**
     * @return void
     */
    private function deleteLassoDirectory(): void
    {
        $this->filesystem->deleteDirectory('.lasso');
    }

    /**
     * @param string $bundle_directory
     * @return string
     */
    private function createZipArchiveFromBundle(string $bundle_directory): string
    {
        $files = (new Finder())
            ->in(base_path('.lasso/bundle'))
            ->files();

        $relative_path = '.lasso/dist/' . $this->bundle_id . '.zip';

        $zip_path = base_path($relative_path);

        $zip = new ZipFactory($zip_path);

        foreach ($files as $file) {
            $zip->add($file->getPathname(), $file->getRelativePathname());
        }

        $zip->closeZip();

        return $zip_path;
    }

    /**
     * @param array $data
     */
    private function sendWebhooks(array $data = [])
    {
        $this->console->info('⏳ Dispatching Webhooks...');

        $webhooks = config('lasso.webhooks.publish', []);

        foreach ($webhooks as $webhook) {
            Webhook::send($webhook, Webhook::PUBLISH_EVENT, $data);
        }

        $this->console->info('✅ Webhooks dispatched.');
    }

    /**
     * @param bool $use_git
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Sammyjo20\Lasso\Exceptions\CommittingFailed
     */
    public function execute(bool $use_git = true)
    {
        $this->compiler->buildAssets();

        $this->console->info('✅ Successfully compiled assets.');

        (new BundleManager())->create();

        $this->filesystem->ensureDirectoryExists('.lasso/dist');

        $this->console->info('⏳ Zipping assets...');

        $zip = $this->createZipArchiveFromBundle('.lasso/bundle');

        $this->console->info('✅ Successfully zipped assets.');

        // Once the Zip is done, we can create the bundle-info file.
        $bundle_info = BundleMetaFactory::create($this->bundle_id, $zip);

        // If we are using Git, we will create a lasso-bundle.json file
        // locally inside the git repository, which will then be committed.

        $this->console->info('⏳ Uploading assets to Filesystem...');

        // Create the bundle info as a file.
        $this->uploadFile($zip, $this->bundle_id . '.zip');

        if ($use_git === true) {
            $this->filesystem->delete(base_path('lasso-bundle.json'));
            $this->filesystem->put(base_path('lasso-bundle.json'), $bundle_info);
        } else {
            // If we're using non-git mode, we should delete any old bundles.
            $this->filesystem->delete(base_path('lasso-bundle.json'));

            // Now it's time to replace the bundle on the server.
            $this->filesystem->put(base_path('.lasso/dist/lasso-bundle.json'), $bundle_info);
            $this->uploadFile(base_path('.lasso/dist/lasso-bundle.json'), 'lasso-bundle.json');
        }

        // Delete the .lasso folder
        $this->deleteLassoDirectory();

        $push_to_git = config('lasso.storage.push_to_git', false);

        // If we're using git, commit the lasso-bundle file.
        if ($use_git === true && $push_to_git === true) {
            (new Committer())->commitAndPushBundle(); // Could be static
        }

        $this->console->info('✅ Successfully uploaded assets.');

        // Done. Send webhooks
        $this->sendWebhooks();
    }

    /**
     * @param string $path
     * @param string $name
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function uploadFile(string $path, string $name)
    {
        $disk = config('lasso.storage.disk');

        $upload_path = DirectoryHelper::getFileDirectory($name, $this->environment);

        Storage::disk($disk)
            ->put($upload_path, $this->filesystem->get($path));
    }
}
