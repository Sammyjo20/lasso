<?php


namespace Sammyjo20\Lasso\Commands;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Services\Fetcher;

class FetchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lasso:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grab the latest Lasso bundle from the filesystem, and pull down the files.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Console $console)
    {
        $console->setCommand($this);

        // 1. Pull down the zip and unzip the bundle
        // 2. Check the integrity of the ZIP
        // 3. Take a Backup of the public_path, before tampering with it.
        // 4. If something goes wrong, we just roll back to the backup, but after that throw an exception.
        // 5. If something doesn't go wrong, whoop.

        return (new Fetcher())->execute();
    }
}
