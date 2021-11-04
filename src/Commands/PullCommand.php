<?php


namespace Sammyjo20\Lasso\Commands;

use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\ConfigValidator;
use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Tasks\Pull\PullJob;

final class PullCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lasso:pull {--silent} {--use-commit} {--with-commit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the latest Lasso bundle from the Filesystem Disk.';

    /**
     * Execute the console command.
     *
     * @param Artisan $artisan
     * @param Filesystem $filesystem
     * @return int
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Sammyjo20\Lasso\Exceptions\BaseException
     * @throws \Sammyjo20\Lasso\Exceptions\ConfigFailedValidation
     * @throws \Sammyjo20\Lasso\Exceptions\PullJobFailed
     */
    public function handle(Artisan $artisan, Filesystem $filesystem): int
    {
        (new ConfigValidator())->validate();

        $useCommit = $this->option('use-commit') === true;
        $withCommit = $this->option('with-commit');
        $this->configureApplication($artisan, $filesystem);

        $artisan->setCommand($this);

        $artisan->note(sprintf(
            '🏁 Preparing to download assets from "%s" filesystem.',
            $filesystem->getCloudDisk()
        ));

        $job = new PullJob();

        if ($useCommit) {
            $job->useCommit();
        }

        if ($withCommit) {
            $job->withCommit(substr(0, 12, $withCommit));
        }

        $job->run();

        $artisan->note('✅ Successfully downloaded the latest assets. Yee-haw!');
        $artisan->note('❤️  Thank you for using Lasso. https://getlasso.dev');

        return 0;
    }
}
