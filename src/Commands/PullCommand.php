<?php

declare(strict_types=1);


namespace Sammyjo20\Lasso\Commands;

use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Tasks\Pull\PullJob;
use Sammyjo20\Lasso\Helpers\ConfigValidator;

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
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Sammyjo20\Lasso\Exceptions\BaseException
     * @throws \Sammyjo20\Lasso\Exceptions\ConfigFailedValidation
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

        $job = new PullJob;

        if ($useCommit) {
            $job->useCommit();
        }

        if (is_string($withCommit)) {
            $job->withCommit(mb_substr((string)$withCommit, 0, 12));
        }

        $job->run();

        $artisan->note('✅ Successfully downloaded the latest assets. Yee-haw!');
        $artisan->note('❤️  Thank you for using Lasso.');

        return self::SUCCESS;
    }
}
