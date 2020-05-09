<?php


namespace Sammyjo20\Lasso\Commands;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Services\Fetcher;

class FetchBundle extends Command
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
    protected $description = 'Fetch the latest bundle.';

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

        return (new Fetcher())->execute();
    }
}
