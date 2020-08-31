<?php


namespace Sammyjo20\Lasso\Services;

use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Helpers\CommandHelper;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class Compiler
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

    /**
     * @param string $command
     * @param float $timeout
     */
    public function compile(string $command, float $timeout)
    {
        $process = new Process(explode(' ', $command));

        $process->setTimeout($timeout)
            ->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @return void
     */
    public function buildAssets(): void
    {
        $command = config('lasso.compiler.script');
        $timeout = config('lasso.compiler.timeout', 600);

        $this->console->info('⏳ Compiling assets...');

        $this->compile($command, $timeout);
    }
}
