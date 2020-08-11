<?php

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
        'upload_assets_to' => 'lasso',

        // Which path to look at
        'public_path' => public_path(),

        // Files that shouldn't be included in the bundle
        'excluded_files' => [],

        // Directories that shouldn't be included in the bundle
        'excluded_directories' => [],

    ],

    'hooks' => [
        // Have events that happen throughout the process.
        // Not sure what this would be, but I will leave this here.
    ],

];
