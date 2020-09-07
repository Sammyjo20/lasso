<?php


namespace Sammyjo20\Lasso\Commands;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Helpers\ConfigValidator;
use Sammyjo20\Lasso\Services\Fetcher;

final class PullCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lasso:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the latest Lasso bundle from the Filesystem Disk.';

    /**
     * Execute the console command.
     *
     * @param Console $console
     * @throws \Sammyjo20\Lasso\Exceptions\ConfigFailedValidation
     */
    public function handle(Console $console)
    {
        (new ConfigValidator())->validate();

        $console->setCommand($this);

        $disk = config('lasso.storage.disk');

        $this->info('🏁 Preparing to download assets from "' . $disk . '" Filesystem.');

        (new Fetcher())->execute();

        $this->info('✅ Successfully downloaded the latest assets. Yee-haw!');
        $this->info('❤️  Thank you for using Lasso. https://getlasso.dev');
    }
}
