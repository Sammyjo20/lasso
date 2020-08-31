<?php

namespace Sammyjo20\Lasso\Helpers;

use Sammyjo20\Lasso\Container\Console;
use Symfony\Component\Process\Process;

final class CommandHelper
{
    /**
     * Have a nice easy wrapper around Symfony's process component.
     *
     * @param string $command
     * @param callable $callback
     * @return callable
     */
    public static function run(string $command, callable $callback)
    {
        $console = resolve(Console::class);

        $process = new Process(
            explode(' ', $command)
        );

        $process->setTimeout(5000);
        $process->run();

        if (!$process->isSuccessful()) {
            return $callback($process);
        }
    }
}
