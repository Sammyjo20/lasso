<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Sammyjo20\Lasso\Factories\BundleFactory;
use Sammyjo20\Lasso\Factories\ZipFactory;
use Symfony\Component\Finder\Finder;

class Bundler
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
     * Bundler constructor.
     */
    public function __construct()
    {
        $this->compiler = new Compiler();
        $this->filesystem = new Filesystem();
        $this->bundle_id = Str::random(20);
        $this->environment = app()->environment();

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

    public function execute()
    {
        $mode = config('lasso.mode');
        $public_path = config('lasso.upload.public_path');

        $this->compiler->buildAssets();

        // Command completed,
        $asset_url = config('app.asset_url', null);

        // Now let's move all the files into a temporary location.
        $this->filesystem->copyDirectory($public_path, '.lasso/bundle');

        $this->filesystem->ensureDirectoryExists('.lasso/dist');

        // Clean any excluded files/directories from the bundle
        (new BundleCleaner())->execute();

        // Todo: If the mode === CDN, we need to process the mix-manifest too.

        //        $manifest = array_map(function ($value) use ($asset_url) {
//            return $asset_url . '/' . $this->bundle_id . $value;
//        }, get_object_vars($manifest));
//
//        $this->filesystem->put('.lasso/bundle/mix-manifest.json', json_encode($manifest));

        $zip = $this->createZipArchiveFromBundle('.lasso/bundle');

        // Once the Zip is done, we can create the bundle-info file.
        $bundle_info = BundleFactory::create($this->bundle_id);

        // Create the bundle info as a file.
        $this->filesystem->put(base_path('.lasso/dist/bundle-info.json'), $bundle_info);

        $this->uploadFile($zip, $this->bundle_id . '.zip');
        $this->uploadFile(base_path('.lasso/dist/bundle-info.json'), 'bundle-info.json');

        // Delete the .lasso folder
        $this->deleteLassoDirectory();
    }

    /**
     * @param string $path
     * @param string $name
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function uploadFile(string $path, string $name)
    {
        $disk = config('lasso.upload.disk');
        $directory = config('lasso.upload.upload_assets_to');

        $upload_path = $directory . '/' . $this->environment . '/' . $name;

        Storage::disk($disk)
            ->put($upload_path, $this->filesystem->get($path));
    }
}
