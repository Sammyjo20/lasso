<?php

namespace Sammyjo20\Lasso\Commands;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Services\Bundler;

class CreateBundle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lasso:publish {-f?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the bundle of files';

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

        // $force = $this->option('f');

        // Check to see if the current environment is supported
        // Also check to see if the git commit contains "no-lasso"

        return (new Bundler())->execute();
    }
}
