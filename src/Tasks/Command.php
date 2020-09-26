<?php

namespace Sammyjo20\Lasso\Tasks;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class Command
{
    /**
     * @var array
     */
    protected $script;

    /**
     * @var string
     */
    protected $timeout;

    /**
     * Run the command. This task will run the given script/
     * if anything goes wrong, it will throw a ProcessFailedException.
     *
     * @throws ProcessFailedException
     */
    public function run(): void
    {
        $process = new Process($this->script);

        $process->setTimeout($this->timeout)
            ->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @param $script
     * @return $this
     */
    public function setScript($script): self
    {
        if (! is_array($script)) {
            $script = explode(' ', $script);
        }

        $this->script = $script;

        return $this;
    }

    /**
     * @param int $timeout
     * @return $this
     */
    public function setTimeout(int $timeout = 600): self
    {
        $this->timeout = $timeout;

        return $this;
    }
}
