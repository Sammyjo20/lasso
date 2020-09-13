<?php

namespace Sammyjo20\Lasso\Commands;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Helpers\ConfigValidator;
use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Tasks\Publish\PublishJob;
use Sammyjo20\Lasso\Services\Bundler;

final class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lasso:publish {--no-git} {--silent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile assets and push assets to the specified Lasso Filesystem Disk.';

    /**
     * Execute the console command.
     *
     * @param Artisan $artisan
     * @param Filesystem $filesystem
     */
    public function handle(Artisan $artisan, Filesystem $filesystem)
    {
        $dontUseGit = $this->option('no-git') === true;
        $this->configureApplication($artisan, $filesystem);

        $job = new PublishJob;

        if ($dontUseGit) {
            $job->dontUseGit();
        }

        $artisan->note(sprintf(
            '🏁 Preparing to publish assets to "%s" filesystem...', $filesystem->getCloudDisk()
        ));

        $job->run();

        $artisan->note(sprintf(
            '✅ Successfully published assets to "%s" filesystem! Yee-haw! 🐎', $filesystem->getCloudDisk()
        ));
    }

    /**
     * Configure the Artisan console and the Filesystem, ready for publishing.
     *
     * @param Artisan $artisan
     * @param Filesystem $filesystem
     * @return void
     */
    private function configureApplication(Artisan $artisan, Filesystem $filesystem): void
    {
        $noPrompt = $this->option('silent') === true;
        $lassoEnvironment = config('lasso.storage.environment', null);

        $artisan->setCommand($this);

        if ($noPrompt === false && !is_null($lassoEnvironment)) {
            $definedEnv = $this->ask('🐎 Which Lasso environment would you like to publish to?', $lassoEnvironment);

            $filesystem->setLassoEnvironment($definedEnv);
        }
    }
}
