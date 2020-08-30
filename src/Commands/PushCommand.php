<?php

namespace Sammyjo20\Lasso\Commands;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Helpers\ConfigValidator;
use Sammyjo20\Lasso\Services\Bundler;

class PushCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lasso:push {--no-git}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the asset compiler and upload the assets to the disk.';

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
        (new ConfigValidator())->validate();

        $console->setCommand($this);

        // Check to see if the current environment is supported
        // Also check to see if the git commit contains "no-lasso"
        $use_git = !$this->option('no-git');

        $this->info('🏁 Starting to publish assets');

        (new Bundler())->execute($use_git);

        $this->info('🐎 Successfully published assets to Filesystem! Yee-haw!');
    }
}
