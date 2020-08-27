<?php

use Illuminate\Support\Str;

return [

    'mode' => 'local', // Local or CDN? CDN, will replace every item in the mix-manifest.json with the cdn-ified directory.

    'cdn_url' => null,

    'compiler' => [

        'package_manager' => 'npm', // Supports npm, yarn.

        'script' => 'dev', // *package_manager* run *script*

    ],

    'upload' => [

        // Which disk shall we use?
        'disk' => 'cdn',

        // Which directory on the disk should we put files?
        'upload_assets_to' => Str::slug('lasso-' . env('APP_NAME','laravel')),

        // Which path to look at
        'public_path' => public_path(),

        // Files that shouldn't be included in the bundle.
        'excluded_files' => [],

        // Directories that shouldn't be included in the bundle
        'excluded_directories' => [],

        'push_to_git' => true, // git add "lasso" && git commit -m"Lasso Assets 🐎" --author="Lasso" && git push

    ],

    'storage' => [

        'environment' => env('APP_ENV', 'production'),

        'bundles_to_keep' => 5,

    ],

    'webhooks' => [
        // Trigger webhooks for events that happen inside of Lasso

        'publish' => [
            //
        ],

        'retrieve' => [
            //
        ],
    ],

];
