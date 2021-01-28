<?php

namespace Sammyjo20\Lasso\Actions;

use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Helpers\CompilerOutputFormatter;
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

    public function execute(): void
    {
        $artisan = resolve(Artisan::class);

        Process::fromShellCommandline($this->command)
            ->setTimeout($this->timeout)
            ->mustRun(function ($type, $line) use ($artisan) {
                $artisan->compilerLine($line);
            });

        $artisan->compilerComplete();
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

    private function line($line, $bar)
    {
        $progress = CompilerOutputFormatter::getWebpackProgress($line);

        $bar->setProgress($progress);
    }
}
