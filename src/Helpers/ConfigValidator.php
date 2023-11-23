<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use Illuminate\Filesystem\Filesystem;
use Sammyjo20\Lasso\Exceptions\ConfigFailedValidation;

/**
 * @internal
 */
class ConfigValidator
{
    /**
     * Filesystem
     */
    public Filesystem $filesystem;

    /**
     * ConfigValidator constructor.
     */
    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Get config parameter
     */
    private function get(string $item): mixed
    {
        return config('lasso.' . $item);
    }

    /**
     * Check the compiler script
     */
    private function checkCompilerScript(mixed $value): bool
    {
        return ! is_null($value);
    }

    /**
     * Check compiler script type
     */
    private function checkCompilerScriptType(mixed $value): bool
    {
        return is_string($value);
    }

    /**
     * Check compiler output
     */
    private function checkCompilerOutputSetting(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        return in_array($value, ['all', 'progress', 'disable']);
    }

    /**
     * Check if public path exists
     */
    private function checkIfPublicPathExists(mixed $value): bool
    {
        return $this->filesystem->exists($value) && $this->filesystem->isReadable($value) && $this->filesystem->isWritable($value);
    }

    /**
     * Check disk exists
     */
    private function checkDiskExists(mixed $value): bool
    {
        return ! is_null(config('filesystems.disks.' . $value));
    }

    /**
     * Check bundle to keep count
     */
    private function checkBundleToKeepCount(mixed $value): bool
    {
        return is_int($value) && $value > 0;
    }

    /**
     * Validate config
     *
     * @throws ConfigFailedValidation|\Sammyjo20\Lasso\Exceptions\BaseException
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
