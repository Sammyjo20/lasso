<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Commands;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\Filesystem;

class BaseCommand extends Command
{
    /**
     * Configure the Artisan console and the Filesystem, ready for publishing.
     */
    protected function configureApplication(Artisan $artisan, Filesystem $filesystem, bool $checkFilesystem = false): void
    {
        $silent = $this->option('silent') === true;
        $lassoEnvironment = config('lasso.storage.environment');

        $artisan->setCommand($this);

        if ($silent) {
            $artisan->silent();
        }

        if ($checkFilesystem === true && $silent === false && ! is_null($lassoEnvironment)) {
            $definedEnv = $this->ask('🐎 Which Lasso environment would you like to publish to?', $lassoEnvironment);

            $filesystem->setLassoEnvironment($definedEnv);
        }
    }
}
