<?php


namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Helpers\CommandHelper;

class Compiler
{
    /**
     * @var Console
     */
    public $console;

    /**
     * Compiler constructor.
     */
    public function __construct()
    {
        $this->console = resolve(Console::class);
    }

    public function buildAssets()
    {
        $this->console->info(sprintf(
            'Compiling assets (%s) ⏳', config('lasso.compiler.command')
        ));

        CommandHelper::run(config('lasso.compiler.command'), function ($process) {
            // Something very bad has happened.
        });
    }
}
