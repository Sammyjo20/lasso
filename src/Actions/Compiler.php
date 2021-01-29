<?php

namespace Sammyjo20\Lasso\Actions;

use Sammyjo20\Lasso\Container\Artisan;
use Symfony\Component\Process\Process;

class Compiler
{
    /**
     * The Process shell command.
     *
     * @var mixed
     */
    protected $command;

    /**
     * The Process command timeout, if something happens to hang for too long.
     *
     * @var
     */
    protected $timeout;

    /**
     * The time it has taken for the compiler to Lasso up the assets.
     *
     * @var float
     */
    protected $compilationTime;

    public function execute(): self
    {
        $artisan = resolve(Artisan::class);

        $startTime = microtime(true);

        Process::fromShellCommandline($this->command)
            ->setTimeout($this->timeout)
            ->mustRun(function ($type, $line) use ($artisan) {
                $artisan->compilerOutput($line);
            });

        $processTime = microtime(true) - $startTime;

        $artisan->compilerComplete();

        $this->setCompilationTime($processTime);

        return $this;
    }

    public function setCommand($command): self
    {
        $this->command = $command;

        return $this;
    }

    public function setTimeout($timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    private function setCompilationTime(float $time): self
    {
        $this->compilationTime = round($time, 2);

        return $this;
    }

    public function getCompilationTime(): float
    {
        return $this->compilationTime;
    }
}
