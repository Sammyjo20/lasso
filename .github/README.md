<p align="center">
  <img src="https://getlasso.dev/images/lasso-logo-small.png" alt="Lasso" height="150">
</p>

# Lasso
### Asset wrangling for Laravel made simple.

[![Latest Stable Version](https://poser.pugx.org/sammyjo20/lasso/v)](//packagist.org/packages/sammyjo20/lasso) [![Total Downloads](https://poser.pugx.org/sammyjo20/lasso/downloads)](//packagist.org/packages/sammyjo20/lasso) [![License](https://poser.pugx.org/sammyjo20/lasso/license)](//packagist.org/packages/sammyjo20/lasso)
[![Code style][ico-code-style]][link-code-style] 

[Official Website](https://getlasso.dev)

### Introduction

Deploying Webpack assets in Laravel can be a nightmare. One problem developers have is dealing with their built assets (created by Webpack/Laravel Mix). Do you store them in version control? Do you deploy them on the server? What if I'm working with a team? Each of these solutions for assets can cause headaches for the developer, including merge conflicts and slowing down servers.

Lasso is a Laravel package designed to take the headaches out of deploying assets to your servers. It works great on load balanced environments too.

### What does Lasso do?

Lasso compiles your assets on your local machine or within Continuous Integration (e.g GitHub Actions) and then uploads the assets to one of your Laravel Filesystems. This harnesses the power of your local machine which is likely much more powerful than the server. 

During deployment, Lasso will then download your assets from the filesystem. It uses Git to keep track of the last asset bundle created, as well as automatically cleans old bundles.

## Installation

Lasso requires Laravel 6+ and PHP 7.3 or higher. To install Lasso, simply run the composer require command below:

```bash
composer require sammyjo20/lasso
```

After that, run the command below to create the lasso.php config file:

```bash
php artisan vendor:publish --tag=lasso-config
```

## Configuration

Now make sure to configure the lasso.php config file in your app/config directory. Make sure to specify a Filesystem Disk you would like Lasso to use.

**If you have multiple projects, make sure to change the "upload_to" path, otherwise you may have asset conflicts in your applications.**

## Getting Setup
Make sure to add all of your public assets that are created by Webpack/Laravel Mix to your .gitignore file. Make sure to also add the ".lasso" directory to your .gitignore file.

Example:

```text
mix-manifest.json
public/css/*
public/js/*
.lasso
```
> The .lasso folder is a temporary directory created by Lasso to keep assets while they're being zipped up. This folder is automatically created and deleted, but it's good to ignore this directory anyway, just in case Lasso falls over before it reaches the cleanup phase.

## Recommended Usage

Lasso comes with two commands that should be used by your project/deployment process. The recommended usage is to run the "publish" command on your local machine, which is likely much more powerful than Continuous Integration or compiling on the server.

### Publish

The publish command should be executed when you would like to upload/publish new assets to your application. Lasso will run the provided script (e.g npm run production) and then zip up the files created by the compiler (e.g Webpack).

```bash
php artisan lasso:publish
```

After running this command, Lasso will create a "lasso-bundle.json" file in your application's root directory. This is the recommended approach as when you commit the file, Lasso will use this to download the latest bundle relating to your commit. If you don't use Git, for example if you are compiling assets within Continuous Integration, you can add the `--no-git` flag to the command.

**Warning: When using the `--no-git` flag, versioning will be limited as the lasso-bundle.json is stored in your Filesystem, rather than your repository. Using Git is the recommended approach.**

If you use git, and want to easily keep track of which bundle file is for what commit, use the `--use-commit` flag. It will ensure the bundle zip file name is the first 12 characters of the commit hash. This also adds the advantage of publishing the bundles during your CI pipeline, without having to make a new commit, whilst giving the versioning benefits of using git.
### Pull

The pull command should then be executed on your deployment script, or on the servers which will require the assets to be on. Simply run the command below. If you are using *Laravel Forge*, add this command to your deployment script. If you are using *Laravel Envoyer*, add this to the list of hooks during your deployment. It should be run on **every server**.

Use the `--use-commit` flag when pulling, if you are publishing the bundles with the `--use-commit` flag.
```bash
php artisan lasso:pull
```

## Usage within Continuous Integration (CI) e.g Github Actions

To use Lasso during continuous integration, it's recommended to run the `php artisan lasso:publish` command, and then commit the "lasso-bundle.json" file which is created. If you aren't able to commit files during your CI process, use the `--no-git` flag on the command, e.g: `php artisan lasso:publish --no-git`

Read this excellent blog post by Alex Justesen on how to integrate Lasso with your CI/CD pipeline: https://dev.to/alexjustesen/laravel-cicd-pipeline-w-lasso-and-github-actions-53gm

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

## Security

If you find any security related issues, please send me an email to import.lorises_0c@icloud.com.

## And that's it! ✨

This is my first Laravel package, I really hope it's been useful to you, if you like my work and want to show some love, consider buying me some coding fuel (Coffee) ❤

[Donate Java (the drink not the language)](https://ko-fi.com/sammyjo20)

[ico-tests]: https://img.shields.io/github/workflow/status/Sammyjo20/Lasso/tests/master?label=tests&style=flat-square
[link-tests]: https://github.com/Sammyjo20/Lasso/actions?query=workflow%3Atests
[ico-code-style]: https://github.com/Sammyjo20/Lasso/workflows/code%20style/badge.svg
[ico-code-style]: https://img.shields.io/github/workflow/status/Sammyjo20/Lasso/code%20style?style=flat-square
[link-code-style]: https://github.com/Sammyjo20/Lasso/actions?query=workflow%3A%22code+style%22
