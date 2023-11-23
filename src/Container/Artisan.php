<?php

declare(strict_types=1);

namespace Sammyjo20\Lasso\Container;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Sammyjo20\Lasso\Helpers\CompilerOutputFormatter;
use Sammyjo20\Lasso\Exceptions\ConsoleMethodException;

/**
 * @internal
 */
final class Artisan
{
    /**
     * Command Line
     */
    protected Command $command;

    /**
     * Check if the console is running in silent mode
     */
    protected bool $isSilent = false;

    /**
     * Check the compiler output mode
     */
    protected string $compilerOutputMode = 'progress';

    /**
     * The progress bar
     */
    private ?ProgressBar $progressBar = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->compilerOutputMode = config('lasso.compiler.output', 'progress');
    }

    /**
     * Handle a method call
     *
     * @throws \Sammyjo20\Lasso\Exceptions\ConsoleMethodException
     */
    public function __call($name, $arguments): mixed
    {
        if (method_exists($this->command, $name)) {
            return call_user_func_array([$this->command, $name], $arguments);
        }

        throw new ConsoleMethodException(sprintf('Method %s::%s does not exist.', get_class($this->command), $name));
    }

    /**
     * Create a note for the front end, set the second parameter to true for an error.
     *
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

    /**
     * Show compiler output
     */
    public function showCompilerOutput(string $line): void
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

    /**
     * Mark compiler as complete
     *
     * @return void
     */
    public function compilerComplete(): void
    {
        if (! $this->progressBar instanceof ProgressBar) {
            return;
        }

        $this->progressBar->finish();
        $this->command->getOutput()->newLine();
    }

    /**
     * Get the progress bar
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
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

        $this->progressBar = $bar;

        return $bar;
    }

    /**
     * Set the command being run
     */
    public function setCommand(Command $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Run the artisan console in silent mode
     */
    public function silent(): self
    {
        $this->isSilent = true;

        return $this;
    }
}
