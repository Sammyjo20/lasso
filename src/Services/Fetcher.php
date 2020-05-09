<?php

namespace Sammyjo20\Lasso\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class Fetcher
{
    public function execute()
    {
        $bundle_info = $this->getBundleInfo();
    }

    private function getBundleInfo()
    {
        $filesystem = new Filesystem();
        $env = app()->environment();
        $disk = config('lasso.filesystem.disk');
        $directory = config('lasso.filesystem.directory');

        try {
            $file = Storage::disk($disk)->get($directory . '/bundle-info-' . $env . '.json');
            $json = json_decode($file);
            $current = optional($json)->current;

            $bundle_id = $current->id;

            // Now copy the mix manifest.
            $file = Storage::disk($disk)
                ->get($directory . '/' . $bundle_id . '/mix-manifest.json');

            $filesystem->put(public_path('mix-manifest.json'), $file);
        } catch (\Exception $ex) { // Todo: Write better exceptions.
            dd($ex);
        }
    }
}
