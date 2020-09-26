<p align="center">
  <img src="https://getlasso.dev/images/lasso-logo-small.png" alt="Lasso">
</p>

# Lasso

[![Code style][ico-code-style]][link-code-style]

## Asset wrangling for Laravel made simple.

[Official Website](https://getlasso.dev)

### Introduction

Lasso is a Laravel package designed to make your deployments faster and easier. One problem developers have is dealing with their built assets (Webpack/Laravel Mix). Do you store them in Git? Do you deploy them on the server? Each of these solutions for assets can cause headaches for the developer, including merge conflicts and slowing down servers.

### What does Lasso do?

Lasso compiles your assets on your local machine or in Continuous Integration and then uploads the assets to a Laravel Filesystem (Flysystem). On deployment, Lasso will then download those assets from the Filesystem. It uses Git to keep track of the last asset bundle created, as well as automatically cleans old bundles.

## Disclaimer

Lasso is still in early development. Therefore, I haven't yet written any tests for Lasso, and the code may still be a little messy! I'm hoping to improve Lasso massively over time, but I was too excited not to share Lasso with the world. If you find any bugs, please open an issue on Github and I'll do my best to get back to you quickly. If you find any security related issues, please send me an email to sam.carre2000@gmail.com.

## Installation

Lasso requires Laravel 6+ and PHP 7.3 or higher. To install Lasso, simply run the composer require command below:

```
composer require sammyjo20/lasso ^1.2
```

After that, run the command below to create the lasso.php config file:

```
php artisan vendor:publish --tag=lasso-config
```

## Configuration

Now make sure to configure the lasso.php config file in your app/config directory. Make sure to specify a Filesystem Disk you would like Lasso to use.

**Warning: If you have multiple projects, make sure to change the "upload_to" path, otherwise you may have asset conflicts in your applications.**

```php
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
         * should run before it times out. By default this is set
         * to 600 seconds (10 minutes).
         */
        'timeout' => 600,

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
         * Lasso will automatically version the assets. This is useful if you
         * suddenly need to roll-back a deployment and use an older version
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
```

## First things first
If you would like to use the recommended approach, make sure to add all of your public assets (js/css/images/mix-manifest.json) to your .gitignore file! Please also make sure to add the ".lasso" folder to your .gitignore file:

```php
.lasso
```

## Recommended Usage

Lasso comes with two commands that should be used by your project/deployment pipeline. The recommended usage is to run the "publish" command on your local machine, which is likely much more powerful than Continuous Integration or compiling on the server.

### Publish

The publish command should be executed when you would like to upload/publish new assets to your application. Lasso will run the provided script (e.g npm run production) and then zip up the files created by the compiler (e.g Webpack).

```php
php artisan lasso:publish
```

After running this command, Lasso will create a "lasso-bundle.json" file in your application's root directory. This is the recommended approach as when you commit the file, Lasso will use this to download the latest bundle relating to your commit. If you don't use Git, for example if you are compiling assets within Continuous Integration, you can add the `--no-git` flag to the command.

**Warning: When using the `--no-git` flag, versioning will be limited as the lasso-bundle.json is stored in your Filesystem, rather than your repository. Using Git is the recommended approach.**

### Pull

The pull command should then be executed on your deployment script, or on the servers which will require the assets to be on. Simply run the command below. If you are using *Laravel Forge*, add this command to your deployment script. If you are using *Laravel Envoyer*, add this to the list of hooks during your deployment. It should be run on **every server**.

```php
php artisan lasso:pull
```

## Usage within Continuous Integration (CI) e.g Github Actions

To use Lasso during continuous integration, it's recommended to run the `php artisan lasso:publish` command, and then commit the "lasso-bundle.json" file which is created. If you aren't able to commit files during your CI process, use the `--no-git` flag on the command, e.g: `php artisan lasso:publish --no-git`

## Multiple Environments

Out of the box, Lasso supports multi-environment applications. If your application has a staging environment, for example - you can use the `LASSO_ENV` environment variable to set the current environment. On your web servers:

```php
LASSO_ENV=staging
```

## Webhooks

Lasso can also trigger webhooks when a command has been executed successfully. Simply list the URLs in the "webhooks" array in the lasso.php config file.

```php
/*
* Lasso will can also trigger Webhooks after its commands have been
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
```

## Cleanup

Lasso will automatically try to keep your Filesystem clean, and will automatically delete old bundles. You can increase/decrease the amount of Bundles Lasso will keep per enviroment by setting the `max_bundles` configuratiion variable in the config/lasso.php file.

```php
/*
 * Lasso will automatically version the assets. This is useful if you
 * suddenly need to roll-back a deployment and use an older version
 * of built files. You can set the maximum amount of files stored here.
 */
'max_bundles' => 5,
```

## Excluded files/directories

Lasso will copy the public directory during it's publish process. If you have any files or directories that you would like Lasso to ignore during this process, specify them in the `excluded_files` and `excluded_directories` configuation variables in the config/lasso.php file.

```php
/*
 * If there any directories/files you would like to Lasso to
 * exclude when uploading to the Filesystem, specify them below.
 */
'excluded_files' => [],

'excluded_directories' => [],
```

## Thanks

Special thanks to @codepotato for the logo! ❤️

---

## And that's it! ✨

This is my first Laravel package, I really hope it's been useful to you, if you like my work and want to show some love, consider buying me some coding fuel (Coffee) ❤

[Donate Java (the drink not the language)](https://ko-fi.com/sammyjo20)

[ico-code-style]: https://github.com/Sammyjo20/Lasso/workflows/code%20style/badge.svg
[link-code-style]: https://github.com/Sammyjo20/Lasso/actions?query=workflow%3A%22code+style%22
