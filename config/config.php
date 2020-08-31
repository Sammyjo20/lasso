<?php

use Illuminate\Support\Str;

return [

    'compiler' => [

        /*
         * Configure which command Lasso should run in its deployment
         * phase. This will most likely be "npm run production" but
         * you may choose what you would like to execute.
         */
        'script' => 'npm run production',

        /*
         * If there any directories/files you would like to Lasso to
         * exclude when uploading to the Filesystem, specify them below.
         */
        'excluded_files' => [],

        'excluded_directories' => [],

    ],

    'storage' => [

        /*
         * Specify the filesystem Lasso should use to use to store
         * and retrieve its files.
         */
        'disk' => 'assets',

        /*
         * Specify the directory Lasso should store all of its
         * files within. By default we will use "lasso-APP_NAME".
         */
        'upload_to' => sprintf('lasso-%s', Str::slug(env('APP_NAME','laravel'))),

        /*
         * Lasso can also create a separate directory containing
         * the environment the files will be stored in. Specify this
         * here.
         */
        'environment' => env('LASSO_ENV', null),

        /*
         * After running "php artisan lasso:push", by default Lasso will
         * create a "lasso-bundle.json" file. If you would like Lasso to
         * automatically commit and push the file to Git after running the
         * command, enable this.
         */
        'push_to_git' => false,

        /*
         * Lasso will automatically version the assets. This is useful if you
         * suddenly need to roll-back a deployment and use an older version
         * of built files. You can set the maximum amount of files stored here.
         */
        'max_bundles' => 5,

    ],

    /*
     * Lasso will can also trigger Webhooks after its commands have been
     * successfully executed. You may specify URLs that Lasso will POST
     * to, for each of the commands.
     */
    'webhooks' => [

        /*
         * Specify which webhooks should be triggered after a successful
         * "php artisan lasso:push" command execution.
         */
        'push' => [
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
