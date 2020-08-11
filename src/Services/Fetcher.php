<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Sammyjo20\Lasso\Factories\ZipExtractor;

class Fetcher
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $environment;

    /**
     * Fetcher constructor.
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();

        $this->environment = app()->environment();
    }

    public function execute()
    {
        $bundle_info = $this->getBundleInfo();
    }

    private function getBundleInfo()
    {
        $disk = config('lasso.upload.disk');
        $directory = config('lasso.upload.upload_assets_to') . '/' . $this->environment;

        // Todo: Rewrite this so it isn't poop. (I'm just getting it working now)

        try {
            $file = Storage::disk($disk)
                ->get($directory . '/bundle-info.json');

            $json = json_decode($file);
            $id = optional($json)->id;

            $file = Storage::disk($disk)
                ->get($directory . '/' . $id . '.zip');

            // Unzip the zip, put it in the .lasso/dist directory
            // Use finder to process each file
            // replace files.

            $path = base_path('.lasso/' . $id . '.zip');

            $this->filesystem->ensureDirectoryExists('.lasso');

            $this->filesystem->put('.lasso/' . $id . '.zip', $file);

            (new ZipExtractor($path))
                ->extractTo(base_path('.lasso/dist'));

            // Now we want to replace the original public directory.

            dd('Done');

        } catch (\Exception $ex) { // Todo: Write better exceptions.
            dd($ex);
        }
    }
}
