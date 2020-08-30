<?php


namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Exceptions\CompilerFailed;
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
        $command = config('lasso.compiler.script');

        $this->console->info('🔥 Compiling assets...');

        CommandHelper::run($command, function ($process) {
//            throw new CompilerFailedException('Something really bad happened.');
            // Something very bad has happened.
        });
    }
}
