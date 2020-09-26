<?php

namespace Sammyjo20\Lasso\Container;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Exceptions\ConsoleMethodException;

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

    /**
     * @param Command $command
     * @return $this
     */
    public function setCommand(Command $command): self
    {
        $this->command = $command;

        return $this;
    }

    /**
     * @return $this
     */
    public function silent(): self
    {
        $this->isSilent = true;

        return $this;
    }
}
