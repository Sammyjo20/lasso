<?php

namespace Sammyjo20\Lasso\Helpers;

use Illuminate\Filesystem\Filesystem;
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
    private function checkCompilerScript($value): bool
    {
        return ! is_null($value);
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkCompilerScriptType($value): bool
    {
        return is_string($value);
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkCompilerOutputSetting($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        return in_array($value, ['all', 'progress', 'disable']);
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkIfPublicPathExists($value): bool
    {
        return $this->filesystem->exists($value) && $this->filesystem->isReadable($value) && $this->filesystem->isWritable($value);
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkDiskExists($value): bool
    {
        return ! is_null(config('filesystems.disks.' . $value, null));
    }

    /**
     * @param $value
     * @return bool
     */
    private function checkBundleToKeepCount($value): bool
    {
        return is_int($value) && $value > 0;
    }

    /**
     * @throws ConfigFailedValidation
     * @return void
     */
    public function validate(): void
    {
        if ($this->get('storage.push_to_git') === true) {
            throw ConfigFailedValidation::because('Lasso no longer supports automatically committing lasso-bundle.json. Please remove "push_to_git" from your lasso.php config file and commit the bundle file manually.');
        }

        if (! $this->checkCompilerScript($this->get('compiler.script'))) {
            throw ConfigFailedValidation::because('You must specify a script to run the compiler. (E.g: npm run production)');
        }

        if (! $this->checkCompilerScriptType($this->get('compiler.script'))) {
            throw ConfigFailedValidation::because('Your compiler script must be a string. Since version 1.3.0, you can now write your script on one line. E.g: npm install && npm run production.');
        }

        if (! $this->checkCompilerOutputSetting($this->get('compiler.output'))) {
            throw ConfigFailedValidation::because('You must specify a valid output setting. Available options: all, progress, disable.');
        }

        if (! $this->checkDiskExists($this->get('storage.disk'))) {
            throw ConfigFailedValidation::because('The specified upload disk is not a valid disk.');
        }

        if (! $this->checkBundleToKeepCount($this->get('storage.max_bundles'))) {
            throw ConfigFailedValidation::because('You must specify how many bundles should be kept. (Min: 1)');
        }

        if (! $this->checkIfPublicPathExists($this->get('public_path'))) {
            throw ConfigFailedValidation::because('The specified public directory is not a valid or accessible directory.');
        }
    }
}
