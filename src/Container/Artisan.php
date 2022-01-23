<?php

namespace Sammyjo20\Lasso\Container;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Exceptions\ConsoleMethodException;
use Sammyjo20\Lasso\Helpers\CompilerOutputFormatter;
use Symfony\Component\Console\Helper\ProgressBar;

final class Artisan
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * @var bool
     */
    protected $isSilent = false;

    /**
     * @var bool
     */
    private $compilerOutputMode = 'progress';

    /**
     * @var ProgressBar|null
     */
    private $progressBar = null;

    public function __construct()
    {
        $this->setCompilerOutputMode(config('lasso.compiler.output', 'progress'));
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed|void
     * @throws ConsoleMethodException
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->command, $name)) {
            return call_user_func_array([$this->command, $name], $arguments);
        }

        throw new ConsoleMethodException(sprintf(
            'Method %s::%s does not exist.',
            get_class($this->command),
            $name
        ));
    }

    /**
     * Create a note for the front end, set the second parameter to true for an error.
     *
     * @param string $message
     * @param bool $error
     * @return $this
     */
    public function note(string $message, bool $error = false): self
    {
        if (! $this->isSilent) {
            $command = $error === true ? 'error' : 'info';
            $this->$command($message);
        }

        return $this;
    }

    public function compilerOutput(string $line): void
    {
        $mode = $this->compilerOutputMode;

        if ($mode === 'all') {
            $this->note($line);

            return;
        }

        if ($mode === 'progress') {
            $progressBar = $this->getProgressBar();

            $progressBar->setProgress(
                CompilerOutputFormatter::getWebpackProgress($line)
            );
        }
    }

    public function compilerComplete(): void
    {
        if ($this->progressBar instanceof ProgressBar) {
            $this->progressBar->finish();
            $this->command->getOutput()->newLine();
        }
    }

    private function getProgressBar(): ProgressBar
    {
        if ($bar = $this->progressBar) {
            return $bar;
        }

        $bar = $this->command->getOutput()->createProgressBar(100);
        $bar->setFormat('🏁 [%bar%] 🏆 %percent:3s%%');
        $bar->setProgressCharacter('🐎');
        $bar->setBarCharacter('=');
        $bar->setEmptyBarCharacter('-');
        $bar->start();

        $this->setProgressBar($bar);

        return $bar;
    }

    public function setCommand(Command $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function silent(): self
    {
        $this->isSilent = true;

        return $this;
    }

    private function setCompilerOutputMode(string $mode): self
    {
        $this->compilerOutputMode = $mode;

        return $this;
    }

    private function setProgressBar(ProgressBar $bar): self
    {
        $this->progressBar = $bar;

        return $this;
    }
}
