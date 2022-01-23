<?php

namespace Sammyjo20\Lasso\Commands;

use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\ConfigValidator;
use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Tasks\Publish\PublishJob;

final class PublishCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lasso:publish {--no-git} {--silent} {--use-commit} {--with-commit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile and push assets to the specified Lasso Filesystem Disk.';

    /**
     * Execute the console command.
     *
     * @param Artisan $artisan
     * @param Filesystem $filesystem
     * @return int
     * @throws \Sammyjo20\Lasso\Exceptions\ConfigFailedValidation
     */
    public function handle(Artisan $artisan, Filesystem $filesystem): int
    {
        (new ConfigValidator())->validate();

        $this->configureApplication($artisan, $filesystem, true);

        $dontUseGit = $this->option('no-git') === true;
        $useCommit = $this->option('use-commit') === true;
        $withCommit = $this->option('with-commit');
        $this->configureApplication($artisan, $filesystem);

        $job = new PublishJob;

        if ($dontUseGit) {
            $job->dontUseGit();
        }

        if ($useCommit) {
            $job->useCommit();
        }

        if ($withCommit) {
            $job->withCommit($withCommit);
        }

        $artisan->note(sprintf(
            '🏁 Preparing to publish assets to "%s" filesystem...',
            $filesystem->getCloudDisk()
        ));

        $job->run();

        $artisan->note(sprintf(
            '✅ Successfully published assets to "%s" filesystem! Yee-haw! 🐎',
            $filesystem->getCloudDisk()
        ));

        return 0;
    }
}
