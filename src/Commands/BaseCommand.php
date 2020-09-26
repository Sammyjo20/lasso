<?php

namespace Sammyjo20\Lasso\Commands;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\Filesystem;

class BaseCommand extends Command
{
    /**
     * Configure the Artisan console and the Filesystem, ready for publishing.
     *
     * @param Artisan $artisan
     * @param Filesystem $filesystem
     * @param bool $checkFilesystem
     */
    protected function configureApplication(Artisan $artisan, Filesystem $filesystem, bool $checkFilesystem = false): void
    {
        $noPrompt = $this->option('silent') === true;
        $lassoEnvironment = config('lasso.storage.environment', null);

        $artisan->setCommand($this);

        if ($noPrompt) {
            $artisan->silent();
        }

        if ($checkFilesystem === true && $noPrompt === false && ! is_null($lassoEnvironment)) {
            $definedEnv = $this->ask('🐎 Which Lasso environment would you like to publish to?', $lassoEnvironment);

            $filesystem->setLassoEnvironment($definedEnv);
        }
    }
}
