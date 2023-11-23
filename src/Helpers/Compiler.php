<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Helpers;

use Sammyjo20\Lasso\Container\Artisan;
use Symfony\Component\Process\Process;

class Compiler
{
    /**
     * The Process shell command.
     */
    protected string $command;

    /**
     * The Process command timeout, if something happens to hang for too long.
     */
    protected int $timeout;

    /**
     * The time it has taken for the compiler to Lasso up the assets.
     *
     * @var float
     */
    protected float $compilationTime = 0;

    /**
     * Execute the compiler
     */
    public function execute(): self
    {
        $artisan = resolve(Artisan::class);

        $startTime = microtime(true);

        Process::fromShellCommandline($this->command)
            ->setTimeout($this->timeout)
            ->mustRun(function ($type, $line) use ($artisan) {
                $artisan->showCompilerOutput($line);
            });

        $processTime = microtime(true) - $startTime;

        $artisan->compilerComplete();

        $this->compilationTime = round($processTime, 2);

        return $this;
    }

    /**
     * Set the command for the compiler
     */
    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Set the timeout for the compiler
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Get the compilation time
     */
    public function getCompilationTime(): float
    {
        return $this->compilationTime;
    }
}
