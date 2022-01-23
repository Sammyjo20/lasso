<?php

return [

    'compiler' => [

        /*
         * Configure which command Lasso should run in its deployment
         * phase. This will most likely be "npm run production" but
         * you may choose what you would like to execute.
         */
        'script' => 'npm run production',

        /*
         * Configure the amount of time (in seconds) the compiler
         * should run before it times out. By default, this is set
         * to 600 seconds (10 minutes).
         */
        'timeout' => 600,

        /*
         * If there are any directories/files you would like to Lasso to
         * exclude when uploading to the Filesystem, specify them below.
         */
        'excluded_files' => [],

        'excluded_directories' => [],

    ],

    'storage' => [

        /*
         * Specify the filesystem Lasso should use to store
         * and retrieve its files.
         */
        'disk' => 'assets',

        /*
         * Specify the directory Lasso should store all of its
         * files within.
         *
         * WARNING: If you have multiple projects all using Lasso,
         * make sure this is unique for each project.
         */
        'upload_to' => 'lasso',

        /*
         * Lasso can also create a separate directory containing
         * the environment the files will be stored in. Specify this
         * here.
         */
        'environment' => env('LASSO_ENV', null),

        /*
         * Lasso can add a prefix to the bundle file, in order to store
         * multiple bundle files in the same filesystem for different
         * environments
         */
        'prefix' => env('LASSO_PREFIX', ''),

        /*
         * Lasso will automatically version the assets. This is useful if you
         * suddenly need to roll back a deployment and use an older version
         * of built files. You can set the maximum amount of files stored here.
         */
        'max_bundles' => 5,

    ],

    /*
     * Lasso can also trigger Webhooks after its commands have been
     * successfully executed. You may specify URLs that Lasso will POST
     * to, for each of the commands.
     */
    'webhooks' => [

        /*
         * Specify which webhooks should be triggered after a successful
         * "php artisan lasso:publish" command execution.
         */
        'publish' => [
            //
        ],

        /*
         * Specify which webhooks should be triggered after a successful
         * "php artisan lasso:pull" command execution.
         */
        'pull' => [
            //
        ]

    ],

    /*
     * Where are your assets stored? Most of the time, they will
     * be stored within the /public directory in Laravel - but if
     * you have changed this - please specify it below.
     */
    'public_path' => public_path(),
];
