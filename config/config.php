<?php

return [

    'compiler' => [

        // Options: Hybrid - Images and other assets aren't served by the CDN, Full - Everything in CDN.
        'mode' => 'hybrid',

        // What to run, this could be anything, yarn run dev, yarn run production, npm run compile.
        'command' => 'npm run dev',

        // Files that shouldn't be included in the bundle
        'excluded_files' => [],

        // Directories that shouldn't be included in the bundle
        'excluded_directories' => [],

        // IF mode is hybrid, download these folders to the local server.
        'non_cdn_items' => [
            'images'
        ],

    ],

    // What filesystem to use to upload to.

    'filesystem' => [

        'disk' => 'cdn',

        'driver' => 's3',

        'directory' => 'public',

    ],

    'hooks' => [

        // Have events that happen throughout the process.

    ],

];
