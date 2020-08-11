<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Finder;

class Bundler
{
    /**
     * @var Compiler
     */
    public $compiler;

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * @var string
     */
    public $bundle_id;

    /**
     * Bundler constructor.
     */
    public function __construct()
    {
        $this->compiler = new Compiler();

        $this->filesystem = new Filesystem();

        $this->bundle_id = Str::random(20);
    }

    public function execute()
    {
        $public_path = config('lasso.upload.public_path');

        // $this->compiler->buildAssets();

        // Command completed,

        $asset_url = config('app.asset_url', null);

        // Now let's move all the files (except excluded to a new folder)
        $this->filesystem->copyDirectory($public_path, '.lasso/bundle');

        // Clean any excluded files/directories
        (new BundleCleaner())->execute();

        // Now make a custom mix-manifest file but replace the location to be the CDN url.
        $manifest = array_map(function ($value) use ($asset_url) {
            return $asset_url . '/' . $this->bundle_id . $value;
        }, get_object_vars($manifest));

        // Replace the old manifest with the new
        $this->filesystem->put('.lasso/bundle/mix-manifest.json', json_encode($manifest));

        // Copy files to filesystem. (use Symfony's Finder to help)
        $finder = new Finder();
        $items = $finder
            ->in(base_path('.lasso/bundle'))
            ->files();

        // Make a new directory
        $disk = config('lasso.filesystem.disk');
        $directory = config('lasso.filesystem.directory');

        Storage::disk($disk)
            ->makeDirectory($directory . '/' . $this->bundle_id . '/');

        foreach ($items as $item) {
            $this->uploadFile($item);
        }

        // Now create a "bundle-info.json" file that we can use to grab the files from.
        // Todo: Will have the ability to roll back to previous assets if something bad happens.

        $this->createBundleInfo();

        // Delete the .lasso folder
        $this->filesystem->deleteDirectory('.lasso');
    }

    /**
     * @param $file
     */
    public function uploadFile($file)
    {
        $disk = config('lasso.filesystem.disk');
        $directory = config('lasso.filesystem.directory');

        $path = $directory . '/' . $this->bundle_id . '/' . $file->getRelativePathName();

        Storage::disk($disk)
            ->put($path, $file->getContents(), 'public');
    }

    /**
     *
     */
    public function createBundleInfo()
    {
        $env = app()->environment();
        $disk = config('lasso.filesystem.disk');
        $directory = config('lasso.filesystem.directory');

        $mode = config('lasso.compiler.mode');
        $non_cdn_items = config('lasso.compiler.non_cdn_items', []);

        // Todo: Perhaps for non-cdn folders we should zip them up? That would definitely make it
        // easer to download on the the servers?

        $info = [
            'current' => [
                'id' => $this->bundle_id,
                'mode' => $mode,
                'pull' => $non_cdn_items,
            ],
            'previous' => null,
        ];

        Storage::disk($disk)
            ->put($directory . '/bundle-info-' . $env . '.json', json_encode($info), 'private');
    }
}
