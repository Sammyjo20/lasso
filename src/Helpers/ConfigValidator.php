<?php

namespace Sammyjo20\Lasso\Helpers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Sammyjo20\Lasso\Exceptions\ConfigFailedValidation;

class ConfigValidator
{
    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * ConfigValidator constructor.
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @param string $item
     * @return mixed
     */
    private function get(string $item)
    {
        return config('lasso.' . $item, null);
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkMode($value): bool
    {
        return Str::contains($value, ['local', 'cdn']);
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkCdnUrl($value): bool
    {
        return !is_null($value) && filter_var($value, FILTER_SANITIZE_URL);
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkPackageManager($value): bool
    {
        return Str::contains($value, ['npm', 'yarn']);
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkIfPublicPathExists($value): bool
    {
        return $this->filesystem->exists($value);
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkDiskExists($value): bool
    {
        return !is_null(config('filesystems.disks.' . $value, null));
    }

    /**
     * @throws ConfigFailedValidation
     * @return void
     */
    public function validate(): void
    {
        if (!$this->checkMode($this->get('mode'))) {
            throw ConfigFailedValidation::because('The specified mode must be either "local" or "cdn".');
        }

        if ($this->get('mode') === 'cdn' && !$this->checkCdnUrl($this->get('cdn_url'))) {
            throw ConfigFailedValidation::because('The specified CDN url is invalid.');
        }

        if (!$this->checkPackageManager($this->get('compiler.package_manager'))) {
            throw ConfigFailedValidation::because('The specified package manager must be either "npm" or "yarn".');
        }

        if (!$this->checkIfPublicPathExists($this->get('upload.public_path'))) {
            throw ConfigFailedValidation::because('The specified public directory is not a valid or accessible directory.');
        }

        if (!$this->checkDiskExists($this->get('upload.disk'))) {
            throw ConfigFailedValidation::because('The specified upload disk is not a valid disk.');
        }
    }
}
